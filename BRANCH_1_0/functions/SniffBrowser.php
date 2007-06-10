<?php
/*
   This function is a browser sniffer that returns a string-indexed array.
   The call:
      $browser = SniffBrowser();
   will return the following:
   $browser['type'] = Netscape, Explorer, Opera, Amaya, Unknown
   $browser['version'] = version number (float)
   $browser['platform'] = Windows, Mac, Other
   $browser['css'] = CSS version
   $browser['dom'] = DOM type: NS, IE, W3C, 0

   Sourced from http://www.eit.ihk-edu.dk/instruct/php-sniffer.php?e=0,7,22#0

   I am not sure of the copyright details for this function...
*/
function SniffBrowser($userAgent)
{
   $b = $userAgent; $vers = 0.0;

   // detect browser brand and version number
   if (eregi('Opera[ \/]([0-9\.]+)' , $b, $a)) {
      $type = 'Opera';}
   elseif (eregi('Netscape[[:alnum:]]*[ \/]([0-9\.]+)', $b, $a)) {
      $type = 'Netscape';}
   elseif (eregi('MSIE[ \/]([0-9\.]+)', $b, $a)) {
      $type = 'Explorer';}
   elseif (eregi('Mozilla[ \/]([0-9\.]+)' , $b, $a)) {
      if (eregi('compatible' , $b)) {
         $type = 'Unknown';}
      else {
         $type = 'Netscape';}}
   elseif (eregi('([[:alnum:]]+)[ \/v]*([0-9\.]+)' , $b, $a)) {
      $type = $a[1]; $vers = $a[2];}
   else {
      $type = 'Unknown';}
   if (!$vers) $vers = $a[1];
   $browser['type'] = $type;
   $browser['version'] = $vers;

   // detect platform
   if (eregi('Win',$b)) $browser['platform'] = 'Windows';
   elseif (eregi('Mac',$b)) $browser['platform'] = 'Mac';
   else $browser['platform'] = 'Other';

   // find CSS version
   // note: it is unknown which future versions will support CSS2
   if ($type == 'Netscape' && $vers >= 4 ||
   $type == 'Explorer' && $vers >= 3 ||
   $type == 'Opera' && $vers >= 3) {
      $browser['css'] = 1;
      if ($type == 'Netscape' && $vers >= 5 ||
      $type == 'Explorer' && $vers >= 6 ||
      $type == 'Opera' && $vers >= 4) {
         $browser['css'] = 2;}}

   // detect DOM version
   $browser['dom'] = '0';
   if ($type == 'Explorer' && $vers >= 4 && $browser['platform'] == 'Windows') {
      // note: it is unknown which DOM model future versions of Explorer will use
      $browser['dom'] = 'IE';}
   elseif ($type == 'Opera' && $vers >= 5) {
      $browser['dom'] = 'W3C';}
   elseif ($type == 'Netscape') {
      if ($vers >= 5) $browser['dom'] = 'W3C';
      elseif ($vers >= 4) $browser['dom'] = 'NS';}

   // return array of answers
   return $browser;
}
?>