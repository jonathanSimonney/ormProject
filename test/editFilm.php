<?php

namespace test;

require_once '../index.php';

//we select a film by id
$film = $orm->getRepository(\Film::class)->find(1)[0];

$film->setTitle('other title');

var_dump($film);

$orm->persist($film);
