<?php

namespace test;

use entity\Film;

require_once '../index.php';


echo $orm->getRepository(Film::class)->count('`films`.`director` = \'me\'');
