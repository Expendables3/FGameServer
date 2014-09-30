<?php
/**
 * @author AnhBV
 * @version 1.0
 * @created 9-9-2010
 * @Description : phan nay dinh nghia cac thong bao se duoc up len tuong nha ban
 */

//$link ='<a href="http://me.zing.vn/....">myFish</a>';
$Link_1 = Common::getSysConfig('domain') ;
//$link = '<a href ='.$Link_1.'> myFish</a>' ;
$link = '' ;
$link_Icon = $Link_1.'/imgcache/iconfeed/';
$link_invite =$Link_1.'/web/index_invite.php?friendId='.Controller::$uId;
$link_invite = '<a href ="'.$link_invite.'" > myFish </a>';
return array(
'create' => array(
    'Id'    => 1,
    'Name'  => 'create' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'khoitao.png',
    'SystemMessage' => array(
         1=>' Ã€ hÃ¡! Tráº¡i cÃ¡ myFish cá»§a tá»› nÃ¨! Báº¡n cÃ³ chÆ°a? ' ,
         2=>'Tráº¡i CÃ¡ myFish nÃ¨! TrÃ² má»›i Ä‘Ã³, tham quan thá»­ Ä‘i ^_^',
        ),
    'LikeMessage' => '',
    'WallMsg' => '@username@ vừa tậu Trại cá trong myFish.Nếu bạn chưa có Trại cá myFish. Hãy tham gia ngay để trải nghiệm những khoảnh khác bất ngờ và vui vẻ'.$link,
    'Public' => true
    ),
'unlockNewFish' => array(
    'Id'    => 2,
    'Name'  => 'unlockNewFish' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'newFish.png',
    'SystemMessage' => array(
         1=>'láº¡i cÃ³ má»™t chÃº cÃ¡ má»›i rá»“i, hi hi ? ' ,
         2=>'^_^',
        ),
    'LikeMessage' => 'Bấm "Thích" để chia sẻ điều này nha bạn.',
    'WallMsg' => '@username@ vừa lai thành công được @itemname@ trong '.$link,
    'Public' => true
    ),
'levelUp' => array(
    'Id'    => 3,
    'Name'  => 'levelUp' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'lencap.png',
    'SystemMessage' => array(
         1=>'uii khÃ´ng pháº£i lÃ  mÃ¬nh láº¡i vá»«a lÃªn cáº¥p Ä‘Ã³ chá»©  8* ',
         2=>'sao lÃªn cáº¥p nhanh quÃ¡ dzá»‹  :Q',
         3=>'VÃ¬ sao lÃªn cáº¥p??? á»Ÿ dÆ°á»›i tháº¥p cÃ³ Ä‘Æ°á»£c hok????',
         4=>'Ä‘Ã£ lÃªn cáº¥p rÃ¹i!  WÃ© giá»�i. Há»› há»› /strong',
         5=>'chá»‰ tay lÃªn trá»�i.Háº­n Ä‘á»�i vÃ´ Ä‘á»‘i. Ha ha  B-)',
        ),
    'LikeMessage' => 'Bấm "Thích" để chia sẻ điều này nha bạn.',
    'WallMsg' => '@username@ vừa đạt cấp độ @level@ trong '.$link,
    'Public' => true
    ),
'buyMixLake' => array(
    'Id'    => 4,
    'Name'  => 'buyMixLake' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'belai.png',
    'SystemMessage' => array(
         1=>'Miáº¿ng cÆ¡m manh Ã¡o cá»§a tui Ä‘Ã³',
         2=>'Cáº§n cÃ¢u cÆ¡m cá»§a tui Ä‘Ã³',
        ),
    'LikeMessage' => '',
    'WallMsg' => '@username@ vừa mua được bể lai @itemname@ trong '.$link,
    'Public' => true ,
    ),
'upgradeLake' => array(
    'Id'    => 5,
    'Name'  => 'upgradeLake' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'nangcapho.png',
    'SystemMessage' => array(
         1=>'má»Ÿ rá»™ng há»“ cÆ¡ Ä‘á»“ phÆ¡i phá»›i',
         2=>'Ä‘Ã£ má»Ÿ rá»™ng há»“ cÃ¡ , thá»�a sá»©c lÃ m ngÆ° dÃ¢n rÃ¹i :D',
         3=>'ngÆ°á»�i ta cÃ³ tiá»�n, ngÆ°á»�i ta nÃ¢ng cáº¥p há»“, cÃ³ gÃ¬ mÃ  tÃ² mÃ²   /<@',
         4=>'Ä‘Ã£ lÃ m Äƒn lÃ  pháº£i chÆ¡i lá»›n,quy mÃ´ cÃ ng lá»›n lá»£i nhuáº­n cÃ ng cao :D',
        ),
    'LikeMessage' => 'Bạn đã thử tính năng này chưa. Bấm "Thích" để chia sẻ với bạn bè nhé',
    'WallMsg' => '@username@ vừa nâng cấp hồ cá trong '.$link,
    'Public' => true
    ),
'completeQuest_1' => array(
    'Id'    => 6,
    'Name'  => 'completeQuest_1' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'hoanthanhnhiemvusonhap.png',
    'SystemMessage' => array(
         1=>'Nhiá»‡m vá»¥ dá»…, lÃªn cáº¥p nhanh, tháº­t lÃ  zui wÃ¡ Ä‘i',
         2=>'tÃ´i Ä‘Ã£ hoÃ n thÃ nh rá»“i Ä‘Ã³ báº¡n cÃ²n chá»� gÃ¬ ná»¯a',
        ),
    'LikeMessage' => '',
    'WallMsg' => '@username@ vừa hoàn thành xong nhiệm vụ Làm quen trong '.$link,
    'Public' => true
    ),
'completeQuest_2' => array(
    'Id'    => 7,
    'Name'  => 'completeQuest_2' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'hoanthanhnhiemvusonhap.png',
    'SystemMessage' => array(
         1=>'tÃ´i Ä‘Ã£ hoÃ n thÃ nh rá»“i Ä‘Ã³ báº¡n cÃ²n chá»� gÃ¬ ná»¯a',
        ),
    'LikeMessage' => '',
    'WallMsg' => '@username@ vừa hoàn thành xong nhiệm vụ Lai Cá trong '.$link,
    'Public' => true
    ),    
'fishing' => array(
    'Id'    => 8,
    'Name'  => 'fishing' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'fish.png',
    'SystemMessage' => array(
         1=>'láº¡i cÃ¢u Ä‘Æ°á»£c cÃ¡ khá»§ng nÃ¨ ',
        ),
    'LikeMessage' => 'Bạn có muốn câu trộm nữa hem? Bấm "Thích" để cho mọi người biết thành tích của bạn nha.',
    'WallMsg' => '@username@ vừa câu được một con @itemname@ trong '.$link,
    'Public' => true
    ),
 'unlockLake' => array(
    'Id'    => 9,
    'Name'  => 'unlockLake' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'newLake.png',
    'SystemMessage' => array(
         1=>'ngÆ°á»�i ta cÃ³ tiá»�n, ngÆ°á»�i ta mua há»“, cÃ³ gÃ¬ mÃ  tÃ² mÃ²   /<@',
         2=>'LÃ  LÃ¡ La! Há»“ to thÃ¬ máº·c há»“ to,náº¿u mÃ  to quÃ¡â€¦ta mua thÃªm há»“  /;P',
         3=>'e hÃ¨m!Há»“ má»›i chuyÃªn Ä‘á»ƒ chá»©a hÃ ng khá»§ng Ä‘Ã¢y nha bÃ  con :P',
        ),
    'LikeMessage' => '',
    'WallMsg' => '@username@ vừa tậu được một hồ mới trong '.$link,
    'Public' => true
    ),
 'mixOverLevel' => array(
    'Id'    => 10,
    'Name'  => 'mixOverLevel' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'newFish.png',
    'SystemMessage' => array(
         1=>'KhÃ´ng cÃ³ gÃ¬ lÃ  khÃ´ng thá»ƒ :d',
         2=>'Ä�á»«ng há»�i vÃ¬ sao má»�nh láº¡i giá»�i nhÆ° tháº¿ nha cÃ¡c tÃ¬nh yÃªu ;-d',
         3=>'Tá»± tin, phong cÃ¡ch,khÃ´ng run tay má»›i lai ra Ä‘Æ°á»£c Ä‘Ã³ ;p',
         4=>'VÃ¬ sao vÆ°á»£t cáº¥p??? Váº«n nhÆ° tháº¿ cÃ³ Ä‘Æ°á»£c hem???',
        ),
    'LikeMessage' => 'Cảm nhận chức năng Lai cá trong myFish và bấm "Thích" nha ^^',
    'WallMsg' => '@username@ vừa lai ra cá vượt cấp thành công trong'.$link,
    'Public' => true
    ),
 'specialFish' => array(
    'Id'    => 11,
    'Name'  => 'specialFish' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'IconSpecialFish.png',
    'SystemMessage' => array(
         1=>'Ä�áº·c biá»‡t thÃ¬ cÃ³ sao, láº§n sau lÃ  cÃ¡ quÃ­ :d',
         2=>'KhÃ´ng thá»ƒ hoÃ£n cÃ¡i sá»± sung sÆ°á»›ng nÃ y Ä‘Æ°á»£c :b :b',
         3=>'Tiáº¿c quÃ¡! TÃ½ thÃ¬ Ä‘Æ°á»£c cÃ¡ quÃ­ rá»“i :~',
        ),
    'LikeMessage' => 'Cảm nhận chức năng Lai cá trong myFish và bấm "Thích" nha ^^',
    'WallMsg' => '@username@ vừa lai thành công cá đặc biệt trong '.$link,
    'Public' => true
    ),

 'rareFish' => array(
    'Id'    => 12,
    'Name'  => 'rareFish' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'IconRareFish.png',
    'SystemMessage' => array(
         1=>'CÃ¡ quÃ­ bá»Ÿi vÃ¬ khÃ´ng gÃ¬ hiáº¿m hÆ¡n nÃ³ ;p ;p',
         2=>'KhÃ´ng pháº£i lÃ  khá»§ng mÃ  lÃ  ráº¥t khá»§ng ;p ;p',
        ),
    'LikeMessage' => 'Cảm nhận chức năng Lai cá trong myFish và bấm "Thích" nha ^^',
    'WallMsg' => '@username@ vừa lai thành công cá quý trong'.$link,
    'Public' => true
    ),
 'material_3' => array(
    'Id'    => 13,
    'Name'  => 'material_3' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'IconMat3.png',
    'SystemMessage' => array(
         1=>'CÃ³ cÃ´ng Ä‘áº­p ngá»�c cÃ³ ngÃ y lÃªn cáº¥p :d :d',
         2=>'Ä�Ã£ cÃ³ láº§n Ä‘áº§u táº¥t cÃ³ láº§n sau b-)',
        ),
    'LikeMessage' => 'Bạn hãy thử ép xem sao ^^. Bấm "Thích" để chia sẻ nha',
    'WallMsg' => '@username@ vừa ép ra nguyên liệu cấp 3 trong'.$link,
    'Public' => true
    ),
 'material_4' => array(
    'Id'    => 14,
    'Name'  => 'material_4' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'IconMat4.png',
    'SystemMessage' => array(
         1=>'QuÃ¡ thÆ°á»�ng cÃ¡c tÃ¬nh yÃªu áº¡ :d :d',
         2=>'CÃ³ khÃ³ chi Ä‘Ã¢u 1 buá»•i chÃ¬u báº¥m vÃ o 1 cÃ¡i tháº¿ lÃ  xong ;p ;p ;p',
        ),
    'LikeMessage' => 'Bấm "Thích" để khoe thành tích nào',
    'WallMsg' => '@username@ thật là giỏi khi ép ra nguyên liệu cấp 4 trong'.$link,
    'Public' => true
    ),

 'material_5' => array(
    'Id'    => 15,
    'Name'  => 'material_5' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'IconMat5.png',
    'SystemMessage' => array(
         1=>'Ä�á»«ng máº¯ng má»�nh , hem pháº£i má»�nh cá»‘ Ã½ Ä‘Ã¢u Ã  8* 8*',
         2=>'CÃ¡i nÃ y hem pháº£i do may máº¯n Ä‘Ã¢u, do trÃ¬nh Ä‘á»™ áº¥y :-dig',
        ),
    'LikeMessage' => 'Bấm "Thích" để khoe thành tích',
    'WallMsg' => '@username@ siêu giỏi khi ép ra nguyên liệu cấp 5 trong'.$link,
    'Public' => true
    ),
  
  'getGiftDay' => array(
    'Id'    => 16,
    'Name'  => 'getGiftDay' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'IconGiftDaily.png',
    'SystemMessage' => array(
         1=>'Hay khÃ´ng báº±ng hÃªn ;-d ;-d',
        ),
    'LikeMessage' => 'Bấm "Thích" để cho mọi người biết đi nào ^^',
    'WallMsg' => '@username@ là 1 người vô cùng may mắn khi đã nhận được quà tặng khủng trong '.$link,
    'Public' => true
    ),
  'unlockSlotMaterial' => array(
    'Id'    => 17,
    'Name'  => 'unlockSlotMaterial' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'unlockSlotMaterial.png',
    'SystemMessage' => array(
         1=>'Chỉ tay là đến, bấm nút là mở :d :d',
        ),
    'LikeMessage' => '@username@ đích thực là 1 người tài giỏi,người ta đã mở được slot nguyên liệu rồi đấy.Còn chờ gì nữa mà hem bấm "Thích" hả bạn ^__^',
    'WallMsg' => '@username@ vừa unlock thành công slot nguyên liệu thứ @itemname@ trong '.$link,
    'Public' => true
    ),
    
   'rareFishGift' => array(
    'Id'    => 18,
    'Name'  => 'rareFishGift' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'IconRareFish.png',
    'SystemMessage' => array(
         1=>'mình vừa nhận được cá Quý nè ',
        ),
    'LikeMessage' => 'Hãy bấm "Thích" để chia vui với mình nha',
    'WallMsg' => '@username@ vừa mới nhận được @itemname@ quý có khả năng đặc biệt trong '.$link,
    'Public' => true
    ),
    // hoan thanh cac series quest ve skill   
    'completeSkillQuest' => array(
    'Id'    => 19,
    'Name'  => 'completeSkillQuest' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'hoanthanhnhiemvusonhap.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bạn đã hoàn thành nhiệm vụ này chưa ? Bấm Thích để chia sẻ với mình nhé !',
    'WallMsg' => '@username@ đã hoàn thành nhiệm vụ @mission@ trong trại cá '.$link,
    'Public' => true
    ),
    
   'feedDailyQuest2' => array(
    'Id'    => 21,
    'Name'  => 'feedDailyQuest' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'iconDailyQuest2.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" để cùng chia vui với bạn nào !',
    'WallMsg' => '@username@ đã may mắn nhận được @itemname@ từ Nhiệm vụ hàng ngày trong ',
    'Public' => true
    ),
    
    'feedDailyQuest3' => array(
    'Id'    => 21,
    'Name'  => 'feedDailyQuest' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'iconDailyQuest2.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" để cùng chia vui với bạn nào !',
    'WallMsg' => '@username@ đã may mắn nhận được @itemname@ từ Nhiệm vụ hàng ngày trong ',
    'Public' => true
    ),
    
   'upgradeMixSkill' => array(
    'Id'    => 22,
    'Name'  => 'upgradeMixSkill' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'iconDailyQuest2.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" để cùng chia vui với bạn nào !',
    'WallMsg' => '@username@ đã nâng cấp thành công @itemname@ lên cấp @mission@ trong trại cá '.$link,
    'Public' => true
    ),
    
    'inviteFriend' => array(
    'Id'    => 23,
    'Name'  => 'inviteFriend' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'iconDailyQuest2.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" ngay còn chờ gì nữa!',      
    'WallMsg' => '@username@ đã gửi một lời mời vô cùng hấp dẫn từ '.$link_invite.' cho tất cả bạn bè ',
    'Public' => true
    ),
    
    'upgradeMaterialSkill' => array(
    'Id'    => 23,
    'Name'  => 'upgradeMaterialSkill' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'upgradeMaterialSkill.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" để cùng chia vui với bạn nào !',
    'WallMsg' => '@username@ đã nâng cấp thành công kỹ năng ghép ngư thạch lên cấp @itemname@ trong trại cá '.$link,
    'Public' => true
    ),
    
   'finishInviteFriend_1' => array(
    'Id'    => 24,
    'Name'  => 'finishInviteFriend_1' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'finishInviteFriend_1.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" ngay còn chờ gì nữa!',
    'WallMsg' => '@username@ đã hoàn thành nhiệm vụ đầu tiên "Thêm bạn mới" trong sự kiện "Thêm bạn bè - thêm năng lượng" của trại cá '.$link,
    'Public' => true
    ),
   'finishInviteFriend_2' => array(
    'Id'    => 25,
    'Name'  => 'finishInviteFriend_2' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'finishInviteFriend_2.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" ngay còn chờ gì nữa!',
    'WallMsg' => '@username@ đã hoàn thành nhiệm vụ thứ hai "Cùng nhau lên cấp" trong sự kiện "Thêm bạn bè - thêm năng lượng" của trại cá '.$link,
    'Public' => true
    ),
    'finishInviteFriend_3' => array(
    'Id'    => 26,
    'Name'  => 'finishInviteFriend_3' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'finishInviteFriend_3.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" ngay còn chờ gì nữa!',
    'WallMsg' => '@username@ đã hoàn thành nhiệm vụ cuối cùng"Lên cấp cao chào quà khủng" trong sự kiện "Thêm bạn bè - thêm năng lượng" của trại cá '.$link,
    'Public' => true
    ),
   'dailyBonus' => array(
    'Id'    => 27,
    'Name'  => 'dailyBonus' ,
    'Num'   => 1,
    'Icon' => $link_Icon.'dailyBonus.png',
    'SystemMessage' => array(
         1=>'',
        ),
    'LikeMessage' => 'Bấm "thích" ngay còn chờ gì nữa!',
    'WallMsg' => '@username@ đã nhận được quà tặng hằng ngày cực khủng trong '.$link,
    'Public' => true
    ),
    
    

);
?>