<?php
    

require_once("testheader.php");
require_once("../Service/CheatService.php");  

function getListParam()
{
    $class = new ReflectionClass("CheatService");
    $methods = $class->getMethods();
    foreach($methods as $reflectionMethod){
        $reflectionParameter = $reflectionMethod->getParameters();
        $parameters = array();
        foreach($reflectionParameter as $parameter){
            $parameters[] = $parameter->getName();
        }
        $methodTable[$reflectionMethod->getName()]['arg'] = $parameters;
        
        $com = $reflectionMethod->getDocComment();
        
        
               
        $methodTable[$reflectionMethod->getName()]['comment'] = $com;
        //$methodTable[$reflectionMethod->getName()]['comment'] = preg_replace("/[^A-Za-z0-9\-\ ]/","",$com);
    }
    return $methodTable;    
}

function standardizeComment_forDetail($com)
{
    $com = str_replace("/**","",$com);
    $com = str_replace("*/","",$com);
    $com = str_replace("*","<br/>",$com); 
    return $com;  
}

function standardizeComment_forTooltip($com)
{
    $com = str_replace("/**","",$com);
    $com = str_replace("*/","",$com);
    $arrComment = explode("*",$com);
    return preg_replace("/[^A-Za-z0-9\-\=\:\ ]/","",$arrComment[1]);  
}

function getCodeHTML()
{
    require_once('cheat_category.php');
    $listParam = getListParam();
    
    //var_dump($listParam);
    
    ksort($listParam); 
    $kk = "<center><h1>List Functions</h1></center  >";
    //$kk .= "<form method=POST onmouseover=\"ddrivetip('This DIV has a tip!!.','lightgreen', 250)\"; ONMOUSEOUT=\"hideddrivetip()\">";
    $kk .= "<form method=POST>";
    
    $kk .= "<table border=0 width='100%'>";
    //echo $_GET['scrw'];
    foreach($category as $cate => $listCate)
    {
        
        ksort($listCate);
        $kk .= "<tr><td width='10%' height='50px'><b>".$cate."</b></td><td width='90%'>";
        $count = 0;
        foreach($listCate as $id => $oMethod)
        {
            $tt = $listParam[$oMethod]['comment'];
            //echo $tt . "<br/>";
            //var_dump($tt);
            $count++; 
            $kk .= "<input type='submit' value='". $oMethod ."' style='width:10%'  name='" .$oMethod . 
                "' ONMOUSEOVER=\"ddrivetip('". standardizeComment_forTooltip($listParam[$oMethod]['comment']) ."','#DFDFFF')\"; ONMOUSEOUT=\"hideddrivetip()\"" . " ></input>";
            if (!$count%5){
                echo $count;
                $kk .= "<br/>";
            }        
        }
        //$kk .= "<hr size = 2 width=100% noshade></td></tr>";  
        $kk .= "</td></tr>";
    }
    
    /*
    $kk .= "<table border=0 width='100%'>";
    foreach($listParam as $methodName => $listArg)
    {
        $kk .= "<tr><td width='20%'>".$methodName."</td><td><input type='submit' value='". $methodName ."' style='width:150px' name='" .$methodName. "' ></input></td></tr>";    
        //$kk .= $methodName . ' ';
    }
    $kk .= "</table>";
    */
    
    $kk .= "</table>"; 
    $kk .= "</form>";
    $kk .= "<hr size = 5 width=100% noshade> <p></p>";
    return $kk;
}

function viewParam()
{
    
    $listPr = getListParam();
    foreach($listPr as $method => $listArg)
    {
        if(isset($_REQUEST[$method]))
        {
            $_SESSION["mtname"] = $_REQUEST[$method];       
        }
    }
            
    $field = "Method : &nbsp &nbsp &nbsp &nbsp<b>". $_SESSION["mtname"] ." </b><br/>";
    $field .= "Description : &nbsp <i>".standardizeComment_forDetail($listPr[$_SESSION["mtname"]]['comment'])."</i><br/>";
    $field .= "<form method=POST>";
    $field .= "<table border='0'>";
    foreach($listPr[$_SESSION["mtname"]]['arg'] as $id => $argName)
    {
        if (!isset($_SESSION[$argName]))
            session_register($argName);
        $field .= "<tr>";
        $field .= "<td> " . $argName. "</td>";
        $field .= "<td>" . "<input type='text' size='10' length='10' name='" .$argName. "' value='".$_SESSION[$argName]."' ></input>" . "</td>";
        $field .= "</tr>";
    }
    $field .= "</table>";
    $field .= "<br/><input type='submit' value='  Cheat  ' width='150px' name='Call_".$_SESSION["mtname"]."' ></input>";    ;

    $field .= "</form>";    

    $field .= "<hr size = 5 width=100% noshade> <p></p>";
    $field .= "<h3>Result </h3><br/>";
    
    return $field;
}


function viewResult()
{
    $re = "";
    $listPr = getListParam();
    foreach($listPr as $method => $listArg)
    {
        if (isset($_REQUEST['Call_'.$method]))
        {   
            $_SESSION["aaa"] = $_REQUEST['Call_'.$method];
            $arr = array();
            foreach($listArg['arg'] as $id => $param)
            {   
                //echo $param. "!!";                                    
                $arr[] .= $_REQUEST[$param];
                //session_register($param);
                //$_SESSION[$param] = $_REQUEST[$param]; 
                
            }

            foreach($listArg['arg'] as $id => $param)
            {   
                //echo $param. "!!";                                    
                //$arr[] .= $_REQUEST[$param];
                if (!isset($_SESSION[$param]))
                    session_register($param);
                $_SESSION[$param] = $_REQUEST[$param]; 
                
            }
            
            
            require_once("../Service/CheatService.php");
 
            $tt = new CheatService();
            $re .= var_export(call_user_func_array(array($tt, $method),$arr));  
            StaticCache::forceSaveAll();
            
            
             
        }       
    }
    
    
    
    return $re;
}


?>  
