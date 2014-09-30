<html>
  
  <head>
  </head>
  
  
  <body>
    <form method="POST" action="">
        
        
        <table>
          <tr>
            <td><textarea cols="20" rows="1" id="list" name="list1">
        </textarea></td>
            <td><input id="sub" name="submit1" type=submit value="Gold award"> </td>
          </tr>
        
        
        
        
          <tr>
            <td><textarea cols="20" rows="3" id="list" name="list2">
        </textarea></td>
            <td><input id="sub" name="submit2" type=submit value="Silver award"></td>
          </tr>
        
        
        
        
          <tr>
            <td> <textarea cols="20" rows="5" id="list" name="list3">
        </textarea></td>
            <td><input id="sub" name="submit3" type=submit value="Bronze award">
        </input></td>
          </tr>
        </table>
        

        
       
       
        
    
    </form>

    <?php
        
        if(isset($_REQUEST['submit1']) || isset($_REQUEST['submit2']) || isset($_REQUEST['submit3'])){
            $list = array();
            if(isset($_REQUEST['submit1']))
                $list = $_REQUEST['list1'];
            else if(isset($_REQUEST['submit2']))
                $list = $_REQUEST['list2']; 
            else $list = $_REQUEST['list3']; 
            
            $listUser = explode("\n",$list);
            $data[0] = 0;
            $ii = 1;
            $data2 = "";
            $data3 = "";
            
            
            $arrBonus = array();
            
            // material
            $oMaterial = array();
            $oMaterial[Type::ItemType] = 'Material';
            $oMaterial[Type::Num] = 10;
            
            $oMaterial[Type::ItemId] = 6;
            $arrBonus[1][] = $oMaterial;
            $oMaterial[Type::ItemId] = 5;
            $arrBonus[2][] = $oMaterial;
            $oMaterial[Type::ItemId] = 4;
            $arrBonus[3][] = $oMaterial;
            
            
            //ZMoney
            $oZmoney = array();
            $oZmoney[Type::ItemType] = 'ZMoney';
            
            $oZmoney[Type::Num] = 10000;
            $arrBonus[1][] = $oZmoney;
            $oZmoney[Type::Num] = 1000;
            $arrBonus[2][] = $oZmoney;
            $oZmoney[Type::Num] = 100;
            $arrBonus[3][] = $oZmoney;
            
            
            foreach($listUser as $index => $uid)
            { 

                $userid = intval(trim($uid));
                $oUser = User::getById($userid);
                if (!is_object($oUser))
                  echo " ".$userid." wrong !";
                
                if(isset($_REQUEST['submit1']))
                {
                    $oUser->saveBonus($arrBonus[1]);

                    // bonus SpiderMan
                    $otion = array();
                    $option[OptionFish::EXP] = 30;
                    $option[OptionFish::MONEY] = 30;
                    $option[OptionFish::TIME] = 30;
                    $autoId = $oUser->getAutoId();
                    $oSpiderman = new Sparta($autoId,$option,90);
                    
                    $oStore = Store::getById($userid);
                    $oStore->addOther(Type::Spiderman, $oSpiderman->Id,$oSpiderman);
                    
                    $oStore->save();
                    $oUser->save();
                }
                else if(isset($_REQUEST['submit2']))
                {
                    $oUser->saveBonus($arrBonus[2]);

                    // bonus batman
                    $otion = array();
                    $option[OptionFish::EXP] = rand(16,20);
                    $option[OptionFish::TIME] = rand(16,20);
                    $autoId = $oUser->getAutoId();
                    $oSpiderman = new Sparta($autoId,$option,30);
                    
                    $oStore = Store::getById($userid);
                    $oStore->addOther(Type::Spiderman, $oSpiderman->Id,$oSpiderman);
                    
                    $oStore->save();
                    $oUser->save();
                }
                else
                {
                    $oUser->saveBonus($arrBonus[3]);

                    // bonus batman
                    $otion = array();
                    $option[OptionFish::TIME] = rand(11,15);
                    $autoId = $oUser->getAutoId();
                    $oSpiderman = new Sparta($autoId,$option,7);
                    
                    $oStore = Store::getById($userid);
                    $oStore->addOther(Type::Spiderman, $oSpiderman->Id,$oSpiderman);
                    
                    $oStore->save();
                    $oUser->save();
                };
                echo "<br/>".$userid." : Done !"; 
            }
              
            StaticCache::forceSaveAll();
            

            
        }
        
    ?>
    
  
  
  </body>


</html>