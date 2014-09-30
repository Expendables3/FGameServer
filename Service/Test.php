<?php
//require_once($_SERVER['DOCUMENT_ROOT'] . "/amfphp/services/vo/demo/Person.php");
class Test
{

	function __construct()
	{
            /*$this->methodTable = array(
                "sendAnObject" => array(
                    "description" => "Tests service",
                    "access" => "remote"
                ),
                "sendAnArray" => array(
                    "description" => "Tests service",
                    "access" => "remote"
                ),
                "sendAString" => array(
                    "description" => "Tests service",
                    "access" => "remote"
                ),
                "test" => array(
                    "description" => "Tests service",
                    "access" => "remote"
                )
                "test2" => array(
                    "description" => "Tests service",
                    "access" => "remote"
                )
            );*/
	}


	/*function sendAnObject($params)
	{
	    $p = new Person();
            $p -> firstName = "firstName: Vi";
            $p -> lastName= "lastName: Tieu Bao";
            $p -> job="Job: QC";
            return $p;
	}
    function sendAnArray($params)
	{

            for ($i=0;$i<10;$i++)
                {
                    $arr=array (1,2,3,4,5);
                    $p = new Person("firstName: Vi","lastName: Tieu Bao",rand(0,10));
                    $p -> firstName = "firstName: Vi";
                    $p -> lastName= "lastName: Tieu Bao";
                    $p -> job= rand(0,100);
                    if ($i%2==0)
                    {
                        $e = "a".$i;
                        $a[$e]=$p;
                    }
                    else {
                        $e = "a".$i;
                        $a[$e]=$arr;
                    }
                }
            return $a;
	}*/

    /*function test($params)
	{
            return "test: ".$params['arg'];
	}
    function test2($params)
	{
            //var_dump($params);
            return "test2: ".$params['arg']["a"];
	}*/


    function sendString($params)
	{
	  $DecoConfig = Common :: getConfig('Other') ;
	    //$p="qwdrjalnflasgfas.dgms.gnsdlgnsdlgnsd,.n./ghsdmghdmhbdbmd./bnd/snmbhl/db.db.dgnhlsfnhlfnblfd";
            return $DecoConfig;
	}
}

?>