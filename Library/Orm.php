<?php

namespace Library;

use Doctrine\Common\CommonException;
use Doctrine\DBAL\Exception\ConnectionException;
use entity\Film;

class Orm
{
// utiliser les __get pour ne faire les requêtes pour les entités liées QUE lorsque celles-ci sont nécessaires.
// (Renvoyer une entité où on aurait réécrit le __get?

    private $dbalConn;
    private $entitiesConfig;//array of entities with name, dbColumn, attributes and repository
    private $entityFolder;
    private $logManager;

    public function __construct($publicConfig, $privateConfig)
    {
        $this->logManager = new LogManager();

        if ($publicConfig['language'] !== 'mySql'){
            throw new \Exception("No other language than mySql is currently supported.");
        }
        if ($publicConfig['use_strict'] !== true){
            throw new \Exception("The orm can't currently be configured to automatically add entity or relation to the database.");
        }
        if ($publicConfig['entity_config'] !== 'annotation'){
            throw new \Exception("No other entity configuration can currently be done without annotation.");
        }
        if ($publicConfig['from_db_to_entity'] !== false){
            throw new \Exception("The db can't currently serve as model for the entities.");
        }

        $this->setupEntitiesAnnotation($publicConfig['entity_folder_path']);
        $this->setupRepositoryAnnotations($publicConfig['repository_folder_path'], $publicConfig['entity_folder_path']);

//        var_dump($this->entitiesConfig['entity\\Film']);

        $this->checkDbConn($privateConfig);
    }

    private function setupRepositoryAnnotations($repositoryFolder, $entityFolder)
    {
        $arrayRepository = scandir(__DIR__.'\\..\\'.$repositoryFolder);
        $this->entityFolder = $entityFolder;

        foreach ($arrayRepository as $repo){
            if ($this->endsWith($repo, '.php')) {
                $repoClass = $repositoryFolder.'\\'.substr($repo, 0, -4);
                $reflectionClass = new \ReflectionClass($repositoryFolder.'\\'.substr($repo, 0, -4));

                if (!$reflectionClass->isSubclassOf(BaseRepository::class)){
                    throw new \Exception('Your repositories MUST extend the baseRepository class');
                }

                $classComment = $reflectionClass->getDocComment();
                $classAnnotation = $this->parseAnnotation($classComment);

                if (!isset($classAnnotation['Entity'])){
                    throw new \Exception('You must put an entity key for your repository class.');
                }

                $key = $entityFolder.'\\'.$classAnnotation['Entity'];
                if (!isset($this->entitiesConfig[$key])){
                    throw new \Exception('Class '.$classAnnotation['Entity'].' does not exist, please enter a valid class name for repository '.$repo);
                }

                $this->entitiesConfig[$key]['repository'] = $repoClass;
            }
        }
    }

    private function setupEntitiesAnnotation($entityFolder)
    {
        $arrayEntity = scandir(__DIR__.'\\..\\'.$entityFolder);

        foreach ($arrayEntity as $entity){
            if ($this->endsWith($entity, '.php')){
                $hasId = false;

                $singleEntityConfig = [];

                $reflectionClass = new \ReflectionClass($entityFolder.'\\'.substr($entity, 0, -4));

                if (!$reflectionClass->isSubclassOf(BaseEntity::class)){
                    throw new \Exception('Your entities MUST extend the baseEntity class');
                }

                $singleEntityConfig['name'] = $reflectionClass->getName();

                $classComment = $reflectionClass->getDocComment();
                $classAnnotation = $this->parseAnnotation($classComment);

                if (!isset($classAnnotation['Table'])){
                    throw new \Exception('The entities in your entity folder MUST be annotated with a @Table annotation.');
                }

                $singleEntityConfig['dbConfig'] = $classAnnotation['Table'];
                $singleEntityConfig['attributes'] = array();

                $properties = $reflectionClass->getProperties();
                foreach ($properties as $property){
                    $propComment = $property->getDocComment();
                    $attributeName = $property->getName();
                    $annotationData = $this->parseAnnotation($propComment);


                    if (!isset($annotationData['var'])){
                        continue;
                    }
                    try{
                        $attribute = new EntityAttribute($attributeName, $annotationData);
                        $singleEntityConfig['attributes'][$attribute->getDbColumn()] = $attribute;

                        if ($attribute->getisId()){
                            $hasId = true;
                        }
                    }
                    catch (CommonException $c){
                        //do nothing, it is only temporary, and make sure the attribute isn't mapped..
                    }

                }

                if (!$hasId){
                    throw new \Exception('The entities MUST have at least an id field : no id field found for entity '.$singleEntityConfig['name']);
                }

                $singleEntityConfig['repository'] = BaseRepository::class;
                $this->entitiesConfig[$singleEntityConfig['name']] = $singleEntityConfig;
            }
        }
    }

    private function parseAnnotation(String $comment)
    {
        $final = [];
        $comment = preg_replace('/[\/\*]/', '', $comment);
        $ret = explode('@', $comment);
        unset($ret[0]);
        foreach ($ret as $elem){
            $elem = trim($elem);
            if (strpos($elem, ' ') !== false){
                $keyValue = explode(' ', $elem);
                $final[$keyValue[0]] = $keyValue[1];
            }else{
                $final[$elem] = '';
            }
        }

        return $final;
    }

    private function checkDbConn($privateConfig)
    {
        $config = new \Doctrine\DBAL\Configuration();

        $privateConfig['driver'] = 'pdo_mysql';//todo set this with the publicConfig language.

        $this->dbalConn = \Doctrine\DBAL\DriverManager::getConnection($privateConfig, $config);

        try{
            $this->dbalConn->connect();
        }catch (ConnectionException $exc){
            throw new \InvalidArgumentException("Invalid db connection parameters given");
        }
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 ||
        (substr($haystack, -$length) === $needle);
    }

    public function updateDb()
    {
        foreach ($this->entitiesConfig as $entity) {
            $sqlQuery = 'CREATE TABLE `'.$entity['dbConfig'].'`(';

            $first = true;

            foreach ($entity['attributes'] as $attribute){
                /**
                 * @var $attribute EntityAttribute
                 */
                $sqlCreate = $attribute->getSQLCreateStatement();
                if ($sqlCreate !== null){
                    if ($first){
                        $first = false;
                    }else{
                        $sqlQuery .= ', ';
                    }

                    $sqlQuery .= $sqlCreate;
                }
            }

            $sqlQuery .= ')';

            try{
                $begin = microtime(true);
                $this->dbalConn->exec($sqlQuery);
                $duration = microtime(true) - $begin;

                $this->logManager->writeToLog($sqlQuery, [], $duration);
            }catch (DBALException $error){
                $this->logManager->writeToLog($sqlQuery, [], $error);
            }
        }
    }

    /**
     * @param BaseEntity $entity
     * @throws \Exception
     */
    public function persist($entity)
    {
        if ($entity instanceof MockedEntity){
            $entity = $entity->getTrueEntity();
        }

        $toBePersistedAfter = array(); //array of children elems who NEED this elem to be persisted themselves.
        $specificEntityConfig = $this->entitiesConfig[\get_class($entity)];

        if ($entity->getId() === null){
            $sqlQuery = 'INSERT INTO `'.$specificEntityConfig['dbConfig'].'` (';
            $sqlQueryValues = '(';
            $params = [];

            $first = true;
            foreach ($specificEntityConfig['attributes'] as $attribute){
                /**
                 * @var $attribute EntityAttribute
                 */
                if ($attribute->getEntityRel() === null){
                    if ($first){
                        $first = false;
                    }else{
                        $sqlQuery .= ', ';
                        $sqlQueryValues .= ', ';
                    }

                    $sqlQuery .= '`'.$attribute->getDbColumn().'`';
                    $sqlQueryValues .= ':'.$attribute->getDbColumn();
                    $params[$attribute->getDbColumn()] = $entity->getSQLValue($attribute);
                }elseif($attribute->getEntityRel()['type'] === 'OneToMany'){
                    $getter = 'get'.ucfirst($attribute->getName());
                    $setter = 'set'.ucfirst($attribute->getEntityRel()['oppositeAttribute']);
                    $toBePersistedAfter[$setter] = array();

                    foreach ($entity->$getter() as $singleEntityToPersist){
                        $toBePersistedAfter[$setter][] = $singleEntityToPersist;
                        //we save the persist of linked entities for after in order to have their "parent" id.
                    }
                }else{
                    if ($first){
                        $first = false;
                    }else{
                        $sqlQuery .= ', ';
                        $sqlQueryValues .= ', ';
                    }

                    $sqlQuery .= '`'.$attribute->getDbColumn().'`';
                    $sqlQueryValues .= ':'.$attribute->getDbColumn();
                    if ($entity->getSQLValue($attribute) === null){
                        $getter = 'get'.ucfirst($attribute->getName());
                        $adder = 'add'.ucfirst(substr($attribute->getEntityRel()['oppositeAttribute'], 0, -1));
                        $entity->$getter()->$adder($entity);
                        $this->persist($entity->$getter());
                        return;
                    }
                    $params[$attribute->getDbColumn()] = $entity->getSQLValue($attribute);
                }

            }

            $sqlQueryValues .= ')';
            $sqlQuery .= ') VALUES '.$sqlQueryValues;

            try{
                $preparedQuery = $this->dbalConn->prepare($sqlQuery);

                $begin = microtime(true);
                $preparedQuery->execute($params);
                $duration = microtime(true) - $begin;

                $this->logManager->writeToLog($sqlQuery, $params, $duration);
            }catch (DBALException $error){
                $this->logManager->writeToLog($sqlQuery, $params, $error);
            }

            $entity->setId($this->dbalConn->lastInsertId());

        }else{
            $oldEntity = $this->getRepository(\get_class($entity))->find($entity->getId());
            $entityAttributeArray = $this->entitiesConfig[\get_class($entity)]['attributes'];
            $updateContent = '';
            $id = null;

            foreach ($entityAttributeArray as $attribute) {
                /** @var EntityAttribute $attribute */
                $getter = 'get'.ucfirst($attribute->getName());

                if ($oldEntity->$getter() !== $entity->$getter()){//todo currently compare two identical datetime as different!
                    if ($attribute->getEntityRel() === null){
                        if ($updateContent !== ''){
                            $updateContent .= ', ';
                        }

                        $updateContent .= '`'.$attribute->getDbColumn().'` = \''.$attribute->fromPHPToSQL($entity->$getter()).'\'';
                    }elseif($attribute->getEntityRel()['type'] === 'OneToMany'){
                        $setter = 'set'.ucfirst($attribute->getEntityRel()['oppositeAttribute']);
                        $toBePersistedAfter[$setter] = array();

                        foreach ($entity->$getter() as $singleEntityToPersist){
                            $toBePersistedAfter[$setter][] = $singleEntityToPersist;
                            //we save the persist of linked entities for after in order to have their "parent" id.
                        }
                    }else{
                        if ($attribute->fromPHPToSQL($entity->$getter()) === null){
                            $adder = 'add'.ucfirst(substr($attribute->getEntityRel()['oppositeAttribute'], 0, -1));
                            $entity->$getter()->$adder($entity);
                            $this->persist($entity->$getter());
                            return;
                        }

                        if ($updateContent !== ''){
                            $updateContent .= ', ';
                        }

                        $updateContent .= '`'.$attribute->getDbColumn().'` = \''.$attribute->fromPHPToSQL($entity->$getter()).'\'';
                    }
                }

                if ($id === null && $attribute->getisId()){
                    $id = $entity->$getter();
                    $idColumn = $attribute->getDbColumn();
                }
            }

            if ($updateContent !== ''){
                $updateStatement = 'UPDATE `'.$this->entitiesConfig[\get_class($entity)]['dbConfig'].'` SET ';
                $updateStatement .= $updateContent.' WHERE `'.$this->entitiesConfig[\get_class($entity)]['dbConfig'].'`.`'.$idColumn.'` = '.$id;

                try{
                    $begin = microtime(true);
                    $this->dbalConn->exec($updateStatement);
                    $duration = microtime(true) - $begin;

                    $this->logManager->writeToLog($updateStatement, [], $duration);
                }catch (DBALException $error){
                    $this->logManager->writeToLog($updateStatement, [], $error);
                }
            }
        }

        foreach ($toBePersistedAfter as $setter => $arrayEntityToPersist){
            foreach ($arrayEntityToPersist as $singleEntityToPersist){
                $singleEntityToPersist->$setter($entity);
                $this->persist($singleEntityToPersist);
            }
        }
    }

    public function getRepository($class)
    {
        $key = $class;
        $repoName = $this->entitiesConfig[$key]['repository'];

        return new $repoName($this->entitiesConfig[$key], $this->dbalConn, $this, $this->logManager);
    }

    public function getEntityFolder()
    {
        return $this->entityFolder;
    }
}
