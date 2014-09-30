<?php
class Ingredients extends Model
{
	public $Iron = 0;
	public $Jade = 0;
	public $SoulRock = array();
	public $SixColorTinh = 0;
	public $PowerTinh = 0;
	public $RestGoldBuyPower = 0;
    public $RestGBuyPower = 0;
	public $LastDateGoldBuyPower = '';
	public $CraftingSkills = array();
    public $reachMaxLevel = false;
	
	public function __construct($uId)
	{
		parent::__construct($uId);
	}
	
	static public function init($uId,$skills)
	{
		$res = new Ingredients($uId);
		foreach ($skills as $skill)
		{
			$res->CraftingSkills[$skill]= array(
				'Level' => 1, 'Exp' => 0
			);
		}
		return $res;
	}
	
	static public function getById($uId)
	{
        $oIngredients = DataProvider::get($uId, 'Ingredients');
		if(empty($oIngredients))
        {
            $skills = Common::getConfig('Param', 'CraftingEquipSkills');
            $oIngredients = self::init($uId,$skills);
        }        
        return $oIngredients;    
	}
	
	public function resetLimitGoldBuy($limitGold, $limitG, $time)
	{
		if ($time != $this->LastDateGoldBuyPower)
		{
			$this->LastDateGoldBuyPower = $time;
			$this->RestGoldBuyPower = $limitGold;
            $this->RestGBuyPower = $limitG;
			return true;
		}
		else return false;
	}
	
	public function accumulateCraftExp($skill, $numExp, $levelExp, $maxLevel)
	{
		$this->CraftingSkills[$skill]['Exp'] += $numExp;
		$isLevelUp = ($this->CraftingSkills[$skill]['Exp'] < $levelExp)? false:true;
		if ($isLevelUp)
		{
            if($this->CraftingSkills[$skill]['Level'] < $maxLevel)
            {
                $this->CraftingSkills[$skill]['Level'] += 1;
                $this->CraftingSkills[$skill]['Exp'] -= $levelExp;
            }
            else $this->reachMaxLevel = true;
		}
	}
    
    public function updateOverLevel($skill, $levelExpRequires, $maxLevel)
    {
        $currLevel = $this->CraftingSkills[$skill]['Level'];
        $currExp = $this->CraftingSkills[$skill]['Exp'];
        
        if(!$this->reachMaxLevel)
            return;
        $cont = false;
        do
        {
            if(empty($levelExpRequires[$currLevel + 1])) break;
            $levelExp = $levelExpRequires[$currLevel + 1];
            if($levelExp <= $currExp)
                if($currLevel < $maxLevel)
                {
                    $currLevel +=1;
                    $currExp -= $levelExp;
                    $cont = true;
                    $this->reachMaxLevel = false;
                }
                else $cont = false;
        } while ($cont);
        
        $this->CraftingSkills[$skill]['Level'] = $currLevel;
        $this->CraftingSkills[$skill]['Exp'] = $currExp;       
    }
    
    //thedg25 added
    public function getPowerTinh(){
        return $this->PowerTinh;
    } 
    
    public function addPowerTinh($amount)
    {
        if ($amount < 0)
            return false;
        $this->PowerTinh += $amount;
        return true;
    }
    
    public function usePowerTinh($amount)
    {
        $amount = intval($amount);
        if ($amount < 1)
            return false;
        if ($this->PowerTinh < $amount)
            return false;
        $this->PowerTinh -= $amount;
        return true;
    }
    
    // them vao cac loai Item khac
    public function addItem($type,$num)
    {
        if(empty($type)|| $num <1)
            return false ;
        switch($type)
        {
            case Iron:
                $this->Iron += $num ;
                break;
            case Jade:
                $this->Jade += $num ;
                break;
            case SixColorTinh:
                $this->SixColorTinh += $num ;
                break;
            default :
                break ;
        }
        return true ;
    }
        
    public function addIngredient($Type, $Num, $Rank = 1)
    {
        if(!in_array($Type, array('Iron', 'Jade', 'PowerTinh', 'SixColorTinh', 'SoulRock')) || $Num < 0)
            return false;
        switch ($Type) {
               case 'SoulRock':
                   if(empty($this->SoulRock[$Rank]))
                    $this->SoulRock[$Rank] = $Num;
                else $this->SoulRock[$Rank] += $Num;
               break;
               
               default:
                   $this->$Type += $Num;
               break;
        }
        
        return true;
    }
}