<?php

namespace Library;


abstract class BaseEntity
{
//    public $needFlush;

    abstract public function getId();
    abstract public function setId(int $id);

    public function __construct()
    {
//        $this->needFlush = true;
    }

    public function __get($name)
    {
        $getter = 'get'.ucfirst($name);
        return $this->$getter();
    }

    public function getSQLValue(EntityAttribute $attribute)
    {
        if ($attribute->getisId()){
            return NULL;
        }

        $name = $attribute->getName();

        $getter = 'get'.ucfirst($name);
        return $attribute->fromPHPToSQL($this->$getter());
    }
}