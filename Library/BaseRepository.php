<?php
namespace Library;


use Couchbase\Exception;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;

class BaseRepository
{
    /** @var  $orm Orm */
    private $orm;
    private $entityConfig;
    private $dbConn;
    protected $dbColumn;//useful for easier access in user created repository...
    private $logManager;

    public function __construct($entityConfig, $dbConn, $orm, $logManager)
    {
        $this->orm = $orm;
        $this->dbConn = $dbConn;
        $this->entityConfig = $entityConfig;
        $this->dbColumn = $entityConfig['dbConfig'];
        $this->logManager = $logManager;
    }

    public function suppress($id)
    {
        $dbWhereClause = '`'.$this->dbColumn.'`.`id` = '.$id;
        $completeSql = 'DELETE FROM `'.$this->dbColumn.'` WHERE ('.$dbWhereClause.')';

        try{
            $begin = microtime(true);
            $this->dbConn->executeQuery($completeSql);
            $duration = microtime(true) - $begin;

            $this->logManager->writeToLog($completeSql, [], $duration);
        }catch (DBALException $error){
            $this->logManager->writeToLog($completeSql, [], $error);
        }
    }

    public function exist($id)
    {
        $dbWhereClause = '`'.$this->dbColumn.'`.`id` = '.$id;

        $completeSql = 'SELECT EXISTS(SELECT 1 FROM `'.$this->dbColumn.'` WHERE ('.$dbWhereClause.'))';

        try{
            $begin = microtime(true);
            $result = $this->dbConn->executeQuery($completeSql)->fetch();
            $duration = microtime(true) - $begin;

            $this->logManager->writeToLog($completeSql, [], $duration);
        }catch (DBALException $error){
            $this->logManager->writeToLog($completeSql, [], $error);
        }

        return (bool)reset($result);

    }

    public function count($dbWhereClause)
    {
        if ($dbWhereClause === ''){
            $completeSql = 'SELECT(COUNT(1)) FROM `'.$this->dbColumn.'`';
        }else{
            $completeSql = 'SELECT(COUNT(1)) FROM `'.$this->dbColumn.'` WHERE ('.$dbWhereClause.')';
        }

        try{
            $begin = microtime(true);
            $result = $this->dbConn->executeQuery($completeSql)->fetch()['(COUNT(1))'];
            $duration = microtime(true) - $begin;

            $this->logManager->writeToLog($completeSql, [], $duration);
        }catch (DBALException $error){
            $this->logManager->writeToLog($completeSql, [], $error);
        }

        return $result;
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

    protected function parseToEntities($dbWhereClause, $orderByArray = [], $joinStatement = '')
    {
        if ($dbWhereClause === ''){
            $completeSql = 'SELECT * FROM `'.$this->dbColumn.'`';
        }else{
            $completeSql = 'SELECT * FROM `'.$this->dbColumn.'` '.$joinStatement.' WHERE ('.$dbWhereClause.')';
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

        try{
            $begin = microtime(true);
            $arrayResult = $this->dbConn->fetchAll($completeSql);
            $duration = microtime(true) - $begin;

            $this->logManager->writeToLog($completeSql, [], $duration);
        }catch (DBALException $error){
            $this->logManager->writeToLog($completeSql, [], $error);
        }

//        var_dump($arrayResult, $this->entityConfig);

        foreach ($arrayResult as $result){
            $newElem = new $this->entityConfig['name']();
            $addMock = false;
            $mockedNewElem = null;

            foreach ($this->entityConfig['attributes'] as $columnName => $attributeObject){
                /** @var EntityAttribute $attributeObject */
                if ($attributeObject->getEntityRel() !== null){
                    $addMock = true;
                    $mockedNewElem = new MockedEntity($newElem);
                    if ($attributeObject->getEntityRel()['type'] === 'ManyToOne'){
                        $sqlValue = $result[$columnName];

                        $getter = 'get'.ucfirst($attributeObject->getName());

                        $className = $this->orm->getEntityFolder().'\\'.$attributeObject->getAttributeType();
                        $requestFunc = function () use ($sqlValue, $className){
                            $otherEntityRepo = $this->orm->getRepository($className);
                            return $otherEntityRepo->find($sqlValue);
                        };

                        $paramName = $attributeObject->getName();

                        $mockedNewElem->addDelayedRequest($paramName, $requestFunc, [$getter]);
                    }else{
                        $getter = 'get'.ucfirst($attributeObject->getName());
                        $adder = 'add'.ucfirst(substr($attributeObject->getName(), 0, -1));

                        $foreignColumnName = $attributeObject->getEntityRel()['oppositeAttribute'].'_id';
                        $entityId = $mockedNewElem->getId();

                        $className = $this->orm->getEntityFolder().'\\'.substr($attributeObject->getAttributeType(), 0, -2);
                        $requestFunc = function () use ($className, $foreignColumnName, $entityId){
                            $otherEntityRepo = $this->orm->getRepository($className);
                            return $otherEntityRepo->findBy([$foreignColumnName => $entityId]);
                        };

                        $paramName = $attributeObject->getName();

                        $mockedNewElem->addDelayedRequest($paramName, $requestFunc, [$getter, $adder]);
                    }
                    continue;
                }

                $sqlValue = $result[$columnName];
                $setter = 'set'.ucfirst($attributeObject->getName());
                $phpValue = $attributeObject->fromSQLToPHP($sqlValue);

                if ($addMock){
                    $mockedNewElem->$setter($phpValue);
                }else{
                    $newElem->$setter($phpValue);
                }
            }

            if ($addMock){
                $ret[] = $mockedNewElem;
            }else{
                $ret[] = $newElem;
            }
        }

        return $ret;
    }
}