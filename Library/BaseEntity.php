<?php

namespace Library;


class BaseEntity
{
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