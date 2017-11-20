<?php

namespace test;

use entity\Film;
use entity\Seance;

require_once '../index.php';

$seance = new Seance();
$seance->setShowtime(new \DateTime('tomorrow'));

$film = new Film();
$film->setReleaseDate(new \DateTime());
$film->setTitle('same title');
$film->setDuration(123);
$film->setDirector('me');


$seance->setFilm($film);

$orm->persist($seance);

$otherSeance = new Seance();
$otherSeance->setShowtime(new \DateTime('tomorrow'));

$filmRepository = $orm->getRepository(Film::class);

//we select a film by id
$otherFilm = $filmRepository->find(1);

$otherSeance->setFilm($otherFilm);

$orm->persist($otherSeance);
