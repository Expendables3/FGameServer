<?php
// Logger      logpatch

$CONFIG_DATA['Logger'] = false ;
$CONFIG_DATA['logServer'] = array('10.30.44.33') ;
$CONFIG_DATA['appKey'] = "" ;
unset($_SYS_CONFIG);
$_SYS_CONFIG['secretkey']			=	'1647737a6091f41cd0f4e02a00e80ea2';
//$_SYS_CONFIG['secretkey']			=	'testkey';

/*
Config servers and buckets
*/
$CONFIG_DATA['Servers'] = array(
                                    array('10.30.44.42',50),
                                  array('10.30.44.43',50),
								  array('10.30.44.196',50),
								  array('10.30.44.205',50),
                        ) ;
$CONFIG_DATA['Buckets'] = array(
                                  'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11213, true),
                               );
