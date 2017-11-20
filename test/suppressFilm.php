<?php

namespace test;

use entity\Film;

require_once '../index.php';

//we suppress a film with his id
$orm->getRepository(Film::class)->suppress(1);
