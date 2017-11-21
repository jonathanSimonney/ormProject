<?php

namespace test;

use entity\Film;

require_once '../index.php';

$filmRepository = $orm->getRepository(Film::class);

//we select a film by id
$film = $filmRepository->find(1);

//or we select all the films
$allFilms = $filmRepository->findAll();

$myFirstFilms = $filmRepository->findBy(['director' => 'me', 'test_column_title' => 'same title', 'duration' => 123]);

//or we select with conditions on the attributes, plus an order on the return (for more advanced selection, you'll need to write your own method of repository.)
$myFilms = $filmRepository->findBy(['director' => 'me', 'test_column_title' => 'same title'], ['duration' => 'ASC', 'releasedate' => 'DESC']);

var_dump($myFirstFilms, $film, $allFilms, $myFilms);

//and we can even make our conditions on the linked entities
var_dump($filmRepository->getFilmsShownThe(new \DateTime('2017-11-21')));