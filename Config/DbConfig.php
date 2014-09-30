<?php
$db_Dev = array(
    'ServerBoss' => array(
        'Host' => '10.198.48.108',
        'User' => 'fishdev',
        'Pass' => 'alibaba',
        'Database' => 'FishDataGame',
    ),
    'Occupy' =>  array(
        'Host' => '10.198.48.108',
        'User' => 'fishdev',
        'Pass' => 'alibaba',                                                               
        'Database' => 'FishDataGame',
    ),       
) ;

$db_QC = array(
    'ServerBoss' => array(
        'Host' => '10.198.48.108',
        'User' => 'fishdev',
        'Pass' => 'alibaba',
        'Database' => 'FishDataGame_QC',
    ),
    'Occupy' =>  array(
        'Host' => '10.198.48.108',
        'User' => 'fishdev',
        'Pass' => 'alibaba',
        'Database' => 'FishDataGame_QC',
    ),       
) ;
$db_Private = array(
    'ServerBoss' => array(
        'Host' => '10.30.44.140',
        'User' => 'privateadmin',
        'Pass' => '@privateadmin',
        'Database' => 'FishDataGame',
    ),
    'Occupy' =>  array(
        'Host' => '10.30.44.140',
        'User' => 'privateadmin',
        'Pass' => '@privateadmin',
        'Database' => 'FishDataGame',
    ),       
) ;

$db_Live = array(
    'ServerBoss' => array(
        'Host' => '10.30.44.13',
        'User' => 'livefeaeve',
        'Pass' => '@lalibaba$4u',
        'Database' => 'FishDataGame',
    ),
    'Occupy' =>  array(
        'Host' => '10.30.44.13',
        'User' => 'livefeaeve',
        'Pass' => '@lalibaba$4u',
        'Database' => 'FishDataGame',
    ),
);

$release = 2;
if($release === 0)
    return $db_Dev ;
elseif($release === 1)
    return $db_QC ;
elseif($release === 2)
    return $db_Private;
else return $db_Live;

?>
