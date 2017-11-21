<?php

namespace test;

use entity\Film;
use entity\Seance;

require_once '../index.php';

//we select a film by id
$film = $orm->getRepository(Film::class)->find(1);

$seance = new Seance();
$seance->setShowtime(new \DateTime('tomorrow'));

$film->addSeance($seance);

$orm->persist($film);
