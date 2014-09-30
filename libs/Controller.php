<?php
    class Model
    {
        static public $appKey = '';
        protected $key ;

        public function __construct($uId = 0,$id = '',$exKey = '')
        {
            $this->key = $uId.'_'.$id.'_'.$exKey ;
        }

        /**
        * get Magic cho cac thuoc tinh  base
        * @param
        * author : Toan Nobita
        * 9/9/2010
        */

        function __get($property)
        {
            $method = "get_{$property}" ;
            if (method_exists($this, $method))
                return $this->$method() ;
        }

        function get_uId()
        {
            $arr = explode('_', $this->key);
            return $arr[0] ;
        }

        function get_exKey()
        {
            $arr = explode('_', $this->key);
            return $arr[2] ;
        }

        function get_id()
        {
            $arr = explode('_', $this->key);
            return $arr[1] ;
        }

        function getMasterKey()
        {
            return Model::$appKey.'_'.$this->key.'_'.get_class($this);
        }

        public function save()
        {
            $key = Model::$appKey.'_'.$this->key.'_'.get_class($this); 
            StaticCache::$data[$key]  =& $this ;
            StaticCache::save($key) ;
        }

        public function delete()
        {
            Model::$appKey.'_'.$key = $this->key.'_'.get_class($this);
            StaticCache::$data[$key]  =& $this ;
            StaticCache::delete($key);
        }

        public function forceSave()
        {   $arr = explode('_', $this->key);
            return DataProvider :: set($arr[0],get_class($this),$this,$arr[1],$arr[2]);
        }


        public function forceDelete()
        {   $arr = explode('_', $this->key);
            return DataProvider :: delete($arr[0] ,get_class($this),$arr[1],$arr[2]) ;
        }

        public function forceAdd()
        {   $arr = explode('_', $this->key);
            return DataProvider :: add($arr[0] ,get_class($this),$this,$arr[1],$arr[2]) ;
        }

        public static function makeKeys($arr_uIds,$ids = '',$exKey= '',$Class = 'User')
        {
            if(!is_array($arr_uIds))
            {
                if(!is_array($ids))
                    return  array(Model::$appKey.'_'.$arr_uIds.'_'.$ids.'_'.$exKey.'_'.$Class) ;

                foreach($ids as $id)
                    $key[] = Model::$appKey.'_'.$arr_uIds.'_'.$id.'_'.$exKey.'_'.$Class ;
                return   $key ;
            }
            else
            {
                if(!is_array($ids))
                    foreach($arr_uIds as $uId)
                        $key[] = Model::$appKey.'_'.$uId.'_'.$ids.'_'.$exKey.'_'.$Class ;
                return   $key ;
            }
            return false ;   
        }

        public function setKey($uId = 0,$id = '',$exKey = '')
        {
            $this->key = $uId.'_'.$id.'_'.$exKey ;
        }
    }


    class Controller
    {
        //static public $uId = 0 ;
        static public $uId = 2199078;
        static public $session_key = 0 ;
        static public $codeId = 0 ;
        static public $access_token = 0 ;
        static public $post = array() ;
        static public $get = array() ;
        static public $config = array() ;

        public static function init()
        {
            //echo("init<br>");
            self::setUp();
            if(!Controller::$uId )
                self :: checkUser() ;
        }

        public static function setUp()
        {
            self::$get = & $_GET ;
            self::$post = & $_POST ;
            self::$config = & Common :: getConfig() ;
            Model::$appKey = Common::getSysConfig('appKey');

            if(Common::getSysConfig('userTest') && isset(self::$get['uId']))
            {
                self :: $uId  = intval(self::$get['uId']);
            }

            if(Common::getSysConfig('userTest'))
            {            
                if (isset($_SERVER['REMOTE_ADDR']) && empty(self::$codeId))
                {          
                    self::$codeId = $_SERVER['REMOTE_ADDR'];
                    if(!empty(self::$uId))
                    {
                        $infoTocken['UserId'] =  self :: $uId ;
                        DataProvider :: set(self::$codeId,'Cache',$infoTocken) ;      
                    }
                } 

            }
            else
            {
                if(isset(self::$get['code']))
                {
                    self::$codeId = self::$get['code'];
                    $infoTocken = ZingApi::getAccessTokenFromCode(self::$codeId);  
                    self::$access_token =  $infoTocken['access_token'] ;
                    $infoTocken['LastTimeUpdate'] = $_SERVER['REQUEST_TIME'] ;
                    DataProvider :: set(self::$codeId,'Cache',$infoTocken) ;  

                }
                else
                {

                    // kiem tra xem da het han thoi gian cua access token hay chua
                    // neu het han thi phai get lai access token
                    $arr = DataProvider :: get(self::$codeId,'Cache') ;  
                    if(empty($arr['access_token']))
                    {
                        header('location: http://me.zing.vn/apps/fish');
                        echo("co le nao<br>");
                        exit();
                    }

                    if($arr['LastTimeUpdate']+ $arr['expires'] <=  $_SERVER['REQUEST_TIME'])
                    {
                        //da het han 
                        $infoTocken = ZingApi::getAccessTokenFromCode(self::$codeId );  
                        self::$access_token =  $infoTocken['access_token'] ;
                        $infoTocken['LastTimeUpdate'] = $_SERVER['REQUEST_TIME'] ;
                        DataProvider :: set(self::$codeId,'Cache',$infoTocken) ;              

                    }
                    else
                    {
                        self::$access_token = $arr['access_token'];
                    }

                }

            }


        }

        /**
        * Kiểm tra xem user đã đăng nhập zingme chưa
        * @param unknown_type $code
        * @return unknown_type
        */


        static public function checkUser()
        {
            //        Debug::log('check user to get uId');
            //      return true;
            if (Common::getSysConfig('userTest'))
            {
                if (isset ($_SERVER['REMOTE_ADDR']) && empty(self::$codeId))
                {
                    self::$codeId = $_SERVER['REMOTE_ADDR'];
                }  
            }

            if(empty(self::$codeId))
            {
                return false ;
            }

            if (intval(Controller::$uId > 0))
                return true ;

            $arr = DataProvider :: get(self::$codeId,'Cache') ;

            $UserId = intval($arr['UserId']) ;
            if($UserId <= 0 ) 
            {
                $UserId = intval(ZingApi :: getUserId()) ;  
                if($UserId > 0 ) 
                {
                    $arr['UserId'] = $UserId ;
                    DataProvider :: set(self::$codeId,'Cache',$arr) ;        
                }
                else
                {
                    //header('location: http://me.zing.vn/apps/fish');
			echo("can not get userid");
                    exit();
                }

            }  
            self :: $uId = $UserId ;

            // Block User
            $blockUid = Common::getConfig('BlockUid');
            if(in_array(Controller::$uId, $blockUid))
            {
                die('Bạn không có quy�?n đăng nhập myFish');
            }
            // end Block

            if (intval(Controller::$uId > 0))
                return true ;
            else
                return false ;
        }


        static private function checkSessionInCache($session_id)
        {
            $userId = DataProvider :: get($session_id,'Cache') ;
            if (intval($userId) <= 0)
            {
                $userId = intval(ZingApi :: getUserId()) ;
                if($userId > 0 )
                {
                    DataProvider :: set($session_id, 'Cache',$userId) ;
                    DataProvider :: set($userId, 'Cache',$session_id) ;
                }
            }
            return $userId ;
        }

        /**
        * check admin
        * @return unknown_type
        */

        public function checkAdmin($uId,$pass)
        {
            if (intval($uId) <1 || empty($pass))
            {
                return false ;
            }
            $admins = & Common :: getConfig('Admin') ;
            if (key_exists($uId, $admins[1]))
            {
                if($pass != $admins[1][$uId]['pass']) 
                    return false ;

                return true ;
            }
            else if(key_exists($uId, $admins[2]))
                {
                    if($pass != $admins[2][$uId]['pass']) 
                        return false ;

                    return true ; 
            }
            else if(key_exists($uId, $admins[3]))
                {
                    if($pass != $admins[3][$uId]['pass']) 
                        return false ;

                    return true ; 
            }
            return false ;
        }

        public function checkPower($Level = 2)
        {
            $admins = & Common :: getConfig('Admin') ;
            if (key_exists(Controller::$uId, $admins[$Level]))
            {
                return true ;
            }
            return false ;
        }

        public function postOnWall($param)
        {
            $TypeFeed     = $param['TypeFeed'] ;
            $UserMsg      = $param['UserMsg'] ;
            $Icon         = $param['Icon'] ;
            $ItemName     = $param['ItemName'];
            $QuestName    = $param['QuestName'];

            // lay thong tin user

            $oUserProfile = UserProfile::getById(Controller::$uId);
            if (!is_object($oUserProfile))
            {
                //thong bao loi
                return array('Error' => Error :: NO_REGIS);
            }

            // lay thong tin user

            $oUser = User::getById(Controller::$uId);

            // kiem tra thong tin dau vao
            if ( empty($TypeFeed)|| empty($UserMsg) )
            {
                // thong bao loi
                return array('Error'=>  Error::PARAM);
            }
            // load tu file config len
            $ConfFeed = Common::getConfig('FeedWall',$TypeFeed);

            if(!is_array($ConfFeed))
            {
                return array('Error'=>  Error::NOT_LOAD_CONFIG);

            }

            /////////////

            $MsgIcon = $ConfFeed['Icon'];
            $MsgName = $ConfFeed['Name'];
            $MsgWallMsg = $ConfFeed['WallMsg'];
            $MsgNum = $ConfFeed['Num'];
            $likeMessage = $ConfFeed['LikeMessage'];
            if(!empty($Icon))
            {
                $MsgIcon = Common::getSysConfig('flashDir').'iconfeed/'.$Icon;
            }

            $likeMessage = str_replace('@username@',  $oUser->Name, $likeMessage);
            $likeMessage = str_replace('@itemname@',  $ItemName, $likeMessage);

            $MsgWallMsg = str_replace('@username@',  $oUser->Name, $MsgWallMsg);
            $MsgWallMsg = str_replace('@level@',  $oUser->Level, $MsgWallMsg);
            $MsgWallMsg = str_replace('@money@',  $oUser->Money, $MsgWallMsg);
            $MsgWallMsg = str_replace('@zmoney@', $oUser->ZMoney, $MsgWallMsg);
            $MsgWallMsg = str_replace('@energy@', $oUser->Energy, $MsgWallMsg);
            $MsgWallMsg = str_replace('@exp@',    $oUser->Exp, $MsgWallMsg);
            $MsgWallMsg = str_replace('@itemname@',$ItemName, $MsgWallMsg);
            $MsgWallMsg = str_replace('@mission@',$QuestName, $MsgWallMsg);
            // goi den ZingApi de feed len tuong
            $result = array();
            $result = $this->feedOpenApi($TypeFeed,195,$UserMsg,Common::getSysConfig('appName'),'',$MsgWallMsg,$likeMessage,'image',$MsgIcon,Common::getSysConfig('applink') );

            $oUserProfile->FeedInfo[$TypeFeed] += 1 ;
            if($result) $oUserProfile->save();
            $result = array();

            //log
            //Zf_log::write_act_log(Controller::$uId, 0, 20, 'postOnWall', 0, 0,$ConfFeed['Id']);

            $result['Error'] = Error :: SUCCESS;
            return $result;

        }

        private function feedOpenApi($TypeFeed,$template_bundle_id,$UserMessage,$name,$href,$caption,$description,$media_type,$media_src,$media_href)
        {
            $template_data = array();
            $template_data['message'] = $UserMessage;

            $hreff = Common::getSysConfig('applink');
            $media = array( "type" => $media_type,"src" =>$media_src,"href" => $media_href);



            if($TypeFeed == "inviteFriend") 
            {
                $Link_1 = Common::getSysConfig('domain') ; 
                $link_invite = $Link_1.'/web/index_invite.php?friendId='.Controller::$uId;
                $hreff = $link_invite; 
                $media = array( "type" => $media_type,"src" =>$media_src,"href" => $link_invite);
            }
            else 
            {
                $caption .= Common::getSysConfig('linkFeed'); 
            }

            $attachment = array(
            "name" => $name,
            "href" => $hreff,
            "caption" => $caption , // only support tag a, b, strong, br
            "description" => $description,  // only support tag a
            "media" => array(
            0 => $media,
            ),
            );
            $template_data['attachment'] = $attachment;


            try
            {
                $result = ZingApi::feedWall($template_bundle_id,$template_data);  
            }
            catch(Exception $fault)
            {
                $result = false; 
            }

            return $result;
        }   
    }
?>
