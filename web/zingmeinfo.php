<html>
  
  <head>
  </head>
  
  
  <body>
    
    <form method="POST" action="">
        
        
        <textarea cols="50" rows="5" id="list" name="list">
        </textarea>

        <input id="sub" name="submit" type=submit value="GetInfo">
        </input>
    
    </form>

    <?php

        if(isset($_REQUEST['submit'])){
            
            
            
            $list = $_REQUEST['list'];
            $listUser = explode("\n",$list);

            $result = "<table border=1 align='center'>";
            $result .= "<tr><td>Index</td>
                            <td>Username</td>
                            <td>Id</td>
                            <td>Email</td>
                            <td>Yahoo ID</td>
                            <td>Google ID</td></tr>";
            
            
            foreach($listUser as $index => $username)
            { 
                //$file = file_get_contents("http://me.zing.vn/trolaieobienxanh2000/profile");
                
                $fp = fsockopen('me.zing.vn', 80, $errno, $errstr, 10);

                $out = '';
                //$out .= "GET /pi/". $username ." HTTP/1.0 \r\n";
                $out .= "GET /". $username ."/profile/profileinfo HTTP/1.0 \r\n";
                $out .= "Host: me.zing.vn\r\n";
                $out .= "Cookie: ";
                foreach ($_COOKIE as $id => $value){
                    $out .= " " .$id . "=" . $value . ";";
                }
                $out .= "Connection: Close\r\n\r\n";

                fwrite($fp, $out);  //send request

                $flagYahoo = false;
                $flagEmail = false;
                $flagGoogle = false;
                
                $tk = array(); 
                while (!feof($fp))
                {
                    $line = fgets($fp, 4096); //get response
                    
                    
                    if ($flagEmail || $flagGoogle || $flagYahoo)    
                    {
                        $posB = strpos($line,"bcr");    
                        $posE = strpos($line,"&nbsp");    
                        $token = substr($line,$posB+5,$posE-$posB);
                        
                        if ($flagEmail) $tk['Email'] = $token;
                        else if ($flagGoogle) $tk['Google'] = $token;
                        else $tk['Yahoo'] = $token;
                        
                        $flagEmail = $flagGoogle = $flagYahoo = false;    
                    }

                    $posEmail = strpos($line,'Email');
                    $posYahoo = strpos($line,'Yahoo ID');
                    $posGoogle = strpos($line,'Google ID');
                    $posId = strpos($line, 'ownerId');
                    
                    if ($posEmail != false){
                        $flagEmail = true;
                    } 
                    else if ($posYahoo != false){
                        $flagYahoo = true;
                    }
                    else if ($posGoogle != false){
                        $flagGoogle = true;
                    }
                    else if ($posId != false){
                        $posE = strpos($line,'\",');
                        $tk['UserId'] = substr($line, $posId+10,$posE-$posId-2);
                    }
                    

                }
                
                $result .= "<tr><td>".($index+1)."</td><td>".$username."</td><td>".$tk['UserId']."</td><td>".$tk['Email']."</td><td>".$tk['Yahoo']."</td><td>".$tk['Google']."</td></tr>";
                fclose($fp); 
                
            }
    
            
            
            $result .="</table>";
            echo $result;   
            
        }
    ?>
    
  
  
  </body>


</html>