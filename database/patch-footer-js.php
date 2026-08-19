<?php

$path = __DIR__.'/../public/js/app.js';
$c = file_get_contents($path);
$from = 'All Rights Reserved';
$to = 'Créé par Alassane Oubda — Tous droits réservés. Contact : oubdaalassane01@gmail.com · +225 0757613098';
$count = 0;
$c = str_replace($from, $to, $c, $count);
file_put_contents($path, $c);
echo "replaced=$count\n";
