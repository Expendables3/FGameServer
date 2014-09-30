<?php
//session_start();
class Admin
{

     public $post = array() ;
     public $get = array() ;
     public $config = array() ;
   
   public $nameGame ;
   public $side = 1;

    protected $tip = '';
    protected $class_tab1;
    protected $class_tab2;
    protected $class_tab3;
    protected $class_tab4;
    protected $class_tab5;
    protected  $url ;
    var $page = 1; // Current Page
    var $perPage = 10; // Items on each page, defaulted to 10
    var $showFirstAndLast = false; // if you would like the first and last page options.
    var $implodeBy=' ';

    public function __construct()
    {
        $this->class_tab1='';
        $this->class_tab2='';
        $this->class_tab3='';
        $this->class_tab4='';
        $this->class_tab5='';
        $this->url =$this->config['domain_admin'];
        $this->get = & $_GET ;
        $this->post = & $_POST ;
        $this->config = & Common :: getConfig() ;
    }
    public function run()
    {
        $this->switchClass(1);
        include TPL_DIR . '/index.php';
    }

    // tab menu active
    private function switchClass($tab=1)
    {
        $tabnumber='class_tab'.$tab;
        $this->$tabnumber='active';
    }
// phân trang
    public function generate($array, $page=1,$perPage = 10)
    {
      // Assign the items per page variable
      if (!empty($perPage))
        $this->perPage = $perPage;

      // Assign the page variable
      if (!empty($page))
        $this->page = $page; // if we don't have a page number then assume we are on the first page

      // Take the length of the array
      $this->length = count($array);

      // Get the number of pages
      $this->pages = ceil($this->length / $this->perPage);

      // Calculate the starting point
      $this->start  = ceil(($this->page - 1) * $this->perPage);

      // Return the part of the array we have requested
      return array_slice($array, $this->start, $this->perPage);
    }

    public function traogiai()
    {
    if(!Controller::checkPower(1))
        return ;
    include TPL_DIR.'/bonusForTopEvent.php' ;
    }
    
    // send mail to all user or one user
    public function systemMail()
    {
        
        if(!Controller::checkPower(2))
            return false;
        $block = 1;
        $notify = false ;
        $MailType = $_POST['MailType'] ;
        if(isset($MailType) && $MailType > 0)
        {
            if($MailType == 1) // he thong
            {
                $block = 2;
            }
            else if($MailType == 2) // owner User
            {
                $block = 3; 
            }
            else if($MailType == 3) // delete mail
            {
                $block = 4; 
            }
            
        }
        // thuc hien send thong tin
        if(isset($_POST['SendMail']))
        {
            $Content = $_POST['Content'] ;
            
            if($MailType == 1 && !empty($Content)) // for all user
            {
                $SystemUser = -111 ;
                try
                {
                    $SystemNotify = DataProvider::get($SystemUser,'SystemNotify');                   
                    $MailTotal = $SystemNotify['MailTotal'] +1;
                    $SystemNotify[$MailTotal]['Content'] = $Content ;
                    $SystemNotify['MailTotal'] = $MailTotal ;
                    DataProvider::set($SystemUser,'SystemNotify',$SystemNotify);                   
                    $notify = true ;
                }
                catch(Exception $e)
                {
                    echo $e->getMessage();
                }
                
            }
            
            $UserList = $_POST['UserList'] ;
            if($MailType == 2 && !empty($Content)&& !empty($UserList) ) // for all user
            {
                $SystemUser = -111 ;
                $UserList = explode(',',$UserList);
                foreach($UserList as $index =>$uId)
                {
                    $oUser = User::getById($uId);
                    if(!is_object($oUser))
                    {
                       
                        continue ;
                    }
                    $oSysMail = SystemMail::getById($uId);
                    $oSysMail->add($SystemUser,$Content);
                    $oSysMail->save();
                    $notify = true ;
                }
                
            }
            
        }
        
        if($MailType == 3 && $block == 4  ) // xoa mail toan he thong
        {
            try
            {   $SystemUser = -111 ;
                $AllSystemMail = DataProvider::get($SystemUser,'SystemNotify');                   
            }
            catch(Exception $e)
            {
                echo $e->getMessage();
            }
        }
        
        if($_POST['DeleteMail'])
        {
            echo 'xoa du lieu';
                try
                {   $SystemUser = -111 ;
                    $SystemNotify = DataProvider::get($SystemUser,'SystemNotify');                   
                    
                    unset($SystemNotify[$_POST['DeleteMail']]);
                    DataProvider::set($SystemUser,'SystemNotify',$SystemNotify);                   
                    $notify = true ;
                }
                catch(Exception $e)
                {
                    echo $e->getMessage();
                }
        }
        
        StaticCache::forceSaveAll();
        include TPL_DIR.'/SystemMail.php' ;
    }
    
    function gmtool()
    {
        $block = 1;
        $uId = 0;
        $mess ="";

        if(!Controller::checkPower(1))
          return false ;
        if((isset($_POST['uid']) && intval($_POST['uid'])>0) || (isset($_GET['uid']) && intval($_GET['uid'])>0) )
        {
            if(isset($_POST['uid']))
                $uId = intval($_POST['uid']);
            else if(isset($_GET['uid']))
                $uId = intval($_GET['uid']);
            if(isset($_GET['return']))
                $messReturn = $_GET['return'];

            ##### user

            $oUser =  User::getById($uId);

            if(!is_object($oUser))
            {
                $mess = "Khong ton tai User nay";
                $block = 1;
            }
            else
            {
                $_SESSION['uId'] = $uId ;
                
                $ViewData = $this->viewAllInfoUser($uId);
                
                //$configItem = &Common::getConfig('ItemAll','Add');
                
                $block = 2;
            }
        }
        else
        {
                $block = 1;
        }

        include TPL_DIR . '/gmtool.php';
    }
  
  public function design_index()
  {      
      if(!Controller::checkPower(2))
        return false ;
      
      if($_POST['GameName'] != $this->nameGame && !empty($_POST['GameName'])  )
      {
         $this->nameGame = $_POST['GameName'];
         $this->side = 2 ;       
      }
      else
      {
         $this->side = 1 ;
      }
      $this->design() ;  
  }
  public function design()
  {
    $messge = '';
    $key = $this->nameGame;
    if(empty($key))
    {
      $key = $_REQUEST['namegame'];  
      $this->nameGame = $key ; 
      if(empty($key))
      {
        echo 'lam gi co key';   
      }
    } 
    if($this->side == 2)
    {
      // get du lieu ra              
      $keyword = 'designTool' ;  
      $return =  DataProvider::get($key,$keyword);
      if($return == false)
      {
         $messge = 'du lieu ko co';
      }    
      $ViewData = json_encode($return);
      $ViewData = json_decode($ViewData,true) ; 
      //------------------
      
      // hien lai lich su 
      $TempKey = "Old_{$key}" ;
      $oldData = DataProvider::get($TempKey,$keyword);
      //$oldData = array_reverse($oldData);
      $oldData = json_encode($oldData);
      $oldData = json_decode($oldData,true); 
    } 
    
    if(isset($_POST['submit_add']))
    {      

       $this->side = 3 ;
    }    
    if(isset($_POST['addNewField']))
    {
        if(empty($key))
        {
           echo 'ko co key dau' ; 
        } 
        else
        { 
          $keyword = 'designTool' ;
          $content  = $_POST['TextContent'];
          $Link     =  $_POST['LinkContent'];   
          $Per      =  intval($_POST['Percent']);
          if(empty($content)||empty($Link)||empty($Per))
          {
               $messge = 'ban nhap thieu thong tin ';
               echo 'ban nhap thieu thong tin ';  
          }
          else if(!is_integer($Per))
          {
              $messge = 'ban nhap sai dinh dang thong tin'; 
          }
          else
          {
            $arr_input = array();
            $arr_input['content'] = $content;
            $arr_input['Link'] = $Link; 
            $arr_input['Per'] = $Per; 
            
            $return =  DataProvider::get($key,$keyword);    
            $return[]= $arr_input ; 
            
            $result = DataProvider::set($key,$keyword,$return);
            if($result ==false)
            {
              $messge = 'luu tru thong tin bi loi ';  
            }
            $messge = 'luu tru thanh cong ';   
          }
        }
        $this->side = 1 ;
         
     }
     
     // xoa o phan chinh 
     if(isset($_POST['deleteField']))
     {

       $id = substr($_POST['deleteField'],3) ;
        
        if(empty($key))
        {
           echo 'ko co key dau' ; 
        } 
        $keyword = 'designTool' ;
        $return =  DataProvider::get($key,$keyword);
        
        $TempKey = "Old_{$key}" ;
        $oldData = DataProvider::get($TempKey,$keyword);
        
        if(empty($return[$id]))
        {
          $messge = 'ko co du lieu truong nay ';  
        }
        else
        {
          // luu vao trong lich su
          $oldData = DataProvider::get($TempKey,$keyword);
          $oldData[]= $return[$id] ; 
          DataProvider::set($TempKey,$keyword,$oldData);

          // xoa o phan chinh
          unset($return[$id]);
          $result = DataProvider::set($key,$keyword,$return);
          if($result ==false)
          {
            $messge = 'xoa bi loi ';  
          }
        }

        $messge = 'xoa thanh cong ';  
        $this->side = 1 ;   
     }
     
     // xoa phan lich su 
     if(isset($_POST['deleteHistory']))
     {

       $id = substr($_POST['deleteHistory'],3) ;
        
        if(empty($key))
        {
           echo 'ko co key dau' ; 
        } 
        echo $id ;  
        $TempKey = "Old_{$key}" ;
        $keyword = 'designTool' ; 
        $oldData = DataProvider::get($TempKey,$keyword);
        
        if(empty($oldData[$id]))
        {
          $messge = 'ko co du lieu truong nay ';  
        }
        else
        {
          // xoa
          unset($oldData[$id]);
          $result = DataProvider::set($TempKey,$keyword,$oldData);
          if($result ==false)
          {
            $messge = 'xoa bi loi ';  
          }
        }

        $messge = 'xoa thanh cong ';  
        $this->side = 1 ;   
     }    
            
     include TPL_DIR . '/design_2.php';  
    }
  
  // quan ly banner tren fish
  public function BannerManager()
  {
      if(!Controller::checkPower(2))
        return  ;
        $messge = '';
        // get du lieu ra              
        $key = 'MyFish_BannerManager' ;  
        $return =  DataProvider::get($key,'designTool');
        if($return == false)
        {
         $messge = 'du lieu ko co';
        }    
          
        if(isset($_POST['AddNewBanner']))
        {
            $BannerName  = $_POST['BannerName'];
            $Percent      =  intval($_POST['Percent']);
            if(empty($BannerName)||empty($Percent))
            {
                $messge = 'ban nhap thieu thong tin ';
            }
            else
            {
                $arr_input = array();
                $arr_input['BannerName']    = $BannerName;
                $arr_input['Percent']       = $Percent; 

                $return_1 =  DataProvider::get($key,'designTool');    
                $return_1[]= $arr_input ; 

                $result = DataProvider::set($key,'designTool',$return_1);
                if($result ==false)
                {
                  $messge = 'luu tru thong tin bi loi ';  
                }
                $messge = 'luu tru thanh cong ';   
            }
        }
        
        // xoa o phan chinh 
        if(isset($_POST['deleteField']))
        {

            $id = substr($_POST['deleteField'],3) ;

            if(empty($key))
            {
                echo 'ko co key dau' ; 
            } 
            $return_2 =  DataProvider::get($key,'designTool');

            if(empty($return_2[$id]))
            {
                $messge = 'ko co du lieu truong nay ';  
            }
            else
            {
                // xoa o phan chinh
                unset($return_2[$id]);
                $result = DataProvider::set($key,'designTool',$return_2);
                if($result ==false)
                {
                    $messge = 'xoa bi loi ';  
                }
                else
                    $messge = 'xoa thanh cong ';  
                
            }
            
        }
        
        $return =  DataProvider::get($key,'designTool');
        if($return == false)
        {
         $messge = 'du lieu ko co';
        }    
        $ViewData = json_encode($return);
        $ViewData = json_decode($ViewData,true) ; 
        //------------------
        
        $block = 1; 
        
        include TPL_DIR . '/bannermanager.php';  
    }
  // quan ly anh trong landing page
  public function image_landingpage()
  {
        $messge = '';
        // get du lieu ra              
        $key = 'MyFish_image_landingpage' ;  
            
        //------------------
          
        if(isset($_POST['AddNewImage']))
        {
            $ImageName  = $_POST['ImageName'];
            $Link       = $_POST['Link'];
            $Order      =  intval($_POST['Order']);
            if(empty($ImageName)||empty($Order)||empty($Link))
            {
                $messge = 'ban nhap thieu thong tin ';
            }
            else
            {
                $arr_input = array();
                $arr_input['ImageName']   = $ImageName;
                $arr_input['Link']       = $Link; 

                $return_1 =  DataProvider::get($key,'designTool');    
                $return_1[$Order]= $arr_input ; 

                $result = DataProvider::set($key,'designTool',$return_1);
                if($result ==false)
                {
                  $messge = 'luu tru thong tin bi loi ';  
                }
                $messge = 'luu tru thanh cong ';   
            }
        }
        // xoa o phan chinh 
        if(isset($_POST['deleteField']))
        {

            $id = substr($_POST['deleteField'],3) ;

            if(empty($key))
            {
                echo 'ko co key dau' ; 
            } 
            $return_2 =  DataProvider::get($key,'designTool');

            if(empty($return_2[$id]))
            {
                $messge = 'ko co du lieu truong nay ';  
            }
            else
            {
                // xoa o phan chinh
                unset($return_2[$id]);
                $result = DataProvider::set($key,'designTool',$return_2);
                if($result ==false)
                {
                    $messge = 'xoa bi loi ';  
                }
                else
                    $messge = 'xoa thanh cong ';  
                
            }
            
        }
        
        $return =  DataProvider::get($key,'designTool');
        if($return == false)
        {
         $messge = 'du lieu ko co';
        }
        $ViewData_1 = json_encode($return);
        $ViewData_1 = json_decode($ViewData_1,true) ; 
        
        $block = 2; 
        
        include TPL_DIR . '/bannermanager.php';  
  }
  
  // quan ly text and link  trong landing page
    public function text_landingpage()
    {
        $messge = '';
        // get du lieu ra              
        $key = 'MyFish_text_landingpage' ;  
            
        //------------------
          
        if(isset($_POST['AddNewText']))
        {
            $TextName   = $_POST['TextName'];
            $Link       = $_POST['Link'];
            $Day       = $_POST['Day'];
            $Order      =  intval($_POST['Order']);
            if(empty($TextName)||empty($Order)||empty($Link)||empty($Day))
            {
                $messge = 'ban nhap thieu thong tin ';
            }
            else
            {

                $arr_input = array();
                $arr_input['TextName']   = $TextName;
                $arr_input['Link']       = $Link; 
                $arr_input['Day']        = $Day; 
                
                $return_1 =  DataProvider::get($key,'designTool');    
                $return_1[$Order]= $arr_input ; 

                $result = DataProvider::set($key,'designTool',$return_1);
                if($result ==false)
                {
                  $messge = 'luu tru thong tin bi loi ';  
                }
                $messge = 'luu tru thanh cong ';   
            }
        }
        // xoa o phan chinh 
        if(isset($_POST['deleteField']))
        {

            $id = substr($_POST['deleteField'],3) ;

            if(empty($key))
            {
                echo 'ko co key dau' ; 
            } 
            $return_2 =  DataProvider::get($key,'designTool');

            if(empty($return_2[$id]))
            {
                $messge = 'ko co du lieu truong nay ';  
            }
            else
            {
                // xoa o phan chinh
                unset($return_2[$id]);
                $result = DataProvider::set($key,'designTool',$return_2);
                if($result ==false)
                {
                    $messge = 'xoa bi loi ';  
                }
                else
                    $messge = 'xoa thanh cong ';  
                
            }
            
        }
        
        $return =  DataProvider::get($key,'designTool');
        if($return == false)
        {
         $messge = 'du lieu ko co';
        }
        $ViewData_1 = json_encode($return);
        $ViewData_1 = json_decode($ViewData_1,true) ; 
        
        $block = 3; 
        
        include TPL_DIR . '/bannermanager.php';  
  }
  
  
    // xoa cac Item cua user
    public function DeleteItem()
    {
        
        if(!Controller::checkPower(1))
            return false ;

        $uId = $_SESSION['uId'];
        $type = $_POST['ztype'];
        
        if(isset($_POST['uid']))
            $uId = intval($_POST['uid']);
        if(empty($uId))
        {
            $uId = 358245;
        }    
        $configItem = Common::getConfig('ItemAll','Delete');

        $arr = array();
        foreach ($configItem[$type] as $key)
        {
            $arr[$key] = $_POST[$key];
        }
        
        $oUser = User::getById($uId);
        if(!is_object($oUser))
        {
            $mess = "Khong ton tai User nay";
        }
        else
        {
            
            // xoa thong tin 
            if(isset($_POST['Delete']))
            {
                if(!empty($type)&& !empty($arr))
                {
                    $oAdminTool=new AdminTool();
                    $return = $oAdminTool->DeleteItem($uId,$type,$arr);

                    if($return) $mess = 'OK - update Thanh cong ' ;
                    else  $mess = 'Not OK - Khong hop le  ' ;
                }
            }
            StaticCache::forceSaveAll();
            
            $ViewData = $this->viewAllInfoUser($uId);
            
        }
        include TPL_DIR . '/DeleteItem.php';
    }
    
    // ham thuc hien viec them Item cho user
    function AddItem()
    {
        if(!Controller::checkPower(1))
            return false ;
            
        //$uId = $_SESSION['uId'];
        
        if(isset($_POST['uid']))
            $uId = intval($_POST['uid']);
        
        if(empty($uId))
        {
            $uId = 358245;
        }
        $type = $_POST['ztype'];
        
        $configItem = Common::getConfig('ItemAll','Add');

        $arr = array();
        foreach ($configItem[$type] as $key)
        {
            $arr[$key] = $_POST[$key];
        }
        
        $oUser = User::getById($uId);
        if(!is_object($oUser))
        {
            $mess = "Khong ton tai User nay";
        }
        else
        {
            
            if(isset($_POST['Add']))
            {
                if(!empty($type)&& !empty($arr))
                {
                    $oAdminTool=new AdminTool();
                    $return = $oAdminTool->UpdateInventory($uId,$type,$arr);
                    
                    if($return) $mess = 'OK - update Thanh cong ' ;
                    else  $mess = 'Not OK - Khong hop le  ' ;
                    
                    if(!empty($_POST['Comment']))
                    {
                        $oAdminTool->InsertComment($_POST['Comment']);
                        setcookie('Comment',$_POST['Comment'],time()+3600);
                    }
                
                }
                StaticCache::forceSaveAll();       
                
            }
            
            $ViewData = $this->viewAllInfoUser($uId);
            //$configItem = Common::getConfig('ItemAll','Add');
        }
        //common::redirect($this->config['domain']."/web/admin.php?mod=Index&act=AddItem&uid=".$uId."&return=".$return);
        include TPL_DIR.'/AddItem.php' ;
    }
    
    function EditItem()
    {
        if(!Controller::checkPower(1))
            return false ;
       if(isset($_POST['uid']))
            $uId = intval($_POST['uid']);
        
        if(empty($uId))
        {
            $uId = 358245;
        }
        $type = $_POST['ztype'];
        
        $configItem = Common::getConfig('ItemAll','Edit');

        $arr = array();
        foreach ($configItem[$type] as $key)
        {
            $arr[$key] = $_POST[$key];
        }
        
        $oUser = User::getById($uId);
        if(!is_object($oUser))
        {
            $mess = "Khong ton tai User nay";
        }
        else
        {
            $block = 1;
            if(isset($_POST['InfoItem']))
            {
                $block = 2;
            }
            if(isset($_POST['Edit']))
            {
                if(!empty($type)&& !empty($arr))
                {
                    $oAdminTool=new AdminTool();
                    $return = $oAdminTool->EditItem($uId,$type,$arr);
                    
                    if($return) $mess = 'OK - update Thanh cong ' ;
                    else  $mess = 'Not OK - Khong hop le  ' ;
                }
                StaticCache::forceSaveAll();  
            }
            
            
            //$ViewData = $this->viewAllInfoUser($uId);
            //$configItem = Common::getConfig('ItemAll','Add');
        }
        //common::redirect($this->config['domain']."/web/admin.php?mod=Index&act=AddItem&uid=".$uId."&return=".$return);
        include TPL_DIR.'/EditItem.php' ;
    } 
    
    function EditFirstAddXu()
    {
        if(!Controller::checkPower(1))
            return false ;
        $adminId = $this->uId;

        if(isset($_POST['uid']))
            $uId = intval($_POST['uid']);
        
        if(empty($uId))
        {
            $uId = 487853;
        }
        
        $type = $_POST['ztype'];
        
        $configItem = Common::getConfig('ItemAll','Edit');

        $arr = array();
        foreach ($configItem[$type] as $key)
        {
            $arr[$key] = $_POST[$key];
        }
                 
        $oUser = User::getById($uId);
        if(!is_object($oUser))
        {
            $mess = "Khong ton tai User nay";
        }
        else
        {
            $block = 1;
            if(isset($_POST['Edit']))
            {
                if(!empty($type)&& !empty($arr))
                {
                    $oUser->FirstAddXu += intval($arr['Num']) ;
                    $oUser->save();
                    $return = true;
                    if($return) $mess = 'OK - update Thanh cong ' ;
                    else  $mess = 'Not OK - Khong hop le  ' ;
                }
                StaticCache::forceSaveAll();
            }

        }
        include TPL_DIR.'/EditFirstAddXu.php';
    }
    function UpdateStore()
    {
        if(!Controller::checkPower(1))
            return false ;
       if(isset($_POST['uid']))
            $uId = intval($_POST['uid']);
       else
       {
           $mess = "ban hay nhap vao UserId";
           $block = 1 ;
           include TPL_DIR.'/UpdateStore.php' ; 
           die();       
       }
       
        $oUser = User::getById($uId);
        if(!is_object($oUser))
        {
            $mess = "Khong ton tai User nay"; 
            $block = 1 ;
            include TPL_DIR.'/UpdateStore.php' ; 
            return  ;       
        }
        else
        {
            if(isset($_POST['Update']))
            {
                $oAdminTool=new AdminTool();
                $return = $oAdminTool->updateStore($uId);
                
                if($return) 
                    $mess = 'OK - update Thanh cong ' ;
                else  
                    $mess = 'Not OK - Khong hop le  ' ;
                StaticCache::forceSaveAll();  
            }
            
            $block = 2 ;
            $mess = "he he thanh cong roi ";
            include TPL_DIR.'/UpdateStore.php' ; 
            return  ;       
        }
        
        
        
    }
    
    function viewItem()
    {
        $type     = $_POST['type'];
        $pageType = $_POST['pageType'];
        #lay ca file config
        if($pageType == 'Add')
            $configItem = Common::getConfig('ItemAll','Add');
        if($pageType == 'Delete')
            $configItem = Common::getConfig('ItemAll','Delete');
        if($pageType == 'Edit')
            $configItem = Common::getConfig('ItemAll','Edit');
        
        if(!is_array($configItem))
        {
            echo 1 ;
        }
        
        $str = '<td width="20%" height="23">Add Info</td>';
        $str .= '<td width="20%" height="23" id="zid">';
        foreach ($configItem[$type] as $key)
        {
            $str .="$key".":";
            $str .="<input type='text' name='$key' value=''/><br>";
        }
        $str .="</td>";
        echo  $str ;

    }
    
    public function viewAllInfoUser($uId)
    {
        // view thong tin user    
        $oUser = User::getById($uId);    
        $runArr['User'] = $oUser ;
        $runArr['Dataversion'] = $oUser->getDataVersion() ;
        $runArr['TotalZMoney'] = $oUser->getTotalZMoney() ;
        $runArr['Store'] = Store::getById($uId);
        $runArr['QuestInfo'] = Quest::getById($uId);
        $runArr['EventList'] = Event::getById($uId);

        $oLake1 = Lake::getById($uId, 1);
        $runArr['Lake1'] = $oLake1 ;
        $runArr['UserProfile'] = UserProfile::getById($uId);            
        $oDeco1 = Decoration::getById($uId,1);
        $runArr['ItemListLake1'] =  $oDeco1;


        $oLake2 = Lake::getById($uId, 2);
        $runArr['Lake2'] = $oLake2 ;            
        $oDeco2 = Decoration::getById($uId, 2);
        $runArr['ItemListLake2'] =  $oDeco2;
        
        $runArr['ItemCode'] = ItemCode::getById($uId);        
        $oAcc = AccumulationPoint::getById($uId);        
        $runArr['AccumulationPoint'] = $oAcc->getPoint() ;

        $ViewData = json_encode($runArr);
        $ViewData = json_decode($ViewData,true) ;
        //$userInfo = ZingApi :: getUserInfo(array($uId)) ;
        //$ViewData['User']['username'] = $userInfo[0]['username'] ;
        return $ViewData ;
        
    }
    
    
    function gmToolLog()
    {

        $myfile = 'gmtool.txt';

        $lines = file($myfile);

        for($i=count($lines);$i>0;$i--)
        {
            echo $lines[$i];
            echo '<br/>';
        }
    }
    function gmtools()
    {
       if(!Controller::checkPower(1))
        return  ;
        if($_POST['submit'])
        {
            $uids = trim($_POST['uids']);
            $arrUids = explode(',',$uids);
            ('User');
            $userModel = new UserModel();
            $userDatas = array();
            $messuid = array();

            foreach($arrUids as $key=>$uid)
            {
                $userData =  $userModel->getUserFromCache($uid);

                if($userData === false || empty($userData))
                {
                    $messuid[] =$uid;
                 }
                else
                {
                    ##### profile
                    $profile = $userModel->getData("{$uid}_userProfile" );
                    $userData['username'] = $profile['uName'];
                    $userData['displayname'] = $profile['displayname'];
                    $userDatas[$uid]=$userData;
                }

            }
            $block =2;
        }
        else
        {
            $block =1;
        }
        include TPL_DIR . '/listuser.php';
    }
    function getconfig()
    {

    }
    function item()
    {

    }
    
    public function result_text()
    {
       // get du lieu ra  
      $key = "Fish" ;            
      $keyword = 'designTool' ;  
      $arr=  DataProvider::get($key,$keyword);
      if($arr == false)
      {
         return null;
      }
      else
      {    
        $rate = array ();
        foreach($arr as $key => $value)
        {
          $rate[$key] = $value['Per']; 
        }
        $index = Common::randomIndex($rate);
        return $arr[$index] ;
      }
    }
    
    public function result_BannerName()
    {
        // get du lieu ra  
        $key = 'MyFish_BannerManager' ;  
        $return =  DataProvider::get($key,'designTool');

        if($return == false)
        {
            return null;
        }
        else
        {    
            $rate = array ();
            foreach($return as $key => $value)
            {
                $rate[$key] = $value['Percent']; 
            }
            $index = Common::randomIndex($rate);
            return $return[$index]['BannerName'];
        }
        
        return null ;
    }
    
    public function updateEquipment()
    {
        if(!Controller::checkPower(1))
            return false;
        // thuc hien send thong tin
        $UserList = $_POST['UserList'] ;
        $notify = array() ;
        if(isset($_POST['Update'])&& !empty($UserList))
        {
            $UserList = explode(',',$UserList);
            foreach($UserList as $index =>$uId)
            {
                $oUser = User::getById($uId);
                if(!is_object($oUser))
                {
                    $notify[$uId] = $uId ;
                    continue ;
                }
                // trong kho 
                $oStore = Store::getById($uId);
                foreach($oStore->Equipment as $Type => $arr_E)
                {
                    if(empty($arr_E)||$Type != SoldierEquipment::Weapon ) continue ;
                    foreach ($arr_E as $id => $object_E )
                    {
                        if(!is_object($object_E)) continue ;
                        if($object_E->Color == 5)
                        {
                            $oStore->Equipment[$Type][$id]= $this->repairEquipment($object_E);
                        }
                        
                    }
                }
                $oStore->save();
                
                // o tren ca
                $arrSoldier = Lake::getAllSoldier($uId,true,true,true);

                foreach($arrSoldier as $Lakeid => $arr_fish)
                {
                    $oLake = Lake::getById($uId,$Lakeid);
                    if(!is_object($oLake))  continue ;
                    foreach($arr_fish as $FishId => $oSoldier)
                    {
                        foreach($oSoldier->Equipment as $Type1 => $arr_E)
                        {
                            if(empty($arr_E)||$Type1 != SoldierEquipment::Weapon ) continue ;
                            foreach ($arr_E as $id1 => $object_E )
                            {
                                if(!is_object($object_E)) continue ;
                                if($object_E->Color == 5)
                                {
                                    $oSoldier->Equipment[$Type1][$id1]= $this->repairEquipment($object_E);
                                }
                                
                            }
                        }
                        
                    }
                    $oLake->save();
                    
                }
            }
        }
        StaticCache::forceSaveAll();
        include TPL_DIR.'/updateEquipment.php' ;
    }
    
    private function repairEquipment($oE)
    {
        
        $newEquipment = Common::randomEquipment($oE->Id,$oE->Rank,$oE->Color,$oE->Source,$oE->Type,$oE->EnchantLevel,$oE->Element,5);
        
        return $newEquipment ;       
    }
    
    // khoi tao ItemCode
    public function createItemCode()
    {
        if(!Controller::checkPower(1))
            return false;
        $block = 1;
        $notify = false ;
        $ItemCodeType = $_POST['ItemCodeType'] ;
        if(isset($ItemCodeType) && $ItemCodeType > 0)
        {
            if($ItemCodeType == 1) // loai config
            {
                $block = 2;
            }
            else if($ItemCodeType == 2) // loai tu dong
            {
                $block = 3; 
            }
            else if($ItemCodeType == 3) // loai tu dong
            {
                $block = 4; 
            }
            
        }
        // thuc hien check key
        if(isset($_POST['checkkey'])&& $ItemCodeType == 1)
        {   
            Debug::log('check ko co gi00');
            
            $key = '../imgcache/CodeList.php' ;
            if(file_exists($key))
            {   
                $Conf = require_once($key) ;
                $Conf2 = array_keys($Conf);
                $conf3 = array_count_values($Conf2);
                foreach($conf3 as $key1 => $value)
                {
                    if($value >1)
                        echo 'co chung key :'.$key;
                }
            }
            else 
            {
               $notify = false ;
               exit;
            }
               $notify = true;
                $block = 2;
            echo 'ko chung key';
        }
        // thuc hien create ItemCode dung config
        if(isset($_POST['Create'])&& $ItemCodeType == 1)
        {
            
            $TextInput      = $_POST['TextInput'];
            $Num            = intval($_POST['Num']);
            $ConfId         = intval($_POST['ConfigId']);
            $is_Element     = $_POST['Element'];
            if(empty($TextInput)||empty($Num)||empty($ConfId)) 
            {
                $notify = false ;
                return false ;
            }
                
            $file = fopen('../imgcache/CodeList.php','w');
            $wfile = fopen('../imgcache/Itemcode.csv','w');
            if($wfile == false || $file == false)
            {
                Debug::log('ko mo duoc file');
                return false ;
            }
            $createStr = "<?php return array( \n";
            fwrite($file,$createStr,strlen($createStr)) ;
            
            $data = '';
            $data_code = '';
            $j = 1 ;
            for($i = 1 ; $i <= $Num ; $i++)
            {
                 $code =  sha1($i.$TextInput.$ConfId,false);
                 
                 $code1 =  sha1($code,false);
                 $code1 = substr($code1,0,8);
                 if($is_Element)// co chon he
                 {
                     $code1 = 'E'.$code1 ;
                 }
                 else
                 {
                     $code1 = 'N'.$code1 ;
                 }
                    
                 $data .= $code1."\n";
                 $data_code .= '"'.$code1.'"'."=>$ConfId,\n";
                 
                 if($j > 10000)
                 {
                     fwrite($wfile,$data,strlen($data)) ;
                     fwrite($file,$data_code,strlen($data_code)) ;
                     $j= 1;
                     $data = '';
                     $data_code = '';
                 }
                 $j++;
            }
            if(fwrite($wfile,$data,strlen($data))== False)
            {
                Debug::log('ghi false');
                return false;
            } 
            if(fwrite($file,$data_code,strlen($data_code))== False)
            {
                Debug::log('ghi file php false');
                $notify = false ;
                return false;
            } 
            
            $endStr = ")?>";
            fwrite($file,$endStr,strlen($endStr)) ;
            
            Debug::log('done');
            fclose($file); 
            fclose($wfile); 
                       
            $notify = true ;
            
            $oAdminTool =  new AdminTool();      
            $oAdminTool->InsertAdminLog('CreateItemCode',Controller::$uId,$ItemCodeType,$ConfId,$Num);
            
            if($Num <10)
            {
                var_dump($data_code) ;
            }
     
        }
       
        
        include TPL_DIR.'/ItemCode.php' ;
    }
    
    // ma hoa Item code dang binh thuong
    private function Encode_ItemCode($input)
    {   
        $output = base64_encode($input);
        $len = ceil(strlen($output)/3);
        
        $output1 = substr($output,0,$len);
        
        $output2 = substr($output,$len);
        
        $output1 = strrev($output1);
        
        $output = $output2.$output1 ;
        return $output ;
  
    }
    
    // khoi tao ItemCode
    public function decodeItemCode()
    {
        if(!Controller::checkPower(1))
            return false;
        $block = 1;
        $notify = false ;
        $ItemCodeType = $_POST['ItemCodeType'] ;
        $Code = $_POST['Code'] ;
        // thuc hien create ItemCode dung config
        if(isset($_POST['Decode'])&& $ItemCodeType == 1 && !empty($Code) )
        {
            $arr= $this->Decode_ItemCode($Code,$ItemCodeType);

            $notify = true ;
                
        }
        // thuc hien create ItemCode tu dong 
        if(isset($_POST['Decode'])&& $ItemCodeType == 2 && !empty($Code) )
        {
            $arr= $this->Decode_ItemCode($Code,$ItemCodeType);
            
            $notify = true ;
               
        }
        // thuc hien create ItemCode tu dong 
        if(isset($_POST['Decode'])&& $ItemCodeType == 3 && !empty($Code) )
        {
            $arr= $this->Decode_ItemCode($Code,2);
            
            $notify = true ;
               
        }
        include TPL_DIR.'/DecodeItemCode.php' ;
    }
    
    private function Decode_ItemCode($input,$Type)
    {
        $conf = Common::getConfig('Secret',$Type);
        
        $arr = array();
        if($Type == 1) // config
        {
            $oAdminTool = new AdminTool();
           //Debug::log('$input_11'.$input);
            $output = substr($input,2);
            //Debug::log('$input_12'.$output);
            $arr = $oAdminTool->DecodeConfigItemCode($output);  
        }
        else // auto
        {
        
            $len = ceil(strlen($output)/3);
            $output1 = substr($output,-$len);
            
            $output2 = substr($output,0,-$len);
            
            $output1 = strrev($output1);
            
            $output = $output1.$output2 ;
            
            $output = base64_decode($output);
            
            $input              = json_decode($output,true);
            $arr['UserType']  = $input['UserType'] ;
            $arr['Id']        = $input['Id']         ;
            $arr['FromTime']  = $input['FromTime']   ;
            $arr['Secret']    = $input['Secret']     ;
            $arr['Content']   = $input['Content'] ;
            $arr['ToTime']    = $input['ToTime']     ;
            
        }
        return $arr ;
  
    }
    
    public function euro_adminEvent()
    {
        if(!Controller::checkPower(3))
            return false;
            
        $Fixture = DataProvider::get('share','EuroFixture');
        $Teams = Common::getConfig('EventEuro_Teams');
        $MatchType = array('BOARD'=> 'Vong bang', 'QUAD' => 'Tu ket', 'SEMI' => 'Ban ket','FINAL' => 'Chung ket',);
        
        if(empty($Fixture))
        {
            $Fixture = Common::getConfig('EventEuro_Fixture');
            foreach($Fixture as $id => $match)
            {
                $match['Result'] = EventEuro::EURO_NOT_HAVING_RESULT;
                $match['Penalty'] = false;
                $match['Goal'] = array();
                $match['MatchTimeBegin'] = strtotime($match['MatchTimeBegin']);
                $match['BetTimeBegin'] = strtotime($match['BetTimeBegin']);
                $match['BetTimeEnd'] = $match['MatchTimeBegin'];
                $match['BetStat'] = ($match['MatchType'] == 'BOARD') ? array(1 => 0, 2 => 0, 3 => 0) : array(1 => 0, 3 => 0);
                
                $Fixture[$id] = $match;
            }
        }
        
        DataProvider::set('share','EuroFixture', $Fixture);
        include TPL_DIR.'/Euro_AdminEvent.php' ; 
    }
    
    public function euro_modifyFixture()
    {   
        if(!Controller::checkPower(3))
            return false;
            
        $Fixture = DataProvider::get('share','EuroFixture');
        $Teams = Common::getConfig('EventEuro_Teams');
        $MatchType = array('BOARD'=> 'Vong bang', 'QUAD' => 'Tu ket', 'SEMI' => 'Ban ket','FINAL' => 'Chung ket',);
        
        include TPL_DIR.'/Euro_ModifyFixture.php' ; 
    }
    
    public function euro_addMatch()
    {
        if(!Controller::checkPower(3))
            return false;
            
        // init adding
        $Teams = Common::getConfig('EventEuro_Teams'); 
        $TeamCode = array_keys($Teams);
        
        $MatchType = array('BOARD'=> 'Vong bang', 'QUAD' => 'Tu ket', 'SEMI' => 'Ban ket','FINAL' => 'Chung ket',);
        $Star = array(1,2,3,4,5);
        
        $BeginTime = array(
            mktime()
        );
        for($i = 1; $i <= 31; $i++)
            $Days[] = $i;
        $Months = array(6,7);
        $block = 1;
        
        // check adding
        
        // preview
        if(isset($_POST['preview']))
        {            
            $block = 2;
        }else if(isset($_POST['submit']))
        {
            $Fixture = DataProvider::get('share','EuroFixture'); 
            
            
            // new match
            $newmatch = array(
                'Team1' => $Team1,
                'Team2' => $Team2,
                'Goal' => array(),
                'Result' => 0,
                'MatchTimeBegin' => $betTimeEnd,
                'BetTimeBegin' => $betTimeBegin,
                'BetTimeEnd' =>  $betTimeEnd,
                'MatchType' => $MatchType,
                'Star' => $Star,
                'UpdateResultTime' => $endMatch, 
            );
        
            $Fixture[] = $newmatch;
            DataProvider::set('share','EuroFixture', $Fixture);
                    
            $block = 3;
        }
        
        
        
        // submit
        
        include TPL_DIR.'/Euro_AddMatch.php';
    }
    
    public function euro_modifyMatch()
    {
        if(!Controller::checkPower(3))
            return false;
            
        $Fixture = DataProvider::get('share','EuroFixture'); 
        $Teams = Common::getConfig('EventEuro_Teams');
        $Star = array(1,2,3,4,5);
        $MatchType = array('BOARD'=> 'Vong bang', 'QUAD' => 'Tu ket', 'SEMI' => 'Ban ket','FINAL' => 'Chung ket',);
        
        
        $ModifyAvailable = array();
        $currtime = time();
        foreach($Fixture as $idMatch=>$match)
        {            
            if($currtime < $match['MatchTimeBegin'])
                $ModifyAvailable[$idMatch] = $match;
        }                            

        $block = 1;
         
        if(isset($_POST['choseModify'])) 
        {          
            $MatchId = $_POST['chooseMatch'];
            if($ModifyAvailable[$MatchId]['MatchType'] == 'BOARD')
                $typeModify = 'fixTeam';
            else $typeModify = 'flexTeam';

            $block = 2;
        }
        
        if(isset($_POST['cancelModify']))
        {
            $block = 4;
        }
        
        if(isset($_POST['submitModify']))
        {
           $MatchId = $_POST['MatchId'];
           $typeModify = $_POST['typeModify'];
           
           $Team1 = $_POST['Team1'];
           $Team2 = $_POST['Team2'];
           $Star = $_POST['Star'];
           $MatchTimeBegin = $_POST['MatchTimeBegin'];
           $BetTimeBegin = $_POST['BetTimeBegin'];
           
           $message = ' Thay doi Success';
           if($typeModify == 'flexTeam')
               if(!empty($Team1) && !empty($Team2) && ($Team1 != $Team2))
               {
                   $Fixture[$MatchId]['Team1'] = $Team1;
                   $Fixture[$MatchId]['Team2'] = $Team2;
               }else $message = 'Error Input';
           
           if(!empty($Star))
                $Fixture[$MatchId]['Star'] = $Star;
           
           $MatchTimeBegin = strtotime($MatchTimeBegin);
           $BetTimeBegin = strtotime($BetTimeBegin);
           if(!empty($MatchTimeBegin))
           {   
                if(!empty($BetTimeBegin))         
                {
                     if($BetTimeBegin < $MatchTimeBegin)
                       {
                            $Fixture[$MatchId]['MatchTimeBegin'] = $MatchTimeBegin;
                            $Fixture[$MatchId]['BetTimeEnd'] = $MatchTimeBegin;
                            $Fixture[$MatchId]['BetTimeBegin'] = $BetTimeBegin;             
                       }
                       else $message = 'Error Input';
                }
                elseif($MatchTimeBegin > $Fixture[$MatchId]['MatchTimeBegin'])
                {
                     $Fixture[$MatchId]['MatchTimeBegin'] = $MatchTimeBegin;
                     $Fixture[$MatchId]['BetTimeEnd'] = $MatchTimeBegin;
                }else $message = 'Error Input';    
               
               
           } elseif(!empty($BetTimeBegin))
                    if ($BetTimeBegin < $Fixture[$MatchId]['MatchTimeBegin'])
                    {
                        $Fixture[$MatchId]['BetTimeBegin'] = $BetTimeBegin; 
                    } else $message = 'Error Input';
           
           DataProvider::set('share','EuroFixture', $Fixture);
           $block = 3; 
        }
           
        include TPL_DIR.'/Euro_ModifyMatch.php';
    }
    
    public function euro_updateResult()
    {
        if(!Controller::checkPower(3))
            return false;
            
        $Fixture = DataProvider::get('share','EuroFixture'); 
        $Teams = Common::getConfig('EventEuro_Teams');
        $MatchType = array('BOARD'=> 'Vong bang', 'QUAD' => 'Tu ket', 'SEMI' => 'Ban ket','FINAL' => 'Chung ket',);
        $SpecialResult = array(0 => "",1 => 'Penalty', 2 => 'Team1 xu thang', 3 => 'Team2 xu thang' );
        
        $ModifyAvailable = array();
        $currtime = time();
        foreach($Fixture as $idMatch=>$match)
        {  
            $matchend = $match['MatchTimeBegin'] + EventEuro::TIME_MATCH;          
            if(($currtime > $matchend) && ($match['Result'] == EventEuro::EURO_NOT_HAVING_RESULT))
                $ModifyAvailable[$idMatch] = $match;
        }                     
        $block = 1;
        
        if(isset($_POST['choseModify'])) 
        {          
            $MatchId = $_POST['chooseMatch'];
            
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];            
         
            $block = 2;
        }
        
        if(isset($_POST['preview']))
        {
            $MatchId = $_POST['MatchId'];
            
            $Team1Goal = $_POST['Team1Goal'];
            $Team2Goal = $_POST['Team2Goal'];
            $SpecialResultIndex = $_POST['specialResult'];

            $error = false;
            if(!is_numeric($Team1Goal) || !is_numeric($Team2Goal))
                $error = true;                                   
            if($Fixture[$MatchId]['MatchType'] != 'BOARD') 
            {
                if(($Team1Goal == $Team2Goal) && $SpecialResultIndex < 2 )
                    $error = true;    
            }
            
            if( ($Fixture[$MatchId]['MatchType'] == 'BOARD') && ($SpecialResultIndex > 0) )
                $error = true;
                
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
                                 
            if($error)
            {
                $block = 4;
                $message = 'Error Input Goal';
            }
            else $block = 3;
            
        }
        
        if(isset($_POST['back']))
        {
            $MatchId = $_POST['MatchId'];
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
            
            $block = 2; 
        }
        
        if(isset($_POST['cancelResult']))
        {
            $MatchId = $_POST['MatchId'];
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
            
            $block = 2; 
        }
        
        if(isset($_POST['submitResult']))
        {
            $MatchId = $_POST['MatchId'];
            
            $Team1Goal = $_POST['Team1Goal'];
            $Team2Goal = $_POST['Team2Goal'];
            $SpecialResultIndex = $_POST['specialResult'];
            
            $error = false;
            if(!is_numeric($Team1Goal) || !is_numeric($Team2Goal))
                $error = true;
            if( ($Fixture[$MatchId]['MatchType'] != 'BOARD') && ($Team1Goal == $Team2Goal))
                $error = true;
                            
            if(!$error)
            {
                if($Team1Goal > $Team2Goal) $Result = 1;
                else if($Team1Goal == $Team2Goal) $Result = 2;
                else $Result = 3;
                
                $message = 'Update Ket qua Success';
                $Goal = array($Team1Goal, $Team2Goal);
                switch($SpecialResultIndex)
                {
                    case 0:
                        $penalty = false;
                        break;
                    case 1:
                        $penalty = true;
                        break;
                    case 2:
                        $Result = 1;
                        break;
                    case 3: 
                        $Result = 3;
                        break;
                }
                $Fixture[$MatchId]['Result'] = $Result;
                $Fixture[$MatchId]['Goal'] = $Goal;
                $Fixture[$MatchId]['Penalty'] = $penalty;
                
                // update Euro Info
                $EuroInfo = DataProvider::get('share','EuroInfo');
                $EuroInfo['LastMatchUpdateResult'] = $MatchId;
                
                // lauch Sql update Result
                $indexNameQuery = 'EventEuro_Dev';
                $sql = 'call updateEuroTop('.$MatchId.','.$Result.')';
                $res = Common::queryMySql($indexNameQuery, $sql, 'MyFish');
                if(!$res)
                    die('Cant update Top. Dont act any more. Contact Admin please');
                // update Top
                $sql = 'select uId, medal, right_bet_matches, bet_matches, last_bet_match, last_bet, level from euro_top order by medal DESC, right_bet_matches DESC, bet_matches DESC, last_bet_match DESC, last_bet ASC, level DESC limit 100';
                $res_top = Common::queryMySql($indexNameQuery, $sql, 'MyFish');
                if(!$res_top)
                    die('Cant set Top. Dont act any more. Contact Admin please');
                
                $i = 0;
                $TopOrder = array();
                $TopProfile = array();
                while($row = mysql_fetch_array($res_top, MYSQL_ASSOC))
                {
                    $i ++;
                    $Uid = $row['uId'];
                    $TopOrder[$i] = $Uid;
                      $TopProfile[$Uid] = array(
                        'Order'     => $i,
                        'Medal'     => $row['medal'],
                        'RightNum'  => $row['right_bet_matches'],
                        'BetNum'    => $row['bet_matches'],
                        'LastBetMatch' => $row['last_bet_match'],
                        'LastBet'   => $row['last_bet'],
                        'Level'     => $row['level']       
                      );
                }
                
                DataProvider::set('share','EuroTop', $TopOrder,'Order');
                DataProvider::set('share','EuroTop', $TopProfile,'Profile');
                DataProvider::set('share','EuroFixture', $Fixture); 
                DataProvider::set('share','EuroInfo', $EuroInfo);
                
            }else
                $message = 'Error Input Goal';
                
            $block = 4;
        }
            
        include TPL_DIR.'/Euro_UpdateResult.php';
    }
    
    public function euro_showTop()
    {
        if(!Controller::checkPower(3))
            return false;
        
        $TopOrder = DataProvider::get('share','EuroTop','Order');    
        $TopProfile = DataProvider::get('share','EuroTop','Profile');
        
        $top10 = array();
        $top = (count($TopOrder) > 100) ? 100 : count($TopOrder);
       for ($i = 1; $i <= $top; $i++)
       {
           $uId = $TopOrder[$i];
           $oUser = User::getById($uId);
           
           $info = array(
                'Id' => $uId,
                'Name' => $oUser->Name,
                'AvatarPic' => $oUser->AvatarPic,   
           );
           $top10[$i] = array_merge($info, $TopProfile[$uId]);
       }
        
        include TPL_DIR.'/Euro_ShowTop.php';        
    }
    
    public function euro_testtop()
    {
              
    }
    
    /**
    * cant run upadate metric , update in game
    * 
    */
    public function euro_updateingame()
    {
        if(!Controller::checkPower(3))
            return false;
        if(Controller::$uId != 18322500 && Controller::$uId != 11357326)
            return false;
            
         $Fixture = DataProvider::get('share','EuroFixture'); 
        $Teams = Common::getConfig('EventEuro_Teams');
        $MatchType = array('BOARD'=> 'Vong bang', 'QUAD' => 'Tu ket', 'SEMI' => 'Ban ket','FINAL' => 'Chung ket',);
        $SpecialResult = array(0 => "",1 => 'Penalty', 2 => 'Team1 xu thang', 3 => 'Team2 xu thang' );
        
        $ModifyAvailable = array();
        $currtime = time();
        foreach($Fixture as $idMatch=>$match)
        {  
            $matchend = $match['MatchTimeBegin'] + EventEuro::TIME_MATCH;          
            if(($currtime > $matchend))
                $ModifyAvailable[$idMatch] = $match;
        }                     
        $block = 1;
        
        if(isset($_POST['choseModify'])) 
        {          
            $MatchId = $_POST['chooseMatch'];
            
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];            
         
            $block = 2;
        }
        
        if(isset($_POST['preview']))
        {
            $MatchId = $_POST['MatchId'];
            
            $Team1Goal = $_POST['Team1Goal'];
            $Team2Goal = $_POST['Team2Goal'];
            $SpecialResultIndex = $_POST['specialResult'];

            $error = false;
            if(!is_numeric($Team1Goal) || !is_numeric($Team2Goal))
                $error = true;                                   
            if($Fixture[$MatchId]['MatchType'] != 'BOARD') 
            {
                if(($Team1Goal == $Team2Goal) && $SpecialResultIndex < 2 )
                    $error = true;    
            }
            
            if( ($Fixture[$MatchId]['MatchType'] == 'BOARD') && ($SpecialResultIndex > 0) )
                $error = true;
                
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
                                 
            if($error)
            {
                $block = 4;
                $message = 'Error Input Goal';
            }
            else $block = 3;
            
        }
        
        if(isset($_POST['back']))
        {
            $MatchId = $_POST['MatchId'];
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
            
            $block = 2; 
        }
        
        if(isset($_POST['cancelResult']))
        {
            $MatchId = $_POST['MatchId'];
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
            
            $block = 2; 
        }
        
        if(isset($_POST['submitResult']))
        {
            $MatchId = $_POST['MatchId'];
            
            $Team1Goal = $_POST['Team1Goal'];
            $Team2Goal = $_POST['Team2Goal'];
            $SpecialResultIndex = $_POST['specialResult'];
            
            $error = false;
            if(!is_numeric($Team1Goal) || !is_numeric($Team2Goal))
                $error = true;
            if( ($Fixture[$MatchId]['MatchType'] != 'BOARD') && ($Team1Goal == $Team2Goal))
                $error = true;
                            
            if(!$error)
            {
                if($Team1Goal > $Team2Goal) $Result = 1;
                else if($Team1Goal == $Team2Goal) $Result = 2;
                else $Result = 3;
                
                $message = 'Update Ket qua  Only In Game Success. Not included update top metric. Please contact Admin for update Top metric';
                $Goal = array($Team1Goal, $Team2Goal);
                switch($SpecialResultIndex)
                {
                    case 0:
                        $penalty = false;
                        break;
                    case 1:
                        $penalty = true;
                        break;
                    case 2:
                        $Result = 1;
                        break;
                    case 3: 
                        $Result = 3;
                        break;
                }
                $Fixture[$MatchId]['Result'] = $Result;
                $Fixture[$MatchId]['Goal'] = $Goal;
                $Fixture[$MatchId]['Penalty'] = $penalty;
                
                // update Euro Info
                $EuroInfo = DataProvider::get('share','EuroInfo');
                $EuroInfo['LastMatchUpdateResult'] = $MatchId;
                                
                DataProvider::set('share','EuroFixture', $Fixture); 
                DataProvider::set('share','EuroInfo', $EuroInfo);
                
            }else
                $message = 'Error Input Goal';
                
            $block = 4;
        }
            
        include TPL_DIR.'/Euro_UpdateResultOnlyInGame.php';
    }
    
    /**
    * mistake update, roll back and update again
    * 
    */
    public function euro_updateResultAgain()
    {
        if(!Controller::checkPower(3))
            return false;
            
        $Fixture = DataProvider::get('share','EuroFixture'); 
        $Teams = Common::getConfig('EventEuro_Teams');
        $MatchType = array('BOARD'=> 'Vong bang', 'QUAD' => 'Tu ket', 'SEMI' => 'Ban ket','FINAL' => 'Chung ket',);
        $SpecialResult = array(0 => "",1 => 'Penalty', 2 => 'Team1 xu thang', 3 => 'Team2 xu thang' );
        
        $ModifyAvailable = array();
        $currtime = time();
        foreach($Fixture as $idMatch=>$match)
        {  
            $matchend = $match['MatchTimeBegin'] + EventEuro::TIME_MATCH;          
            if(($currtime > $matchend) && ($match['Result'] > EventEuro::EURO_NOT_HAVING_RESULT))
                $ModifyAvailable[$idMatch] = $match;
        }                     
        $block = 1;
        
        if(isset($_POST['choseModify'])) 
        {          
            $MatchId = $_POST['chooseMatch'];
            
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];            
         
            $block = 2;
        }
        
        if(isset($_POST['preview']))
        {
            $MatchId = $_POST['MatchId'];
            
            $Team1Goal = $_POST['Team1Goal'];
            $Team2Goal = $_POST['Team2Goal'];
            $SpecialResultIndex = $_POST['specialResult'];

            $error = false;
            if(!is_numeric($Team1Goal) || !is_numeric($Team2Goal))
                $error = true;                                   
            if($Fixture[$MatchId]['MatchType'] != 'BOARD') 
            {
                if(($Team1Goal == $Team2Goal) && $SpecialResultIndex < 2 )
                    $error = true;    
            }
            
            if( ($Fixture[$MatchId]['MatchType'] == 'BOARD') && ($SpecialResultIndex > 0) )
                $error = true;
                
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
                                 
            if($error)
            {
                $block = 4;
                $message = 'Error Input Goal';
            }
            else $block = 3;
            
        }
        
        if(isset($_POST['back']))
        {
            $MatchId = $_POST['MatchId'];
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
            
            $block = 2; 
        }
        
        if(isset($_POST['cancelResult']))
        {
            $MatchId = $_POST['MatchId'];
            $Team1 = $Teams[$Fixture[$MatchId]['Team1']];
            $Team2 = $Teams[$Fixture[$MatchId]['Team2']];
            
            $block = 2; 
        }
        
        if(isset($_POST['submitResult']))
        {
            $MatchId = $_POST['MatchId'];
            
            $Team1Goal = $_POST['Team1Goal'];
            $Team2Goal = $_POST['Team2Goal'];
            $SpecialResultIndex = $_POST['specialResult'];
            
            $error = false;
            if(!is_numeric($Team1Goal) || !is_numeric($Team2Goal))
                $error = true;
            if( ($Fixture[$MatchId]['MatchType'] != 'BOARD') && ($Team1Goal == $Team2Goal))
                $error = true;
                            
            if(!$error)
            {
                if($Team1Goal > $Team2Goal) $Result = 1;
                else if($Team1Goal == $Team2Goal) $Result = 2;
                else $Result = 3;
                
                $message = 'Update Lai Ket qua Success';
                $Goal = array($Team1Goal, $Team2Goal);
                switch($SpecialResultIndex)
                {
                    case 0:
                        $penalty = false;
                        break;
                    case 1:
                        $penalty = true;
                        break;
                    case 2:
                        $Result = 1;
                        break;
                    case 3: 
                        $Result = 3;
                        break;
                }
                $Fixture[$MatchId]['Result'] = $Result;
                $Fixture[$MatchId]['Goal'] = $Goal;
                $Fixture[$MatchId]['Penalty'] = $penalty;
                
                // update Euro Info
                $EuroInfo = DataProvider::get('share','EuroInfo');
                $EuroInfo['LastMatchUpdateResult'] = $MatchId;
                
                $indexNameQuery = 'EventEuro_Dev';
                
                // rollback 
                $sql = 'call rollbackEuroTop('.$MatchId.')';
                $res = Common::queryMySql($indexNameQuery, $sql, 'MyFish');
                if(!$res)
                    die('Cant rollback');
                
                    
                // lauch Sql update Result
                
                $sql = 'call updateEuroTop('.$MatchId.','.$Result.')';
                $res = Common::queryMySql($indexNameQuery, $sql, 'MyFish');
                if(!$res)
                    die('Cant update Top');
                // update Top
                $sql = 'select uId, medal, right_bet_matches, bet_matches, last_bet_match, last_bet, level from euro_top order by medal DESC, right_bet_matches DESC, bet_matches DESC, last_bet_match DESC, last_bet ASC, level DESC limit 100';
                $res_top = Common::queryMySql($indexNameQuery, $sql, 'MyFish');
                if(!$res_top)
                    die('Cant set Top. Dont act any more. Contact Admin please');
                
                $i = 0;
                $TopOrder = array();
                $TopProfile = array();
                while($row = mysql_fetch_array($res_top, MYSQL_ASSOC))
                {
                    $i ++;
                    $Uid = $row['uId'];
                    $TopOrder[$i] = $Uid;
                      $TopProfile[$Uid] = array(
                        'Order'     => $i,
                        'Medal'     => $row['medal'],
                        'RightNum'  => $row['right_bet_matches'],
                        'BetNum'    => $row['bet_matches'],
                        'LastBetMatch' => $row['last_bet_match'],
                        'LastBet'   => $row['last_bet'],
                        'Level'     => $row['level']       
                      );
                }
                
                DataProvider::set('share','EuroTop', $TopOrder,'Order');
                DataProvider::set('share','EuroTop', $TopProfile,'Profile');
                DataProvider::set('share','EuroFixture', $Fixture); 
                DataProvider::set('share','EuroInfo', $EuroInfo);
                
            }else
                $message = 'Error Input Goal';
                
            $block = 4;
        }
            
        include TPL_DIR.'/Euro_UpdateResultAgain.php';
    }
    
    public function euro_refreshTop()
    {
        if(!Controller::checkPower(3))
            return false;
        if(Controller::$uId != 18322500 && Controller::$uId != 11357326)
            return false;
        $indexNameQuery = 'EventEuro_Dev'; 
            
        $sql = 'select uId, medal, right_bet_matches, bet_matches, last_bet_match, last_bet, level from euro_top order by medal DESC, right_bet_matches DESC, bet_matches DESC, last_bet_match DESC, last_bet ASC, level DESC limit 100';
        $res_top = Common::queryMySql($indexNameQuery, $sql, 'MyFish');
        if(!$res_top)
            die('Cant set Top. Dont act any more. Contact Admin please');
        
        $i = 0;
        $TopOrder = array();
        $TopProfile = array();
        while($row = mysql_fetch_array($res_top, MYSQL_ASSOC))
        {
            $i ++;
            $Uid = $row['uId'];
            $TopOrder[$i] = $Uid;
              $TopProfile[$Uid] = array(
                'Order'     => $i,
                'Medal'     => $row['medal'],
                'RightNum'  => $row['right_bet_matches'],
                'BetNum'    => $row['bet_matches'],
                'LastBetMatch' => $row['last_bet_match'],
                'LastBet'   => $row['last_bet'],
                'Level'     => $row['level']       
              );
        }
        
        DataProvider::set('share','EuroTop', $TopOrder,'Order');
        DataProvider::set('share','EuroTop', $TopProfile,'Profile');
        
       include TPL_DIR.'/Euro_RefreshTop.php';  
    }
    
    public function euro_showTopBet()
    {
       if(!Controller::checkPower(3))
            return false;
       $TopBet = array();
       if(isset($_POST['choseModify'])) 
        {          
            $MatchId = $_POST['chooseMatch'];
            $indexNameQuery = 'EventEuro_Dev'; 
            $sql = 'select * from act_bet_euro_match where match_id =' . $MatchId . ' order by medal_bet DESC, ABS(ball_left_vip) DESC limit 20';
            $res_top = Common::queryMySql($indexNameQuery, $sql, 'MyFish');
            if(!$res_top)
                die('Cant get Top Bet. Dont act any more. Contact Admin please !');
                
            
            while ($row = mysql_fetch_array($res_top, MYSQL_ASSOC))
            {
                 $TopBet[] = $row;       
            }
        }
        
        include TPL_DIR.'/Euro_ShowTopBet.php';        
    }
    
    public function euro_resetData()
    {   
        if(!Controller::checkPower(1))
            return false;
                      
        $block = 1;
        if(isset($_POST['uIdSubmitted']) && isset($_POST['uId']))
        {
            $uId = $_POST['uId'];
            $block = 2;
        }
        if(isset($_POST['submitFinal']) && isset($_POST['uIdFinal']))
        {
            $uId = $_POST['uIdFinal'];
            $oEvent = Event::getById($uId);
                  if(is_object($oEvent)) 
                  {
                    unset($oEvent->EventList['EventEuro']); 
                    $oEvent->forceSave();
                  }
            $message = 'Reset Success';            
            $block = 3;
        }
        if(isset($_POST['cancel']))
        {
            
            $message = 'Admin Canceled';
            $block = 3 ;
        }
        include TPL_DIR.'/Euro_ResetUserData.php';
    }
    /**
    * in case user bought protect account function
    * user lost pass, proven
    * 
    */
    public function DeletePassword()
    {
        if(!Controller::checkPower(1))
            return false;
            
        $block = 1;
        
        if(isset($_POST['uIdSubmitted']) && !empty($_POST['uId']))
        {
            $uId = $_POST['uId'];
            $block = 2;
        }
        if(isset($_POST['submitFinal']) && isset($_POST['uIdFinal']))
        {
            $uId = $_POST['uIdFinal'];
            
            $oUser = User :: getById($uId) ;
            if(is_object($oUser))
            {
                if($oUser->passwordState != PasswordState::IS_UNAVIABLE)
                {
                    $oUser->passwordState = PasswordState::NO_PASSWORD;     
                    $md5Password = "";
                    $oUser->setMd5Password($md5Password);
                    $oUser->forceSave();
                    $message = 'Reset Success';                
                } else $message = 'User ko hop le';
            } else $message = 'User ko ton tai';                                   
            
            $block = 3;
        }
        if(isset($_POST['cancel']))
        {
            
            $message = 'Admin Canceled';
            $block = 3 ;
        }
            
        include TPL_DIR.'/DeletePassword.php';
    }
    
    public function ResetUser()    
    {
        if(!Controller::checkPower(1))
            return false ;

        if(isset($_POST['uid']))
            $uId = intval($_POST['uid']);
            
        $block = 1 ;
             
        if(empty($uId))
        {
            //echo 'ban can nhap vao uId';
            $mess = 'uId - Khong hop le  ' ;
        }            

        // xoa du lieu User
        if(!empty($uId) && isset($_POST['ResetData']))
        {
            $oAdminTool=new AdminTool();
            if(!$oAdminTool->ResetUser($uId))
            {
               $mess = 'ko Thanh cong ' ;   
            }
            else
            {
               $mess = 'OK - update Thanh cong ' ;    
            }
            
            $block = 2 ;
        }
        if(isset($_POST['Ok']))
        {
            StaticCache::forceSaveAll();
            $mess = 'OK - update Thanh cong ' ;
            $block = 1 ;
        }
        
        include TPL_DIR . '/ResetUser.php';
        
    }
    
    public function ResetOccupyBoard()
    {
        if(!Controller::checkPower(1))
            return false ;
        $block = 1;
        if(isset($_POST['submitFinal']))
        {
            DataProvider::delete('share','Top10OccupierCache'); 
            DataProvider::getMemBase()->delete('Total_OccupyRankInitial_DataRunTime'); 
            $message = 'Success';
            $block = 2;    
        }
        if(isset($_POST['cancel']))
        {
            
            $message = 'Admin Canceled';
            $block = 2 ;
        }
        
        include TPL_DIR . '/ResetOccupyBoard.php';
    }
    
    public function ShowOccupyBoard()
    {
        if(!Controller::checkPower(1))
            return false ;            
        $block = 1;
        if(isset($_POST['getRank']))
        {
            if(isset($_POST['DateBoard']))
            {
                $strDate = trim($_POST['DateBoard']);
            }
            else $strDate = date('Y-m-d', time());
            $timeEndConf = '10:00:00';
            $GiftEndOccupyTime = strtotime($strDate . ' ' . $timeEndConf);
            if(!empty($_POST['UidBoard']))
            {
                $uid = trim($_POST['UidBoard']);
                // in cache
                $RankBigBoardCache = DataRunTime::getDataTime('OccupiedBak', $GiftEndOccupyTime);
                if(empty($RankBigBoardCache))
                   $uRankCache = 'Not Exist Cache';
                else
                {
                    $uRankCache = $RankBigBoardCache[$uid];                
                    if(empty($uRankCache))
                        $uRankCache = 'Out of Top';    
                }                 
                // in bak                        
                $sql = "select Rank from Occupy_OccupiedBigBoardBak where RankTime = {$GiftEndOccupyTime} and Uid = {$uid}" ;            
                $res = Common::queryMySql(OccupyFea::CODE,$sql);
                if($res)
                 {  
                    if($row = mysql_fetch_array($res,MYSQL_ASSOC))
                     {   
                         $uRankBak = $row['Rank'];
                     }
                    else $uRankBak = 'Out of Top';
                    $block = 2;
                 }
                 else
                 {                   
                    $message = 'Not connected to mysql server';
                    $block = 3;             
                 }
            }                                   
            else 
            {
                $message = 'Empty Uid. Please Input';
                $block = 3;
            }           
        }
                 
        include TPL_DIR . '/ShowOccupyBoard.php';
    }
    
    public function Config6Star() {
        if(!Controller::checkPower(3))
            return false ;                    
        $Num6Star = intval(DataRunTime::get('Num6Star',true));
        $NumView6Star = intval(DataRunTime::get('NumView6Star',true));
        $Quota_6  = intval(DataRunTime::get('Quota_6',true));
        $Quota_12  = intval(DataRunTime::get('Quota_12',true));
        $Quota_18  = intval(DataRunTime::get('Quota_18',true));
        $Quota_24  = intval(DataRunTime::get('Quota_24',true)); 
               
        $Num_6  = intval(DataRunTime::get('Num_6',true));
        $Num_12  = intval(DataRunTime::get('Num_12',true));
        $Num_18  = intval(DataRunTime::get('Num_18',true));
        $Num_24  = intval(DataRunTime::get('Num_24',true)); 
        
        $DateKey  = DataRunTime::get('DateKey',true); 
        $QuotaVipMax = DataRunTime::get('QuotaVipMax',true); 

        if(isset($_POST['Submit']) && $_POST['Submit'] =='Save' ) {
            if(isset($_POST['NumView6Star']) && intval($_POST['NumView6Star']) >=0 )
                $NumView6Star = intval($_POST['NumView6Star']);
                
            if(isset($_POST['Quota_6']) && intval($_POST['Quota_6']) >=0 )
                $Quota_6 = intval($_POST['Quota_6']);
            if(isset($_POST['Quota_12']) && intval($_POST['Quota_12']) >=0 )
                $Quota_12 = intval($_POST['Quota_12']);
            if(isset($_POST['Quota_18']) && intval($_POST['Quota_18']) >=0 )
                $Quota_18 = intval($_POST['Quota_18']);
            if(isset($_POST['Quota_24']) && intval($_POST['Quota_24']) >=0 )
                $Quota_24 = intval($_POST['Quota_24']);            
                
            // save to membase
            DataRunTime::set('NumView6Star', $NumView6Star, true);
            DataRunTime::set('Quota_6', $Quota_6, true);
            DataRunTime::set('Quota_12', $Quota_12, true);
            DataRunTime::set('Quota_18', $Quota_18, true);
            DataRunTime::set('Quota_24', $Quota_24, true);                                                
        }
        
        include TPL_DIR . '/Config6Star.php';
    }
    
    
}

?>
