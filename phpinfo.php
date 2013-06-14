<?php
include_once("./lib/phpsniff/phpSniff.class.php");

phpinfo();

$phpSniffer = new phpSniff();
print_r($phpSniffer->property());
?>
