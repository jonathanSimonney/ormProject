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

    public function find($id)
    {
        $dbWhereClause = '`'.$this->dbColumn.'`.`id` = '.$id;

        return $this->parseToEntities($dbWhereClause);
    }

    protected function parseToEntities($dbWhereClause)
    {
        $ret = [];

        $completeSql = 'SELECT * FROM `films` WHERE ('.$dbWhereClause.')';

        $arrayResult = $this->dbConn->fetchAll($completeSql);

        var_dump($arrayResult, $this->entityConfig);

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