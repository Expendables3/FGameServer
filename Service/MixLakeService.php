<?php

/**
* @author AnhBV
* @version 1.0
* @created 2-9-2010
* @Description : thuc hien viec xu ly phan ho lai
*/
class MixLakeService 
{

   	/**
	* @author AnhBV
	* @created 04-10-2010
	* @Description : ham thuc hien viec mua ho lai ca
	*/
    public function buyMixLake($param)
    {
      $TypeId = $param['TypeId'];
      if (!Controller::$uId)
	  {
	    return array('Error' => Error :: LOGIN) ;
	  }
      $MConfig = Common::getConfig('MixLake');
      if (empty ($TypeId) || ($TypeId < 1) || ($TypeId > count($MConfig)))
      {
      	return array('Error' => Error :: PARAM) ;
      }

      Common :: loadModel('User') ;
      $oUser = User :: getById(Controller::$uId) ;
      if (!is_object($oUser))
      {
      	return array('Error' => Error :: NO_REGIS) ;
      }

      $MixConfig = $MConfig[$TypeId];
      // kiem tra tien va level ngoi choi xem co du khong
      if(!$oUser->addMoney(-$MixConfig['Money']))
      {
            return array('Error' => Error :: NOT_ENOUGH_MONEY) ;
      }
      if($oUser->getLevel() < $MixConfig['LevelRequire']  )
      {
        	return array('Error' => Error :: NOT_ENOUGH_LEVEL) ;
      }

      // kiem tra xem ho loai nay ban da co chua
      Common::loadModel('MixLake');

      // luu vao kho
      Common::loadModel('Store');
      $oStore = Store::getById(Controller::$uId);
      $oStore->addItem('MixLake', $TypeId, 1);       
      $oStore->save();    

      // tru tien nguoi cho va cong diem kinh nghiem
      $oUser->addExp($MixConfig['Exp']);
      $oUser->save();

      $arr_result = array();
      $arr_result['Money']    = $oUser->Money ;
      $arr_result['Exp']      = $oUser->Exp ;
      $arr_result['Error']    =  Error :: SUCCESS;

      return $arr_result;
    }

   /* public function sellMixLake($param)
    {
      $MixLakeId =  $param['Id'];
      if (!Controller::$uId
      {
      	return array('Error' => Error :: LOGIN) ;
      }
      $MConfig = Common::getConfig('MixLake');
      if (empty ($MixLakeId) || ($MixLakeId < 1))
      {
      	return array('Error' => Error :: PARAM) ;
      }
      Common :: loadModel('User') ;
      $oUser = User :: getById(Controller::$uId ;

      if (!is_object($oUser))
      {
      	return array('Error' => Error :: NO_REGIS) ;
      }

      Common::loadModel('MixLake');
      $oMixLake = MixLake::getById(Controller::$uId,$MixLakeId);
      if(!is_object($oMixLake))
      {
        return array('Error' => Error :: OBJECT_NULL) ;
      }
      // kiem tra xem be co dang reset khong ?
     //if(!$oMixLake->checkResetTime())
      //{
      //   return array('Error' => Error :: MIXLAKE_IS_RESETTING) ;
      //}

      // cong them tien cho nguoi choi
      $conf = Common::getConfig('MixLake',$oMixLake->TypeId);
      $conf_param = & Common ::getParam();
      if(!is_array($conf)|| isset($conf['Money'])== FALSE || !is_array($conf_param))
      {
         return array('Error' => Error :: NOT_LOAD_CONFIG) ;
      }

      $mixLakeMoney = $conf['Money']/$conf_param['pa_4'];
      $oUser->addMoney($mixLakeMoney);  // chia 2

      // xoa be lai
      $oMixLake->delete();
      $oUser->save();

      $arr_result = array();
      $arr_result['Money'] =  $oUser->Money ;
      $arr_result['Error'] =  Error :: SUCCESS;

      Zf_log::write_act_log(Controller::$uId, Controller::$uId, 'sell_mixlake', $mixLakeMoney, 0, 0, $oUser->Level, "{$MixLakeId}");

      // thuc hien update quest
      if(!empty($param) && $param['IsQuest'])
      {
        Common::loadModel('Quest');
        $oQuest = Quest::getById(Controller::$uId);
        $oQuest->update('sellMixLake',$param);
        $oQuest->save();
      }

      return  $arr_result ;

    }*/

 }
?>