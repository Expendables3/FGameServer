<?php
function getTop()
  {
      //echo phpinfo();
      mysql_connect('10.198.48.84:3306','fishevent','fishevent');
      mysql_select_db('CommonEvent');
      $result = mysql_query("select * from topEventFW");
      //var_dump($result);
      while($row = mysql_fetch_array($result,MYSQL_NUM))
      {
          var_dump($row[0].'_'.$row[1].'_'.$row[2]);
      }
      //mysql_free_result($result);
  }
  //echo phpinfo();
  getTop();
   

 require_once 'testfooter.php';
// print_r($oStore);