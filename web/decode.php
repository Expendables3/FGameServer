<html>
  
  <head>
  </head>
  
  
  <body>
    <form method="POST" action="">
        
        
        <textarea cols="50" rows="2" id="list" name="list2">
        </textarea>
        <input id="sub" name="submit2" type=submit value=" Decode ">
          
 
        
    
    </form>

    <?php
        
        if(isset($_REQUEST['submit2'])){
            $aa = $_REQUEST['list2'];
            $len = strlen($aa);
            $cc = strrev(substr($aa,0, $len-2));
            $dd = substr($aa, $len-2,2);
            $args2 = $cc.$dd;
            
            $args2 = base64_decode($args2); 
            $args3 = json_decode($args2,true);
            var_dump($args3);    
        }
        
    ?>
    
  
  
  </body>


</html>