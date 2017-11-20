<?php


namespace Library;


class EntityAttribute
{
    static private $dbTypeEquivalent;
    private $name;
    private $isId;
    private $isNullable;
    private $dbColumn;
    private $dbType;
    private $attributeType;
    private $entityRel;

    public function __construct($name, $annotationData)
    {
        self::$dbTypeEquivalent = [//todo put this in a Type class
            'integer'  => 'INT',
            'string'   => 'VARCHAR(255)',
            'text'     => 'LONGTEXT',
            'datetime' => 'DATETIME',
        ];

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
            //WARNING! if you put a OneToMany annotationData, you MUST put a ManyToOne relation to ensure the db is created as needed!

            if (isset($annotationData['ManyToOne'])){
                $this->entityRel['type'] = 'ManyToOne';
                $this->entityRel['targetEntity'] = $annotationData['ManyToOne'];
                if (isset($annotationData['InversedBy'])) {
                    $this->entityRel['oppositeAttribute'] = $annotationData['InversedBy'];
                }else{
                    throw new \Exception('Please give an InversedBy annotation for entity with a ManyToOne relation.');
                }
            } else{
                $this->entityRel['type'] = 'OneToMany';
                $this->entityRel['targetEntity'] = $annotationData['OneToMany'];
                if (isset($annotationData['MappedBy'])) {
                    $this->entityRel['oppositeAttribute'] = $annotationData['MappedBy'];
                }else{
                    throw new \Exception('Please give an MappedBy annotation for entity with a OneToMany relation.');
                }
            }
        }else{
            $this->dbType = $annotationData['Column'];
        }
    }

    private function getFormatedDbType(){
        if (isset(self::$dbTypeEquivalent[strtolower($this->dbType)])){
            return self::$dbTypeEquivalent[strtolower($this->dbType)];
        }

        return strtoupper($this->dbType);

        //todo : log this somewhere throw new \Exception('unrecognized data type.');
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

    public function getSQLCreateStatement(){
        if ($this->isId){
            return $this->dbColumn.' INT AUTO_INCREMENT NOT NULL, PRIMARY KEY('.$this->name.')';
        }

        if ($this->entityRel !== null){
            if ($this->entityRel['type'] === 'ManyToOne'){
                return$this->dbColumn.'_id INT';
            }
            return null;
        }

        $notOrDefault = $this->isNullable ? 'DEFAULT' : 'NOT';

        return $this->dbColumn.' '.$this->getFormatedDbType().' '.$notOrDefault.' NULL';
    }

    public function fromPHPToSQL($phpVal){//todo put this in a type class
        $type = $this->getDbType();

        if ($type === 'date' || $type === 'datetime'){
            if (!$phpVal instanceof \DateTimeInterface){
                throw new \Exception("Please make sure the php value passed to be put in a date or datetime column is a datetime.");
            }

            /**
             * @var $phpVal \DateTimeInterface
             */

            if ($type === 'date'){
                return $phpVal->format('Y-m-d');
            }

            return $phpVal->format('Y-m-d H:i:s');
        }
        //(there will be datetime and date in column key.
        //todo if this is an entity attribute, do things differently...

        return $phpVal;
    }

    public function fromSQLToPHP($sqlVal){//todo put this in a type class
        $type = $this->getDbType();

        if ($type === 'date' || $type === 'datetime'){
            return new \DateTime($sqlVal);
        }
        //(there will be datetime and date in column key.
        //todo if this is an entity attribute, do things differently...

        return $sqlVal;
    }
}