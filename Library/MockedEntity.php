<?php


namespace Library;


class MockedEntity
{
    private $mockedEntity;
    private $overidedCall;

    public function __construct($mockedEntity)
    {
        $this->mockedEntity = $mockedEntity;
        $this->overidedCall = array();
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
        if (isset($this->overidedCall[$name])){
            $requestAndParam = $this->overidedCall[$name];
            $requestMethod = $requestAndParam[0];
            $param = $requestAndParam[1];
            $setter = 'set'.ucfirst($param);
            $this->mockedEntity->$setter($requestMethod());

            $this->overidedCall = array_filter($this->overidedCall, function ($requestAndParam) use ($param){
                return $requestAndParam[1] !== $param;
            });
        }

        return \call_user_func_array(array($this->mockedEntity, $name), $arguments);
    }

    public function addDelayedRequest($paramToOverride, $requestMethod, $arrayMethods)
    {
        foreach ($arrayMethods as $method){
            $this->overidedCall[$method] = [$requestMethod, $paramToOverride];
        }
    }

    public function getTrueEntity()
    {
        return $this->mockedEntity;
    }
}