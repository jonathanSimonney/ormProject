<?php
namespace Library;


class BaseRepository
{
    private $entityConfig;
    private $dbConn;
    protected $dbColumn;//useful for easier access in user created repository...

    public function __construct($entityConfig, $dbConn)
    {
        $this->dbConn = $dbConn;
        $this->entityConfig = $entityConfig;
        $this->dbColumn = $entityConfig['dbConfig'];
    }

    public function suppress($id)
    {
        $dbWhereClause = '`'.$this->dbColumn.'`.`id` = '.$id;
        $completeSql = 'DELETE FROM `'.$this->dbColumn.'` WHERE ('.$dbWhereClause.')';

        $this->dbConn->executeQuery($completeSql);
    }

    public function exist($id)
    {
        $dbWhereClause = '`'.$this->dbColumn.'`.`id` = '.$id;

        $completeSql = 'SELECT EXISTS(SELECT 1 FROM `'.$this->dbColumn.'` WHERE ('.$dbWhereClause.'))';

        $result = $this->dbConn->executeQuery($completeSql)->fetch();

        return (bool)reset($result);

    }

    public function count($dbWhereClause)
    {
        if ($dbWhereClause === ''){
            $completeSql = 'SELECT(COUNT(1)) FROM `'.$this->dbColumn.'`';
        }else{
            $completeSql = 'SELECT(COUNT(1)) FROM `'.$this->dbColumn.'` WHERE ('.$dbWhereClause.')';
        }

        return $this->dbConn->executeQuery($completeSql)->fetch()['(COUNT(1))'];
    }

    public function find($id)
    {
        $dbWhereClause = '`'.$this->dbColumn.'`.`id` = '.$id;

        $arrayEntities = $this->parseToEntities($dbWhereClause);

        if (\count($arrayEntities) === 0){
            return null;
        }

        return $arrayEntities[0];
    }

    public function findAll()
    {
        return $this->parseToEntities('');
    }

    public function findBy($findParams, $orderParam = [])
    {
        $dbWhereClause = '';
        $first = true;

        foreach ($findParams as $key => $value){
            if ($first){
                $first = false;
            }else{
                $dbWhereClause .= ' AND ';
            }
            $dbWhereClause .= '`'.$this->dbColumn.'`.`'.$key.'` = \''.$value.'\'';
        }

        return $this->parseToEntities($dbWhereClause, $orderParam);
    }

    protected function parseToEntities($dbWhereClause, $orderByArray = [])
    {
        if ($dbWhereClause === ''){
            $completeSql = 'SELECT * FROM `'.$this->dbColumn.'`';
        }else{
            $completeSql = 'SELECT * FROM `'.$this->dbColumn.'` WHERE ('.$dbWhereClause.')';
        }

        if ($orderByArray !== []){
            $completeSql .= ' ORDER BY ';
            $first = true;
            foreach ($orderByArray as $column => $ascOrDesc) {
                if ($first){
                    $first = false;
                }else{
                    $completeSql .= ', ';
                }
                $completeSql .= '`'.$column.'` '.strtoupper($ascOrDesc);
            }
        }

        $ret = [];

        $arrayResult = $this->dbConn->fetchAll($completeSql);

//        var_dump($arrayResult, $this->entityConfig);

        foreach ($arrayResult as $result){
            $newElem = new $this->entityConfig['name']();

            foreach ($result as $columnName => $sqlValue){
                /** @var EntityAttribute $attributeObject */
                $attributeObject = $this->entityConfig['attributes'][$columnName];
                $setter = 'set'.ucfirst($attributeObject->getName());
                $phpValue = $attributeObject->fromSQLToPHP($sqlValue);

                $newElem->$setter($phpValue);
            }

            $ret[] = $newElem;
        }

        return $ret;
    }
}