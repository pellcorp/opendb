<?php

chdir('..');

include_once("lib/phpsniff/phpSniff.class.php");
$phpSniffer = new phpSniff();

print_r($phpSniffer->property());

?>