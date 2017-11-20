<?php


namespace Library;


class MockedEntity
{
    private $mockedEntity;
    private $forbiddenCall;

    public function __construct($mockedEntity)
    {
        $this->mockedEntity = $mockedEntity;
        $this->forbiddenCall = array();
    }

    public function __isset($name)
    {
        return isset($this->mockedEntity->$name);
    }

    public function __set($name, $value)
    {
        $this->mockedEntity->$name = $value;
    }

    public function __get($name)
    {
        return $this->mockedEntity->$name;
    }

    public function __invoke()
    {
        return $this->mockedEntity;
    }

    public function __call($name, $arguments)
    {
        if (isset($this->forbiddenCall[$name])){
            return \call_user_func_array($this->forbiddenCall[$name], $arguments);
        }
        return \call_user_func_array(array($this->mockedEntity, $name), $arguments);
    }

    public function addForbiddenCall($methodName, $replacementMethod)
    {
        $this->forbiddenCall[$methodName] = $replacementMethod;
    }

    public function getTrueEntity()
    {
        return $this->mockedEntity;
    }
}