<html>
<head>
</head>
<body>

<?php

   //error_reporting(E_ALL); //=> show all
   //error_reporting(0); //=> show all 
    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service/" );
    define( "LIB_DIR" , ROOT_DIR ."/libs/" );

    require LIB_DIR.'/DataRunTime.php';
    require LIB_DIR.'/Common.php'; 
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php';  

   class TypeAttack
   {
       const AttackFriend   = 1;
       const AttackSea_1    = 2;
       const AttackSea_2    = 3;
       const AttackSea_3    = 4;
       
   }
   
   class exe
   {
       
       public function aaa($uId,$LakeId,$SoldierId,$TypeActtack)
       {
           /* $uId = 358245;
            $LakeId = 1;
            $SoldierId = 111;
            $TypeActtack = TypeAttack:: AttackFriend ;*/
            /*echo '===========';
            echo $uId.'_';
            echo $LakeId.'_';
            echo $SoldierId.'_';
            echo $TypeActtack.'_';
            echo '===========';*/
            Model::$appKey = Common::getSysConfig('appKey');
            $oUser = User::getById($uId);
            $oLake = Lake::getById($uId,intval($LakeId));
            if(!is_object($oLake))
                return 'Wrong LakeId' ;
            $oSoldier = $oLake->getFish($SoldierId);
            if(!is_object($oSoldier)|| !isset($oSoldier->Element))
                return 'Wrong $SoldierId' ;
                
            $attackIndex = array();
            if($TypeActtack == 1 ||$TypeActtack == 2 ||$TypeActtack == 3)
            {
                $attackIndex[$SoldierId] = $oSoldier->getIndex($uId);
                $attackIndex[$SoldierId]['Element'] = $oSoldier->Element;
                $attackIndex[$SoldierId]['Base'] = $oSoldier->getIndexBase($uId);
				$attackIndex[$SoldierId]['CurrentHealth'] = $oSoldier->getHealth();
				$attackIndex[$SoldierId]['MaxHealth'] = $oSoldier->getMaxHealth();
            }
            else if($TypeActtack == 4)
            {
                $FishWorld = FishWorld::getById($uId);
                if(!is_object($FishWorld))
                    return 'Wrong FishWorld' ;
                $oIceSea = $FishWorld->getSea(3);
                if(!is_object($oIceSea))
                return 'Wrong IceSea' ;
                
                 $attackIndex[$SoldierId] = $this->calculationIndexSoldier($oSoldier,$uId);
                 $attackIndex[$SoldierId]['Element'] = $oSoldier->Element;
                 $attackIndex[$SoldierId]['Base'] = $oSoldier->getIndexBase($uId);
				 $attackIndex[$SoldierId]['CurrentHealth'] = $oSoldier->getHealth();
				 $attackIndex[$SoldierId]['MaxHealth'] = $oSoldier->getMaxHealth();
            }
            $arr = var_export($attackIndex);
            return $arr ;
       }
       
       
      public function calculationIndexSoldier($oSoldier,$uId)
      {
          $arrIndex = array(); 
          $userId = $uId;
          $oStoreEquip = StoreEquipment::getById($userId);
          $listIndex = Common::getParam('SoldierIndex');
          foreach($listIndex as $name)
          {
              // get from base + equipment
              $arrIndex[$name] += $oSoldier->$name + $oStoreEquip->SoldierList[$oSoldier->Id]['Index'][$name];
              //get from meridian 
              if(!empty($oStoreEquip->listMeridian[$oSoldier->Id][$name]))
              {
                  $arrIndex[$name] += $oStoreEquip->listMeridian[$oSoldier->Id][$name];
              }
          }
          // phan buff them cua IceWave
           $arrIndex['Element'] = $oSoldier->Element ;
           $arrIndex = $this->iceWaveEffect($arrIndex);

          // get from BuffItem
          $arrIndex['Damage'] += $oSoldier->getDamageBuffItem();
          // get from Gem
          $funcGem = SoldierFish::getFunctionGem($oSoldier->GemList, $oSoldier->Element, $arrIndex['Damage']);
          $arrIndex['Damage'] += $funcGem['Damage'];
          $arrIndex['Defence'] += $funcGem['Defence'];
          $arrIndex['Vitality'] += $funcGem['Vitality'];
          $arrIndex['Critical'] += $funcGem['Critical'];
          
          return $arrIndex;
      }
      
       // phan tinh toan buff cua song bang
      public function iceWaveEffect($arr)
      {
          $confEffect = Common::getWorldConfig('IceWave',1);
          if(empty($confEffect)) 
            return $arr ;
          $arr['Damage'] = round($arr['Damage'] + $arr['Damage']*$confEffect[$arr['Element']]/100);
          return $arr ;
          
      }
       
   }
?>

<div>
    <form action="" method="post" name="form1" action="getSoldierInfo.php">
    UserId :<input type="text" size="20" name="UserId"><br>
    LakeId :<input type="text" size="20" name="LakeId"><br>
    SoldierId :<input type="text" size="20" name="SoldierId"><br>
    TypeActtack :<select name='TypeActtack'>
    <option value="<?php echo TypeAttack::AttackFriend ; ?>">AttackFriend</option>
    <option value="<?php echo TypeAttack::AttackSea_1 ; ?>">AttackSea_1</option>
    <option value="<?php echo TypeAttack::AttackSea_2 ; ?>">AttackSea_2</option>
    <option value="<?php echo TypeAttack::AttackSea_3 ; ?>">AttackSea_3</option>
    </select><br>
    <input type="submit" name="ok" value="ok"><br>
    <input type="reset" name="reset" value="reset"><br>
    </form>
</div>
      
<?php

/*$uId = $_GET['uid'];
$LakeId = $_GET['lakeid'];
$SoldierId = $_GET['soldierid'];
$TypeActtack = $_GET['type'];*/

$uId = $_POST['UserId'];
$LakeId = $_POST['LakeId'];
$SoldierId = $_POST['SoldierId'];
$TypeActtack = $_POST['TypeActtack'];

$ob = new exe();
$result = $ob->aaa($uId,$LakeId,$SoldierId,$TypeActtack);
echo '<pre>';
echo $result ;
echo '</pre>';
?>
</body>
</html>