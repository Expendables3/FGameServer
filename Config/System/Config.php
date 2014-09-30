<?php
/**
 * Config  Release 0 : Dev , 1 : QC Test , 2 : LIVE
 */

 $release = 0;

 if($release === 0)
   return require dirname(__FILE__).'/ConfigDev.php';
 else if($release === 1)
   return require dirname(__FILE__).'/ConfigQC.php';
 else
   return require dirname(__FILE__).'/ConfigProduction.php';

?>
