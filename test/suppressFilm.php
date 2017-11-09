<?php

namespace test;

require_once '../index.php';

$seance = new \Seance();
$seance->setShowtime(new \DateTime('tomorrow'));

//we suppress a film with his id
$orm->getRepository(\Film::class)->suppress(1);
