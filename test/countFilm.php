<?php

namespace test;

require_once '../index.php';


echo $orm->getRepository(\Film::class)->count('`films`.`director` = \'me\'');
