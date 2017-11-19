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

    public function count($dbWhereClause)
    {
        if ($dbWhereClause === ''){
            $completeSql = 'SELECT(COUNT(1)) FROM `films`';
        }else{
            $completeSql = 'SELECT(COUNT(1)) FROM `films` WHERE ('.$dbWhereClause.')';
        }

        return $this->dbConn->executeQuery($completeSql)->fetch()['(COUNT(1))'];
    }

    public function find($id)
    {
        $dbWhereClause = '`'.$this->dbColumn.'`.`id` = '.$id;

        return $this->parseToEntities($dbWhereClause);
    }

    protected function parseToEntities($dbWhereClause)
    {
        if ($dbWhereClause === ''){
            $completeSql = 'SELECT * FROM `films`';
        }else{
            $completeSql = 'SELECT * FROM `films` WHERE ('.$dbWhereClause.')';
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