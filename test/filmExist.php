<?php

namespace test;

require_once '../index.php';

$seance = new \Seance();
$seance->setShowtime(new \DateTime('tomorrow'));

//we select a film by id
$film = $orm->getRepository(\Film::class)->exist(1);

if ($film !== null){
    //le film existe
}
