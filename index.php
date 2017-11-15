<?php
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Library\Orm;


$publicConfig = Yaml::parse(file_get_contents(__DIR__.'/config/public.yml'));
$privateConfig = Yaml::parse(file_get_contents(__DIR__.'/config/private.yml'));

//we give our orm its configuration AND its information to connect to the database.
$orm = new Orm($publicConfig['orm_config'], $privateConfig['db_config']);
