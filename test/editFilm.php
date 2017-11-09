<?php

namespace test;

require_once '../index.php';

$seance = new \Seance();
$seance->setShowtime(new \DateTime('tomorrow'));

//we select a film by id
$film = $orm->getRepository(\Film::class)->find(1);

$film->setTitle('other title');

$orm->persist($film);
$orm->flush();