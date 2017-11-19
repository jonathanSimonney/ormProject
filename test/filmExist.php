<?php

namespace test;

require_once '../index.php';

//we select a film by id
$film = $orm->getRepository(\Film::class)->exist(1);
