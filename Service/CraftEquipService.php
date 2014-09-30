<?php
class CraftEquipService extends Controller
{
	public function getIngredient()
	{
		$oIngredients = Ingredients::getById(self::$uId);
		
		if (empty($oIngredients))
		{
			$skills = Common::getConfig('Param', 'CraftingEquipSkills');
			$oIngredients = Ingredients::init(self::$uId, $skills);
			$oIngredients->save();
		}
		
		$time = date('dmY', $_SERVER['REQUEST_TIME']);
		$limitGoldCrafting = Common::getConfig('Param', 'CraftingBuyPower','LimitGold');
        $limitGCrafting = Common::getConfig('Param', 'CraftingBuyPower','LimitG');
		if ($oIngredients->resetLimitGoldBuy($limitGoldCrafting, $limitGCrafting, $time))
			$oIngredients->save();
		
		$runArr['Error'] = Error::SUCCESS;
		$runArr['Ingredients'] = array(
			'Iron' => $oIngredients->Iron,
			'Jade' => $oIngredients->Jade,
			'SoulRock' => $oIngredients->SoulRock,
			'SixColorTinh' => $oIngredients->SixColorTinh,
			'PowerTinh' => $oIngredients->PowerTinh
		); 
		$runArr['CraftingSkills'] = $oIngredients->CraftingSkills;
		$runArr['RestGoldBuyPower'] = $oIngredients->RestGoldBuyPower;	
        $runArr['RestGBuyPower'] = $oIngredients->RestGBuyPower;    
		
		return $runArr;
	}
    
    public function autoRefineIngredient($params)
    {
        if ($params['Color'] < 0 || $params['Color'] > 4 || $params['Rank'] < 0)
        {                                
            return array('Error' => Error::PARAM);
        }
        
        $oStore = Store::getById(self::$uId);
        if (empty($oStore))
            return array('Error' => Error::SUCCESS);
        $listEquip = $oStore->Equipment;
        if(empty($listEquip))
        {
            return array('Error' => Error::SUCCESS); 
        }
        $oIngredients = Ingredients::getById(self::$uId);
        if (empty($oIngredients))
            return array('Error' => Error::OBJECT_NULL);
        $ingredients = array();
        foreach ($listEquip as $typeEquip => $arrEquip)
        {
              $ingredientsConf = Common::getConfig('IngredientsRefining', $typeEquip); 
              if(!empty($ingredientsConf))
              foreach($arrEquip as $idEquip => $equip)
              {
                    if($equip->Color == $params['Color'] && ($equip->Rank%10 == $params['Rank'] || $params['Rank'] == 0))
                    {
                        if(empty($ingredientsConf))
                            return array('Error' => Error::ACTION_NOT_AVAILABLE);
                            
                        $ingredients[$idEquip] = $this->refine($oIngredients, $equip, $ingredientsConf);
                        unset($oStore->Equipment[$typeEquip][$idEquip]);
                    }
              }
        }
        
        $oIngredients->save();
        $oStore->save();
        
        $runArr['Error'] = Error::SUCCESS;
        $runArr['Ingredients'] = $ingredients;
        
        return $runArr;
    }
	
	public function refineIngredient($params)
	{
		//check param
		if (!is_array($params['EquipList']))
			return array('Error' => Error::PARAM);
		$oStore = Store::getById(self::$uId);
 		if (empty($oStore))
			return array('Error' => Error::OBJECT_NULL);

		//check exist Equip
		$equipList = $params['EquipList'];
		$oIngredients = Ingredients::getById(self::$uId);
		if (empty($oIngredients))
			return array('Error' => Error::OBJECT_NULL);
			
		foreach ($equipList as $slot => $equip)
		{
			if ($equip == NULL) 
			{
				$ingredients[$slot] = NULL;
				continue;	
			}
			$typeEquip = $equip['type'];
			$idEquip = $equip['id'];
			$oEquip = $oStore->Equipment[$typeEquip][$idEquip];
			
			if (empty($oEquip))
			{
				$ingredients[$slot] = array();
			}
				
			else 
			{
				$ingredientsConf = Common::getConfig('IngredientsRefining', $typeEquip);
                
                if(empty($ingredientsConf))
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);
                    
				$ingredients[$slot] = $this->refine($oIngredients,$oEquip,$ingredientsConf);
				unset($oStore->Equipment[$typeEquip][$idEquip]);
                // log
                Zf_log::write_equipment_log(Controller::$uId, 0, 20,'deleteEquipment', 0, 0, $oEquip);
			}
		}
		
		$oIngredients->save();
		$oStore->save();
		
		$runArr['Error'] = Error::SUCCESS;
		$runArr['Ingredients'] = $ingredients;
		
		return $runArr;
	}
	
	public function craftEquip($params)
	{
		if (empty($params['SkillCraft']) || (!is_int($params['LevelCraft'])&&($params['LevelCraft'] <= 0)) || !is_int($params['Element']) || empty($params['TypeEquip']))
			return array('Error' => Error::PARAM);
			
		$skill = $params['SkillCraft'];
		$level = $params['LevelCraft'];
		$element = $params['Element'];
		$type = $params['TypeEquip'];
		
		$skills = Common::getConfig('Param', 'CraftingEquipSkills');
        
		if (!in_array($skill, $skills))
			return array('Error' => Error::PARAM);
		$oIngredients = Ingredients::getById(self::$uId);
		if (empty($oIngredients))
			return array('Error' => Error::OBJECT_NULL);
		if ($skill == 'Magic')
        {
            $craftRequire = Common::getConfig('CraftingEquip', $skill, $level);
            $craftMaxLevel = count(Common::getConfig('CraftingEquip', $skill));
        }
			
		else
        {
            $craftRequire = Common::getConfig('CraftingEquip', $type, $level);
            $craftMaxLevel = count(Common::getConfig('CraftingEquip', $type));
        }
        if(empty($craftRequire))
            return array('Error' => Error::PARAM);
        // update exp, level after add crafting levels
        $expRequire = Common::getConfig('Crafting_Exp', 'Require', 'Exp');
        $oIngredients->updateOverLevel($skill, $expRequire, $craftMaxLevel);
        // craft <= level hien tai
        $currLevelSkill = $oIngredients->CraftingSkills[$skill]['Level'];
        if ($level > ($currLevelSkill))
            return array('Error' => Error::PARAM);    
		// check item require
        $ingredientRequire = $craftRequire['Ingredients'];
		
		if (!$this->isCraftedEquip($oIngredients, $ingredientRequire))
			return array('Error' => Error::NOT_ENOUGH_CONDITION);
		else 
		{
			$rateEquip = $craftRequire['RR'];
			if (asort($rateEquip))
				$color = Common::randomIndex($rateEquip);
			//create Equip
			$rank = $craftRequire['Rank'];
			$rank = $element * 100 + $rank;
			$oUser = User::getById(Controller::$uId);
	        $oStore = Store::getById(Controller::$uId);
	        $conf_equip = Common::getConfig('Wars_'.$type);
	        $conf_equip = $conf_equip[$rank][$color];
	        
			$oEquip = new Equipment($oUser->getAutoId(),$conf_equip['Element'],$type,$rank,$color,rand($conf_equip['Damage']['Min'],$conf_equip['Damage']['Max']),rand($conf_equip['Defence']['Min'],$conf_equip['Defence']['Max']),rand($conf_equip['Critical']['Min'],$conf_equip['Critical']['Max']),$conf_equip['Durability'],$conf_equip['Vitality'], SourceEquipment::CRAFT);
			$oEquip->Author['Id'] = self::$uId;
			$oEquip->Author['Name'] = $oUser->Name;
            $oEquip->bonus = self::getBonusEquipment($type,$rank,$color);
            
			$oStore->addEquipment($type,$oEquip->Id,$oEquip);
			
			// level up			
			$expRequireLevelUp = $expRequire[$currLevelSkill + 1];
			$expAccumulated = Common::getConfig('Crafting_Exp', 'ExpCrafted', $currLevelSkill);
			$expAccumulated = $expAccumulated[$level];
			$oIngredients->accumulateCraftExp($skill, $expAccumulated, $expRequireLevelUp, $craftMaxLevel);
			
			// done
			$oUser->save();
			$oStore->save();
			$oIngredients->save();
		}
		$runArr['Error'] = Error::SUCCESS;
		$runArr['Equip'] = $oEquip;

        //log craft to do than
        Zf_log::write_act_log(Controller::$uId, Controller::$uId, 20, 'Crafting', 0, 0, $level,$skill,$type,$element);
        
        if(is_object($oEquip))
            Zf_log::write_equipment_log(Controller::$uId,0, 20, 'EquiqCrafted',0, 0,$oEquip);
            
		return $runArr;
	}
	
	public function buyPowerTinh($params)
	{
		if (!is_string($params['Type']))
			return array('Error' => Error::PARAM);
		$type = $params['Type'];
		$oIngredients = Ingredients::getById(self::$uId);
		if (empty($oIngredients))
			return array('Error' => Error::OBJECT_NULL);
			
		// buy
		$numPower = Common::getConfig('Param', 'CraftingBuyPower', 'Num');
		$oIngredients->PowerTinh += $numPower;
		// payment
		$oUser = User::getById(self::$uId);
		$numMoney = Common::getConfig('Param', 'CraftingBuyPower', $type);
		switch ($type)
		{	
			// Money
			case 'Money':
				if ($oIngredients->RestGoldBuyPower > 0)
                    $oIngredients->RestGoldBuyPower -= 1;
				else return array('Error' => Error::NOT_ACTION_MORE);
                
				if (!$oUser->addMoney(-$numMoney,'buyPowerTinh'))
					return array('Error' => Error::NOT_ENOUGH_MONEY);
                
                Zf_log::write_act_log(Controller::$uId, Controller::$uId, 23, 'BuyPowerCraft', -$numMoney, 0);
				break;
			// ZMoney
			case 'ZMoney':
                if ($oIngredients->RestGBuyPower > 0)
                    $oIngredients->RestGBuyPower -= 1;
                else return array('Error' => Error::NOT_ACTION_MORE);
                
				if (!$oUser->addZingXu(-$numMoney,'1:buyPowerTinh:1'))
					return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                
                Zf_log::write_act_log(Controller::$uId, Controller::$uId, 23, 'BuyPowerCraft', 0, -$numMoney);
				break;
		}
		
        $oIngredients->save();
		$oUser->save();
		
		$runArr['Error'] = Error::SUCCESS;
		$runArr['Money'] = $oUser->Money;
		$runArr['ZMoney'] = $oUser->ZMoney;
		
		return $runArr;
	}
	
	private function refine($oIngredients,$oEquip, $ingredientsConf){
		$level =($oEquip->Rank)%100;
		$color = $oEquip->Color;
		$option = count($oEquip->bonus);
		$Ingredients = array();
		$ingredientsConf = $ingredientsConf[$level][$color];
		foreach ($ingredientsConf as $ingredient => $value)
		{
			switch ($ingredient)
			{
				case 'SoulRock':
					foreach ($value as $rockLevel => $num)
					{
					    $Ingredients[$ingredient][$rockLevel] = $num;
					    if(empty($oIngredients->SoulRock[$rockLevel]))
						    $oIngredients->SoulRock[$rockLevel] = $num;
					    else $oIngredients->SoulRock[$rockLevel] += $num;
					}
					
					break;
				case 'PowerTinh':
                    $luck = mt_rand(0,99);
                    if ($luck < $value['Ran'])
                        $num = $value['Num'];
                    else 
                        $num = 0;
                    $Ingredients[$ingredient] = $num;
                    $oIngredients->$ingredient += $num;
                    break;
				case 'SixColorTinh':
					$luck = mt_rand(0,99);
					if ($luck < $value['Ran'])
						$num = $value['Num'][array_rand($value['Num'])];
					else 
						$num = 0;
					$Ingredients[$ingredient] = $num;
					$oIngredients->$ingredient += $num;
					break;
				default:
					
					$Ingredients[$ingredient] = $value;
					
					$oIngredients->$ingredient += $value;	
                    break;
			}
		}
		return $Ingredients;
	}
	
	private function isCraftedEquip($oIngredients, $ingredientRequire)
	{
		foreach ($ingredientRequire as $ingredient)
		{
			$type = $ingredient['Type'];
			$num = $ingredient['Num'];
			switch ($type)
			{
				case 'SoulRock':
					$level = $ingredient['Rank'];
					$oIngredients->SoulRock[$level] -= $num;
					if ($oIngredients->SoulRock[$level] < 0)
						return false;
					break;
				default:
					$oIngredients->$type -= $num;
					if ($oIngredients->$type < 0)
						return false;				
			}
		}
		return true;
	}
    
    private static function getBonusEquipment($type, $rank, $color)
    {
        $conf_bonus = Common::getConfig('EquipmentRate',$type);
        $conf_equip = Common::getConfig('Wars_'.$type,$rank,$color);
        $numOptLst = array_keys($conf_bonus['Color'][$color]);
        arsort($numOptLst) ;
        $minNumOpt = array_pop($numOptLst);
        $numOpt = Common::randomIndex($numOptLst);
        $numOpt = $numOptLst[$numOpt];
        $bonus = array();                   
        $arrConver = Common::getParam('ConvertIncreaseEquipment');
        for($i=0; $i<$numOpt;$i++)
        {
            $index = Common::randomIndex($conf_bonus['Random']);                  
            $value = rand($conf_equip[$index]['Min'],$conf_equip[$index]['Max']);      
            $bonus[$i][$arrConver[$index]] = $value;      
        }
        return $bonus;
    }    
}