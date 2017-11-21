<?php

namespace test;

use entity\Film;
use entity\Seance;

require_once '../index.php';

$filmRepo = $orm->getRepository(Film::class);
$seanceRepo = $orm->getRepository(Seance::class);

//we suppress a film with his id
$filmRepo->suppress(12);

$seanceOrphaned = $seanceRepo->find(11);
var_dump($seanceOrphaned->getFilm());//return null (so $seanceOrphaned EXIST!)


//BEWARE! you MUST suppress manually "child" entities, or tou will get orphaned childs. look :
$filmToSuppress = $filmRepo->find(10);

foreach ($filmToSuppress->getSeances() as $seance){
    $seanceRepo->suppress($seance->getId());
}

$filmRepo->suppress(10);

$seanceNotOrphaned = $seanceRepo->find(8);
var_dump($seanceNotOrphaned);//return null : (so $seanceNotOrphaned is NOT null)