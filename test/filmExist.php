<?php

namespace test;

use entity\Film;

require_once '../index.php';

//we select a film by id
$filmExist = $orm->getRepository(Film::class)->exist(1);

var_dump($filmExist);
