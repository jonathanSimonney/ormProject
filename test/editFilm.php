<?php

namespace test;

use entity\Film;

require_once '../index.php';

//we select a film by id
$film = $orm->getRepository(Film::class)->find(5);

$film->setTitle('other title');

var_dump($film);

$orm->persist($film);
