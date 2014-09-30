<?php
return array(
    'Add'=>array(
        // add into User
        '--selectItem--'=>array(),
        'Money'     => array('Num'),
        'ZMoney'    => array('Num'),
        'Exp'       => array('Num'),
        'SuperFish' =>array('Id','ItemType','Money','Exp','Time','Expired','Num',),//Sparta,Batman,Spiderman,Swat,...
        'Fish'      =>array('Id','ItemId','FishType','Num'),
        'Decoration'=>array('Id','ItemType','ItemId','Num'),//Other,OceanAnimal,OceanTree,BackGround
        'Item'      =>array('ItemType','ItemId','Num'),//EnergyItem,Material,Viagra,......
        'Equipment' =>array('Id','ItemType','Num','Level','Color','Element','Enchant','NumOption','Source',
        'NameOp1','ValueOp1','NameOp2','ValueOp2','NameOp3','ValueOp3','NameOp4','ValueOp4','NameOp5','ValueOp5'),
        'Soldier'   =>array(),
        'ReputationLevel'=>array('Level','Point','resetquest'),
    ),    
    'Delete' =>array(
        // add into User
        '--selectItem--'=>array(),
        'SuperFish' =>array('Id','ItemType','IsLake'),//Sparta,Batman,Spiderman,Swat,...
        'Fish'      =>array('Id','IsLake'),
        'Decoration'=>array('Id','ItemType','IsLake'),//Other,OceanAnimal,OceanTree,BackGround
        'Item'      =>array('ItemType','ItemId','Num'),//EnergyItem,Material,Viagra,...... mac dinh la trong kho
        'EquipmentInStore' =>array('Id','ItemType'),
        'EquipmentInSoldier' =>array('LakeId','SoldierId','EquipmentType','EquipmentId'),
        'Soldier'   =>array(),
        'EquipmentFollowCodition'=>array('ItemType','Rank','Color'),
    ),
    'Edit' =>array(
        // add into User
        '--selectItem--'=>array(),
        'SuperFish' =>array('Id','ItemType','IsLake'),//Sparta,Batman,Spiderman,Swat,...
        'Fish'      =>array('Id','IsLake'),
        'Decoration'=>array('Id','ItemType','IsLake'),//Other,OceanAnimal,OceanTree,BackGround
        'Item'      =>array('ItemType','ItemId','Num'),//EnergyItem,Material,Viagra,...... mac dinh la trong kho
        'EquipmentInStore' =>array('Id','ItemType'),
        'EquipmentInSoldier' =>array('LakeId','SoldierId','EquipmentType','EquipmentId'),
        'Soldier'   =>array(),
        'FirstAddXu'=>array('Num'),
    ),
);

?>
