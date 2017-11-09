<?php

namespace test;

require_once '../index.php';

$seance = new \Seance();
$seance->setShowtime(new \DateTime('tomorrow'));

$film = new \Film();
$film->setReleaseDate(new \DateTime());
$film->setTitle('same title');
$film->setDuration(123);
$film->addSeance($seance);
$film->setDirector('me');

$orm->persist($film);
$orm->flush();