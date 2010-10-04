<?php
chdir('../../');

// This must be first - includes config.php
require_once("./include/begin.inc.php");

require_once("./functions/GDImage.class.php");

$gdImage = new GDImage('gif');
$gdImage->createImage('code_bg');
$gdImage->sendImage();

print_r($gdImage->getErrors());

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>