<?php


namespace Library;


use Doctrine\Common\CommonException;

class EntityAttribute
{
    private $name;
    private $isId;
    private $isNullable;
    private $dbColumn;
    private $dbType;
    private $attributeType;
    //todo
//    private $entityRel;

    public function __construct($name, $annotationData)
    {
        $this->setName($name);

        if (isset($annotationData['Id'])){
            $this->isId = true;
        }else{
            $this->isId = false;
        }

        if (!isset($annotationData['Nullable'])){
            $this->isNullable = false;
        }else{
            $this->isNullable = (bool)$annotationData['Nullable'];
        }

        if (!isset($annotationData['ColumnName'])){
            $this->dbColumn = mb_strtolower($name);
        }else{
            $this->dbColumn = $annotationData['ColumnName'];
        }

        if (!isset($annotationData['var'])){
            throw new \Exception("This attribute can't be mapped!");
        }

        $this->attributeType = $annotationData['var'];

        if (!isset($annotationData['Column'])){
            if (!isset($annotationData['ManyToOne']) && !isset($annotationData['OneToMany'])){
//                var_dump($annotationData);
                throw new \Exception('Please provide an annotation Column or an annotation of entity relation.');
            }
            throw new CommonException('This feature isn\'t implemented yet.');
            //for now, simply throw an exception, but it is an EXTREMELY IMPORTANT
            //todo!!!

        }else{
            $this->dbType = $annotationData['Column'];
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getisId()
    {
        return $this->isId;
    }

    /**
     * @param mixed $isId
     */
    public function setIsId($isId)
    {
        $this->isId = $isId;
    }

    /**
     * @return mixed
     */
    public function getisNullable()
    {
        return $this->isNullable;
    }

    /**
     * @param mixed $isNullable
     */
    public function setIsNullable($isNullable)
    {
        $this->isNullable = $isNullable;
    }

    /**
     * @return mixed
     */
    public function getDbColumn()
    {
        return $this->dbColumn;
    }

    /**
     * @param mixed $dbColumn
     */
    public function setDbColumn($dbColumn)
    {
        $this->dbColumn = $dbColumn;
    }

    /**
     * @return mixed
     */
    public function getDbType()
    {
        return $this->dbType;
    }

    /**
     * @param mixed $dbType
     */
    public function setDbType($dbType)
    {
        $this->dbType = $dbType;
    }

    /**
     * @return mixed
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * @param mixed $attributeType
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;
    }

//    /**
//     * @return mixed
//     */
//    public function getEntityRel()
//    {
//        return $this->entityRel;
//    }
//
//    /**
//     * @param mixed $entityRel
//     */
//    public function setEntityRel($entityRel)
//    {
//        $this->entityRel = $entityRel;
//    }
}