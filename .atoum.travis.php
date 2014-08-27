<?php

$script->addDefaultReport();

/*
* Clover xml coverage
*/
$cloverWriter = new \mageekguy\atoum\writers\file(__DIR__.'/atoum.coverage.xml');
$clover = new \mageekguy\atoum\reports\asynchronous\clover();
$clover->addWriter($cloverWriter);

$runner->addReport($clover);

include '.atoum.php';
