<?php
// Logger      logpatch

$CONFIG_DATA['Logger'] = false ;

$CONFIG_DATA['logpatch'] = "./log/" ;

/*
Config servers and buckets
*/
$CONFIG_DATA['Servers'] = Common::getSysConfig('dbServer') ;
$CONFIG_DATA['Buckets'] = Common::getSysConfig('dbBuckets') ; 
// Config cac key cua du an

$CONFIG_DATA['Key']['Data'] = array(
                                    'Cache' => false
									,'Expire' => 0
                                    );

$CONFIG_DATA['Key']['Quest'] = array(
                                    'Cache' => false
									,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['NewQuest'] = array(
                                    'Cache' => false
									,'Expire' => 0
                                    );                                   

$CONFIG_DATA['Key']['User'] = array(
                                    'Cache' => false
									,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['UserProfile'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['Lake'] = array(
                                    'Cache' => false
									,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['Decoration'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );									
$CONFIG_DATA['Key']['Store'] = array(
                                    'Cache' => false
									,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['Market'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );                                    
$CONFIG_DATA['Key']['Page'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    ); 
$CONFIG_DATA['Key']['PageManagement'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    ); 
$CONFIG_DATA['Key']['Cache'] = array(
                                    'Cache' => true
                                    ,'Expire' => mktime(0, 0, 0) + 24*60*60 - $_SERVER['REQUEST_TIME']
                                    );
$CONFIG_DATA['Key']['FishWorld'] = array(
                                    'Cache' => false
									,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['MiniGame'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );                                    
$CONFIG_DATA['Key']['Friends'] = array(
                                    'Cache' => true
									//,'Expire' => mktime(0, 0, 0) + 24*60*60 - $_SERVER['REQUEST_TIME']
                                    ,'Expire' => 24*60*60
                                    );
$CONFIG_DATA['Key']['GiftBox'] = array(
                                    'Cache' => true
                                    ,'Expire' => 60*60*24*7
                                    );
$CONFIG_DATA['Key']['MailBox'] = array(
                                    'Cache' => true
                                    ,'Expire' => 60*60*24*7
                                    );
$CONFIG_DATA['Key']['SystemMail'] = array(
                                    'Cache' => false
                                    ,'Expire' => 60*60*24*7
                                    );
$CONFIG_DATA['Key']['SystemNotify'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['ItemCode'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['ConfigItemCode'] = array(
                                    'Cache' => false
                                    ,'Expire' => 60*60*24*30
                                    );
$CONFIG_DATA['Key']['Diary'] = array(
                                    'Cache' => true
                                    ,'Expire' => 60*60*24
                                    );
$CONFIG_DATA['Key']['designTool'] = array(
                                    'Cache' => false
                                    ,'Expire' => 60*60*24
                                    );

$CONFIG_DATA['Key']['Link'] = array(
                                    'Cache' => true
                                    ,'Expire' => 20*60*60*24
                                    );                                    
$CONFIG_DATA['Key']['FishTournament'] = array(
									'Cache' => false
                                    ,'Expire' => 0
									);          
$CONFIG_DATA['Key']['FishTourManager'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['Ingredients'] = array(
									'Cache' => false
                                    ,'Expire' => 0
									);
$CONFIG_DATA['Key']['Event'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
					);                  			                                    
$CONFIG_DATA['Key']['PowerTinhQuest'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    ); 
$CONFIG_DATA['Key']['StoreEquipment'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    ); 
$CONFIG_DATA['Key']['AddXuInfo'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );

$CONFIG_DATA['Key']['TrainingGround'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['EuroFixture'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    ); 
$CONFIG_DATA['Key']['EuroTop']['Profile'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    ); 
$CONFIG_DATA['Key']['EuroTop']['Order'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    ); 
$CONFIG_DATA['Key']['EuroInfo'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['ServerBoss'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
// Occupy - Lien dau Feature                                    
$CONFIG_DATA['Key']['OccupyingProfile'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['Top10OccupierCache'] = array(
                                    'Cache' => true
                                    ,'Expire' => 60*60*2
                                    );                                                                   
//HammerMan
$CONFIG_DATA['Key']['HammerMan'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );

//AccumulationPoint
$CONFIG_DATA['Key']['AccumulationPoint'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
//SmashEgg
$CONFIG_DATA['Key']['SmashEgg'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
                                    
$CONFIG_DATA['Key']['Noel'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );
$CONFIG_DATA['Key']['KeepLogin'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );

$CONFIG_DATA['Key']['VipBox'] = array(
                                    'Cache' => false
                                    ,'Expire' => 0
                                    );


$CONFIG_DATA['Key']['LastTimeLogEquipment'] = array(
                                    'Cache' => true,
                                    'Expire' => 60*60*7
                                    ); 
$CONFIG_DATA['Key']['SB_TopUser'] = array(
                                    'Cache' => true,
                                    'Expire' => 7*60*60*24
                                    );

$CONFIG_DATA['Key']['SB_TopUser_Boss'] = array(
                                    'Cache' => true,
                                    'Expire' => 7*60*60*24      
                                    ); 
$CONFIG_DATA['Key']['SB_LastHit'] = array(
                                    'Cache' => true,
                                    'Expire' => 7*60*60*24      
                                    );   
$CONFIG_DATA['Key']['SB_LastHit_Boss'] = array(
                                    'Cache' => true,
                                    'Expire' => 7*60*60*24      
                                    );  
$CONFIG_DATA['Key']['WinBoss'] = array(
                                    'Cache' => true,
                                    'Expire' => 60*60*1
                                    ); 
$CONFIG_DATA['Key']['LastTimeGetGift'] = array(
                                    'Cache' => true,
                                    'Expire' => 60*60*1
                                    );   
$CONFIG_DATA['Key']['SealExchangeHerbMedal'] = array(
                                    'Cache' => true,
                                    'Expire' => 0
                                    );
$CONFIG_DATA['Key']['ResetMarket'] = array(
                                    'Cache' => false,
                                    'Expire' => 0
                                    );
$CONFIG_DATA['Key']['ResetMarketTime'] = array(
                                    'Cache' => false,
                                    'Expire' => 0
                                    );                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
                                    