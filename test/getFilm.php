<?php

namespace test;

require_once '../index.php';

$seance = new \Seance();
$seance->setShowtime(new \DateTime('tomorrow'));

$filmRepository = $orm->getRepository(\Film::class);

//we select a film by id
$film = $filmRepository->find(1);

//or we select all the films
$filmRepository->findAll();

//or we select with conditions on the attributes (for more advanced selection, you'll need to write your own method of repository.)
$filmRepository->findBy(['director' => 'name of director']);

//this selection can be ordered as we want
//Is this ok if this is made in the repositories with some exemple of how to do it with doctrine queryBuilder?

//and we can even make our conditions on the linked entities
//Is this ok if this is made in the repositories with some exemple of how to do it with doctrine queryBuilder?


$orm->persist($film);
$orm->flush();