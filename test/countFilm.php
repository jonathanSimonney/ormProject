<?php

namespace test;

require_once '../index.php';


$orm->getRepository(\Film::class)->count(/*write this method in the repository*/);
