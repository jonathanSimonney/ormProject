<?php

namespace test;

use entity\Film;
use entity\Seance;

require_once '../index.php';

//we select a film by id
$seance = $orm->getRepository(Seance::class)->find(5);

var_dump($seance->getFilm());

$film = new Film();
$film->setReleaseDate(new \DateTime());
$film->setTitle('same title');
$film->setDuration(123);
$film->setDirector('me');

$seance->setFilm($film);

$orm->persist($seance);
