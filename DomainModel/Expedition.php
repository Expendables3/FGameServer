<?php
class Expedition extends Model
{
    const POS_TRUNK_1 = 12;          //vị trí ô để các Rương trên silkRoad
    const POS_TRUNK_2 = 24;
    const POS_TRUNK_3 = 36;
    const POS_TRUNK_4 = 48;
    const POS_TRUNK_5 = 59;
    public static $CHEAT_MODE = true;
    public $idQuestCheat;
    public $rateDice = array(1 => 1000, 1000, 1000, 1000, 1000, 1000);
    public static  $RATE_DICE = array(1 => 1000, 1000, 1000, 1000, 1000, 1000);
    /*Xác suất của loại ô xuất hiện trong từng chặng*/
    
    private static $RATE_ROAD_TYPE_1 = array(1 => 1000, 1000, 1000);              //xác suất rơi vào loại ô ở từng chặng
    private static $RATE_ROAD_TYPE_2 = array(1 => 1000, 1000, 1000);
    private static $RATE_ROAD_TYPE_3 = array(1 => 1000, 1000, 1000);
    private static $RATE_ROAD_TYPE_4 = array(1 => 1000, 1000, 1000);
    private static $RATE_ROAD_TYPE_5 = array(1 => 1000, 1000, 1000);

    public static  $RATE_DECREASE_HARD = array(1 => 80, 15, 05);
    public static  $RATE_INCREASE_VALUE = array(1 => 80, 15, 05);
    
    /*Define all input and output of All Quest*/
    private static $list = array(
        'craftEquip'=>array(
        ),
        'upgradeGem'=>array(
        ),
        'enchantEquipment'=>array(
        ),
        'startTraining'=>array(
        ),
        'attackFriendLake'=>array(
        ),
        'completeDailyQuest'=>array(
        ),
        'exchangeItemCollection'=>array(
        ),
        
    );
    private $silkRoad;              //con đường viễn chinh
    //private $isFirstTime = true;           //lần đầu tiên trong ngày = true, else = false
    private $curIndex;              //vị trí hiện tại của người chơi trên con đường viễn chinh
    private $questId;               //questId hiện tại của người chơi => truy vấn ra được độ khó
    private $hardId;                //độ khó hiện tại
    private $gift;                  //một object gồm các trường: value => array("itemtype","itemid","num","color","rank")
    private $num;                   //số lượng task đã thực hiện
    private $lastTimeLog = 0;
    private $numRolling;            //số lần tung xúc sắc còn lại
    private $numCardFree;               //số lệnh bài miễn phí hiện có
    private $numCardBuy;               //số lệnh bài user mua hiện có
    
    /*getter and setter*/
    public function &getSilkRoad()
    {
        if(isset($this->silkRoad))
            return $this->silkRoad;
        else
            return null;
    }
    public function getQuestId()
    {
        if(isset($this->questId))
        {
            return $this->questId;
        }
        else{
            return -1;
        }
    }
    public function &getHardId()
    {
        if(isset($this->hardId))
        {
            return $this->hardId;
        }
        else{
            return -1;
        }
    }
    public function getGift()
    {
        if(isset($this->gift))
        {
            return $this->gift;   
        }
        else{
            return null;
        }
    }
    public function &getIndex()
    {
        if(isset($this->curIndex))
        {
            return $this->curIndex;
        }
        else{
            return null;
        }
    }
    /*
    public function setIndex($value)
    {
        if($value >=0 && $value < 50)
        {
            $this->curIndex = $value;
        }
        else{
            $this->curIndex = -1;
        }
    }
    */
    public function getLastTimeLog()
    {
        return $this->lastTimeLog;
    }
    /*
    public function setLastTimeLog($value)
    {
        $this->lastTimeLog = $value;
    }
    */
    /*
    public function setNum($value)
    {
        if($value >= 0)
        {
            $this->num = $value;
            $this->save();
        }
    }
    */
    public function &getNumCardFree()
    {
        return $this->numCardFree;
    }
    /*
    public function setNumCardFree($value)
    {
        if($value >= 0)
        {
            $this->numCardFree = $value;
        }
    }
    */
    public function &getNumCardBuy()
    {
        return $this->numCardBuy;
    }
    public function &getNumRolling()
    {
        return $this->numRolling;
    }
    /*
    public function setNumRolling($value)
    {
        if($value >= 0)
        {
            $this->numRolling = $value;
        }
    }
    */
    public function &getNumTask()
    {
        return $this->num;
    }
    /////////////////////////////////////////////////////////////////////////////////////
    public function resetLastTimeLog()
    {
        $this->lastTimeLog = 0;
        $this->save();
    }
    /**
    * hàm tạo
    * @param mixed $uId : id của user
    */
    public function __contruct($uId)
    {
        $this->silkRoad = array();
        $this->curIndex = 0;
        $this->questId = 0;
        $this->gift = array();
        $this->lastTimeLog = 0;
        $this->num = 0;
        parent::__construct($uId);
    }
    
    /**
    * hàm tạo ra con đường Viễn Chinh, lưu lại nó
    * khởi gán cho $silkRoad và lưu lại
    */
    public function createSilkRoad()
    {
        if(isset($this->silkRoad))
        {
            unset($this->silkRoad);
        }
        $this->silkRoad = array();
        /*phần tử đầu tiên*/
        $itemRoad = 0;
        $this->silkRoad[0] = $itemRoad;
        /*chặng 1 : nhiều ngư thần*/
        for($i = 1; $i < self::POS_TRUNK_1 - 1; $i++)
        {
            $itemRoad = $this->randomIndex(self::$RATE_ROAD_TYPE_1); //khởi tạo giá trị cho $itemRoad
            $this->silkRoad[$i] = $itemRoad;                        //push vào silkRoad
        }
        /*phần tử rương 1*/
        $itemRoad = 10;
        $this->silkRoad[self::POS_TRUNK_1 - 1] = 4;
        $this->silkRoad[self::POS_TRUNK_1] = $itemRoad;         //khí vận
        /*chặng 2*/
        for($i = self::POS_TRUNK_1 + 1; $i < self::POS_TRUNK_2 - 1; $i++)
        {
            $itemRoad = $this->randomIndex(self::$RATE_ROAD_TYPE_2);
            $this->silkRoad[$i] = $itemRoad;
        }
        /*phần tử rương 2*/
        $itemRoad = 20;
        $this->silkRoad[self::POS_TRUNK_2 - 1] = 4;         //khí vận
        $this->silkRoad[self::POS_TRUNK_2] = $itemRoad;
        /*chặng 3*/
        for($i = self::POS_TRUNK_2 + 1; $i < self::POS_TRUNK_3 - 1; $i++)
        {
            $itemRoad = $this->randomIndex(self::$RATE_ROAD_TYPE_3);
            $this->silkRoad[$i] = $itemRoad;
        }
        /*phần tử rương 3*/
        $itemRoad = 30;
        $this->silkRoad[self::POS_TRUNK_3 - 1] = 4;         //khí vận
        $this->silkRoad[self::POS_TRUNK_3] = $itemRoad;
        /*chặng 4*/
        for($i = self::POS_TRUNK_3 + 1; $i < self::POS_TRUNK_4 - 1; $i++)
        {
            $itemRoad = $this->randomIndex(self::$RATE_ROAD_TYPE_4);
            $this->silkRoad[$i] = $itemRoad;
        }
         /*phần tử rương 4*/
        $itemRoad = 40;
        $this->silkRoad[self::POS_TRUNK_4 - 1] = 4;         //khí vận
        $this->silkRoad[self::POS_TRUNK_4] = $itemRoad;
        /*chặng 5*/  
        for($i = self::POS_TRUNK_4 + 1; $i < self::POS_TRUNK_5 - 1; $i++)
        {
            $itemRoad = $this->randomIndex(self::$RATE_ROAD_TYPE_5);
            $this->silkRoad[$i] = $itemRoad;
        }
         /*phần tử rương 5 <=> phần tử kết thúc*/
        $itemRoad = 100;
        $this->silkRoad[self::POS_TRUNK_5 - 1] = 4;
        $this->silkRoad[self::POS_TRUNK_5] = $itemRoad;
        
    }
    
    /**
    * tạo quest tại vị trí hiện tại
    * cập nhật độ khó mới
    */
    public function createQuest($hard)
    {
        $typeQuest = $this->silkRoad[$this->curIndex];
        $conf = Common::getConfig('ExpeditionQuest');
        $lstReadyQuest = array();
//        $segment = ceil($this->curIndex / 10);
//        $hardConf = Common::getConfig('SilkRoad', 'Hard', $segment);
//        $this->hardId = $this->randomIndex($hardConf);
        $this->hardId = $hard;
        foreach($conf[$typeQuest] as $i => $v)
        {
            $numTask = $v['Hard'][$this->hardId];//xem số lượng task cần thực hiện
            if($numTask > 0)
            {
                $lstReadyQuest[$i] = 100;
                if(self::$CHEAT_MODE)
                {
                    if($i == $this->idQuestCheat)
                    {
                        Debug::log('jump to cheat');
                        $lstReadyQuest[$i] = 100000;
                        $this->idQuestCheat = -1;
                    }
                }
            }
        }
        $this->questId = $this->randomIndex($lstReadyQuest);
    }
    
    /**
    * tạo gift tại vị trí hiện tại
    */
    public function createGift($value)
    {
        $conf = Common::getConfig('ExpeditionGift');
//        $segment = ceil($this->curIndex / 10);
//        $valueConf = Common::getConfig('SilkRoad', 'Value', $segment);
//        $value = $this->randomIndex($valueConf);
        if(isset($this->gift))
            unset($this->gift);       //giải phóng $gift khởi giá trị cũ
        $this->gift = array();
        $this->gift[$value] = array();
        
        $lstGift = $conf[$value];
        $lstSure = $lstGift['Sure'];
        $lstSureReady = array();
        foreach($lstSure as $i => $giftInfo)
        {
            $lstSureReady[$i] = $giftInfo['Rate'];
        }
        
        
        $this->gift[$value]['Sure'] = array();
        $index = 0;
        for($i = 0; $i <= 1; $i++)//chọn 2 quà sure
        {
            $this->gift[$value]['Sure'][$i] = $this->getGiftFromList($lstSure, $lstSureReady, $index);
            unset($lstSureReady[$index]);//loại bỏ quà vừa được chọn để ko bị chọn lại
        }
        
        $lstMore = $lstGift['More'];
        if(count($lstMore) == 0) return;
        $lstMoreReady = array();
        foreach($lstMore as $i => $giftInfo)
        {
            $lstMoreReady[$i] = $giftInfo['Rate'];
        }
        $this->gift[$value]['More'] = array();
        
        for($i = 3; $i <= $value; $i++)
        {
            $j = $i - 3;
            $this->gift[$value]['More'][$j] = $this->getGiftFromList($lstMore, $lstMoreReady, $index);
            unset($lstMoreReady[$index]);
        }
    }
    public function changeGift($value, $oldValue)
    {
        $listGiftMore = array();
        if(isset($this->gift))
        {
            if(!empty($this->gift[$oldValue]['More']) && is_array($this->gift[$oldValue]['More']))
            {
                $listGiftMore = $this->gift[$oldValue]['More'];
            }
            unset($this->gift);
        }
        $this->gift = array();
        $this->gift[$value] = array();
        $conf = Common::getConfig('ExpeditionGift');
        $lstGift = $conf[$value];
        $lstSure = $lstGift['Sure'];
        $lstSureReady = array();
        foreach($lstSure as $i => $giftInfo)
        {
            $lstSureReady[$i] = $giftInfo['Rate'];
        }
        $this->gift[$value]['Sure'] = array();
        $index = 0;
        for($i = 0; $i <= 1; $i++)//chọn 2 quà sure
        {
            $this->gift[$value]['Sure'][$i] = $this->getGiftFromList($lstSure, $lstSureReady, $index);
            unset($lstSureReady[$index]);//loại bỏ quà vừa được chọn để ko bị chọn lại
        }
        for($i = $oldValue + 1; $i <= $value; $i++)
        {
            $lstMore = $conf[$i]['More'];
            if(count($lstMore) == 0 || $lstMore == null || empty($lstMore))
            {
                continue;
            }
            $lstMoreReady = array();
            foreach($lstMore as $k => $giftInfo)
            {
                $lstMoreReady[$k] = $giftInfo['Rate'];
            }
            $gift = $this->getGiftFromList($lstMore, $lstMoreReady);
            array_push($listGiftMore, $gift);
        }
        $this->gift[$value]['More'] = $listGiftMore;
    }
    
    /**
    * lấy về 1 quà trong $listGift dựa vào $listRate => trả về $index và $gift
    * @param mixed $listGift [in] : tập quà
    * @param mixed $listRate [in] : tập tỷ lệ
    * @param mixed $index [out] : chỉ số của quà trả về
    */
    private function getGiftFromList($listGift, $listRate, &$index = 0)
    {
        $index = $this->randomIndex($listRate);
        $temp = $listGift[$index];
        if(gettype($temp['ItemId']) == 'array')
        {
            $indexId = $this->randomIndex($temp['ItemId']);
            $temp['ItemId'] = $temp['ItemId'][$indexId];
        }
        if(gettype($temp['Num']) == 'array')
        {
            $indexNum = $this->randomIndex($temp['Num']);
            $temp['Num'] = $temp['Num'][$indexNum];
        }
        return $temp;
    }
    /**
    * giảm độ khó của quest => đổi quest
    */
    private function decreaseHard()
    {
        $conf = Common::getConfig('ExpeditionQuest');//tất cả quest
        $oldQuest = $conf[$this->silkRoad[$this->curIndex]][$this->questId];//lấy ra quest hiện tại
        $oldHard = $oldQuest['Hard'];   //độ khó hiện tại
        $oldId = $oldQuest['Id'];       //id quest hiện tại
        $dec = $this->randomIndex(self::$RATE_DECREASE_HARD);     //xem giảm độ khó bao nhiêu: 1 hoặc 2 hoặc 3
        $newHard = $this->minusSmart($oldHard, $dec, 1);
        $listReadyQuest = array();
        /*lấy ra những tập quest sẵn sàng (danh sách quest có độ khó = $newHard)*/
        foreach($conf as $typeQuest => $obj)
        {
            if($typeQuest == $this->silkRoad[$this->curIndex])
            {
                foreach($obj as $idQuest => $quest)
                {
                    if($idQuest != $oldId && $quest['Hard'] == $newHard)
                    {
                        $listReadyQuest[$idQuest] = 100;//tỷ lệ là như nhau và = 100
                    }
                }
            }
        }
        $this->questId = $this->randomIndex($listReadyQuest);       //lấy id mới trong dãy những quest sẵn sàng thay
        $this->save();
        return $this->questId;
    }
    /**
    * tăng giá trị cho quà hiện tại
    */
    private function increaseValue()
    {
        $conf = Common::getConfig('ExpeditionGift');//tất cả gift
        foreach($gift as $oldValue => $obj){};      //$oldValue là giá trị cũ, $obj là thông tin quà sure =>, more=>
        
        $inc = $this->randomIndex(self::$RATE_INCREASE_VALUE);
        $newValue = $this->plusSmart($oldValue, $inc, 5);
        
        $listSure = $conf[$newValue]['Sure'];
        $listSureReady = array();
        foreach($listSure as $i => $giftInfo)
        {
            $listSureReady[$i] = $giftInfo['Rate'];
        }
        $listMore = $conf[$newValue]['More'];
        $listMoreReady = array();
        foreach($listMore as $i => $giftInfo)
        {
            $listMoreReady = $giftInfo['Rate'];
        }
        unset($this->gift);
        $this->gift = array();
        $this->gift[$newValue] = array();
        $this->gift[$newValue]['Sure'] = array();
        $this->gift[$newValue]['More'] = array();
        $index = 0;
        for($i = 0; $i <= 1; $i++)
        {
            $this->gift[$newValue]['Sure'][$i] = $this->getGiftFromList($listSure, $listSureReady, $index);
            unset($listSureReady[$index]);
        }
        for($i = 3; $i <= $newValue; $i++)
        {
            $j = $i - 3;
            $this->gift[$newValue]['More'][$i] = $this->getGiftFromList($listMore, $listMoreReady, $index);
            unset($listMoreReady[$index]);
        }
        $this->save();
        return $this->gift;
    }
    
    /**
    * giải phóng quest hiện tại
    */
    public function freeQuestGift()
    {
        unset($this->gift);             //giải phóng gift
        $this->questId = -1;            //về trạng thái chờ quest
        $this->hardId = -1;
        $this->num = 0;                 
    }
    
    public function freeQuest()
    {
        $this->questId=-1;
        $this->hardId=-1;
        $this->num=0;
    }
    /**
    * trừ 2 số cho nhau 1 cách thông minh (không cho nhỏ hơn giới hạn)
    * @param mixed $no1 số bị trừ
    * @param mixed $no2 số trừ
    * @param mixed $limit giới hạn
    */
    public function minusSmart($no1, $no2, $limit)
    {
        $result = $no1 - $no2;
        if($result < $limit)
        {
            $result = $limit;
        }
        return $result;
    }
    /**
    * cộng 2 số cho nhau 1 cách thông minh (không cho lớn hơn giới hạn)
    * 
    * @param mixed $no1     số hạng 1
    * @param mixed $no2     số hạng 2
    * @param mixed $limit   giới hạn
    */
    public function plusSmart($no1, $no2, $limit)
    {
        $result = $no1 + $no2;
        if($result > $limit)
        {
            $result = $limit;
        }
        return $result;
    }
    /**
    * lấy thể hiện hiện tại (sẽ bao gồm cả con đường và quest, gift)
    * @param mixed $uId : id của người chơi
    */
    public static function getInstanceById($uId)
    {
        $oExpedition = DataProvider :: get($uId,__CLASS__) ;
        if (!is_object($oExpedition))
        {
            $oExpedition = new Expedition(Controller::$uId);
            $oExpedition->save();
        }
        return $oExpedition;
    }
    /**
    * lấy về 1 phần tử với tập rate đã cho
    * @param mixed $listRate : tập phần tử có rate
    */
    public function randomIndex($listRate)
    {
        $total = 0;
        foreach($listRate as $index => $value)
        {
            $total += $value;
        }
        $rand = rand(1, $total);
        $temp = $total;
        $result = -1;
        foreach($listRate as $index => $value)
        {
            $temp -= $value;
            if($rand > $temp)
            {
                $result = $index;
                break;
            }
        }
        return $result;
    }
    /**
    * lấy danh sách các action trong configQuest (chính là tên các API: feed, attackFriendLake...)
    */
    public static function getListAction()
    {
        $conf = Common::getConfig('ExpeditionQuest');
        $listAction = array();
        $i = 0;
        foreach($conf as $listTask)
        {
            foreach($listTask as $task)
            {
                $listAction[$i] = $task['Action'];
                $i++;
            }
        }
        return $listAction;
    }
    
    /**
    * cập nhật num cho action(là quest)
    * @param mixed $action tên action đang được thực hiện
    */
    /**
    * cập nhật num cho action(là quest)
    * @param mixed $action :  tên action đang được thực hiện (API)
    * @param mixed $input : tập param truyền vào cho API
    * @param mixed $output : tập giá trị trả về của API
    */
    public function update($action, $input, $output)
    {
        $conf = Common::getConfig('ExpeditionQuest');
        $typeQuest = $this->silkRoad[$this->curIndex];
        $task= $conf[$typeQuest][$this->questId];
        $curAction = $task['Action'];
        $numPlus = 0;
        if($curAction == $action)
        {
            if($action == 'cleanLake' || $action == 'run' || $action == 'feed')
            {
                $numPlus = 1;
            }
            else
            {
                $isPlusByInput = true;
                $isPlusByOutput = true;
                if(!empty($task['Input']))//có check Input
                {
                    if($action == 'acttackMonster')
                    {
                        if($task['Input']['IdMonster'] == 0)//đánh thằn lằn đỏ, hoặc rắn thường
                        {
                           Debug::log('IdRound yeu cau = '.$task['Output']['IdRound']);
                           if($task['Output']['IdRound'] == 1 && $task['Output']['IdSea'] == 4 &&
                                $output['IdRound'] == 1 && $output['IdSea'] == 4)//đánh nhau vói thằn lằn
                           {
                               if($input['IdMonster'] != 1)//không phải đánh thằn lằn xanh
                                {
                                    Debug::log('Đánh nhau thằn lằn đỏ');
                                    $task['Input']['IdMonster'] = $input['IdMonster'];
                                }
                            }
                            if($task['Output']['IdRound'] == 3 && $task['Output']['IdSea'] == 4 && 
                                $output['IdRound'] == 3 && $output['IdSea'] == 4)//đánh nhau với rắn
                            {
                                if($input['IdMonster'] != 6)//không phải đánh rắn chúa
                                {
                                    Debug::log('Đánh nhau rắn thường');
                                    $task['Input']['IdMonster'] = $input['IdMonster'];
                                }
                            }
                        }
                    }
                    $isPlusByInput = $this->checkParam($task['Input'], $input);
                }
                if(!empty($task['Output']))//có check Output
                {
                    if($action != 'attackFriendLake')
                    {
                        $isPlusByOutput = $this->checkParam($task['Output'], $output);
                    }
                }
                if($isPlusByInput && $isPlusByOutput)
                {
                    $numPlus = 1;
                }
                if($action == 'attackFriendLake')
                {
                    if(!empty($task['Output']))
                    {
                        $numPlus = 0;
                        $temp = $output['Bonus'];
                        foreach($temp as $k => $v)
                        {
                            if(($v['ItemType'] == $task['Output']['ItemType']) && ($v['ItemId'] == $task['Output']['ItemId']))
                            {
                                $numPlus = $v['Num'];
                                break;
                            }
                        }
                    }
                }
            }
            
        }
        else
        {
            $map = array('Money' => 'earnMoney',
                      'Energy' => 'useEnergy',
                      'NumMaterial' => 'collectMaterial',
                      'NumFish' => 'fishingFish');
            if(in_array($curAction, $map)) //các quest có mặt trong $map
            {
                $numPlus = $this->getNumPlus($curAction, $map);
            }
        }
        if($numPlus > 0)
        {
            $this->num = $this->plusSmart($this->num, $numPlus, $task['Hard'][$this->hardId]);   
        }
    }
    
    public function getNumPlus($curAction, $map)
    {
        $oAct = ActionQuest::getInstance();
        foreach($oAct->getActionQuest() as $action => $num)
        {
            if($curAction == $map[$action])
            {
                $result = $num;
                break;
            }
        }
        return $result;
    }
    
    public function resetExpedition()
    {
        $this->curIndex = 0;
        $this->freeQuestGift();
        $this->createSilkRoad();
        $this->numRolling = Common::getConfig('Param', 'Expedition', 'NumRollingFree');
        $this->numCardFree = Common::getConfig('Param', 'Expedition', 'NumCardFree');
        $this->lastTimeLog = $_SERVER['REQUEST_TIME'];
    }
    
    /**
    * Kiểm tra xem $param có trong $cfgParam ko?
    * @param mixed $cfgParam : tập param của task
    * @param mixed $param : tập param đem đi kiểm tra
    * @return : số Num + vào Num của task (quyết định việc hoàn thành task)
    */
    private function checkParam($cfgParam, $param)
    {
        $isPlus = true;
        foreach($cfgParam as $key => $value)
        {
            //Debug::log('search: '.$key);
            $isPlus = $isPlus && ($this->searchKey($key, $param) == $value);
        }
        return $isPlus;
    }
    /**
    * Tìm key trong cây param
    * @param mixed $key
    * @param mixed $param
    */
    private function searchKey($key, $param)
    {
        if(gettype($param) != 'array' && gettype($param) != 'object')
        {
            return -1;
        }
        $rs = -1;
        foreach($param as $k => $v)
        {
            //Debug::log('is array or object k: '.$k);
            if($key == $k && gettype($k) == 'string')
            {
                //Debug::log('key: '.$key);
                $rs = $v;
                break;
            }
            else
            {
                //Deblug::log('key: '.$k.' v: '.$v);
                $rs = $this->searchKey($key, $v);
                if($rs != -1)
                {
                    break;
                }
            }
        }
        return $rs;
    }
    
    public function getGiftChance($chanceConf)
    {
        $lstGift = array();
        $lstRate = array();
        foreach($chanceConf as $k => $giftInfo)
        {
            $lstRate[$k] = $giftInfo['Rate'];
        }
        $gift = $this->getGiftFromList($chanceConf, $lstRate);
        array_push($lstGift, $gift);
        return $lstGift;
    }
}
?>
