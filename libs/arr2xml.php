<?php
/**
 * associative array to xml transformation class
 *
 * @PHPVER    5.3
 *
 * @author    HungNM2
 * @ver        0.1
 * @date    05/10/2010
 */




class arr2xml
{
    public function __construct()
    {

    }
    function array_to_xml($XMLFile, $array, $level=2)
    {
        $lower = true;
        $xml = '';
        if ($level <=3 && $XMLFile != "SeriesQuest" && $XMLFile != "DailyQuest") {
            $xml .= str_repeat("\t",$level)."<listname>".
                    "\n".str_repeat("\t",$level+1)."<name>$XMLFile</name>\n";
                    $level++;
        }
        if ($XMLFile == "param") $lower = false;
        foreach ($array as $key=>$value) {
            if (is_Numeric($key) && $XMLFile != 'lake')
            {
              $key = $XMLFile.$key;
              //echo "<br>key: $key<br>";
            }
            if ($lower)  $key = mb_strtolower($key,'UTF-8');
            if (is_array($value)) {
                if ($XMLFile == "param" || $key == "param") $lower = false;
                if ($XMLFile != "lake")
                {
                    if ($level <=3 && $XMLFile != "SeriesQuest" && $XMLFile != "DailyQuest" )
                        $xml .= str_repeat("\t",$level)."<item>\n";
                    else
                        $xml .= str_repeat("\t",$level+1)."<$key>\n";
                }else
                {
                    $xml .= str_repeat("\t",$level)."<lake>\n";
                    $xml .= str_repeat("\t",$level+1)."<Id>$key</Id>\n";
                }

                $multi_tags = false;
                foreach($value as $key2=>$value2) {
                    if ($XMLFile == "param" || $key == "param" || $key2 == "param") $lower = false;
                    if (is_Numeric($key2))
                    {
                      if ($XMLFile != 'lake')
                          $key2 = $key.$key2;
                      else $key2 = "level".$key2;
                      //echo "<br>key2: $key2<br>" ;
                    }
                    if ($lower) $key2 = mb_strtolower($key2,'UTF-8');
                    if (is_array($value2)) {
                        $xml .= str_repeat("\t",$level+2)."<$key2>\n";
                        $xml .= $this->array_to_xml($key2,$value2, $level+2);
                        $xml .= str_repeat("\t",$level+2)."</$key2>\n";
                        $multi_tags = true;
                    } else {
                        $xml .= str_repeat("\t",$level+2).
                                "<$key2>$value2</$key2>\n";
                        $multi_tags = true;
                    }
                }
                if (!$multi_tags && (count($value)+1>0)) {
                    $xml .= str_repeat("\t",$level+1)."<$key>\n";
                    $xml .= $this->array_to_xml($key,$value, $level+2);
                    $xml .= str_repeat("\t",$level+1)."</$key>\n";
                }
                if ($XMLFile != "lake")
                {
                    if ($level <= 3 && $XMLFile != "SeriesQuest" && $XMLFile != "DailyQuest")
                        $xml .= str_repeat("\t",$level)."</item>\n";
                    else
                        $xml .= str_repeat("\t",$level+1)."</$key>\n";
                }else
                {
                    $xml .= str_repeat("\t",$level)."</lake>\n";
                }
            } else
             {
               $xml .= str_repeat("\t",$level+1).
                      "<$key>$value</$key>\n";
            }
        }
        if ($level==3) {
            $level--;
            $xml .= str_repeat("\t",$level)."</listname>\n";
        }
        return $xml;
    }
}


 ?>
