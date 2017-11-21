<?php

namespace test;

use entity\Film;
use entity\Seance;

require_once '../index.php';

//we select a film by id
$seance = $orm->getRepository(Seance::class)->find(5);

var_dump($seance->getFilm());

$film = $orm->getRepository(Film::class)->find(1);

$seance->setFilm($film);

$orm->persist($seance);
