<html>
<head>
	<title>Feed test</title>
	<script type="text/javascript" src="http://static.me.zing.vn/feeddialog/js/zmfeeddialog-2.01.min.js" ></script>
</head>
<body>


<?php


define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
define( "SER_DIR" , ROOT_DIR ."/Service" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );
define( "BETA_DIR" , ROOT_DIR ."/BetaTest" );
define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");




$config_dev = array(
	'appname' => 'test',
	'apikey' => 'db177303aa0b1498e512f112803f846d',
	'secretkey' => '8d3738883de7a75b382bb1253f59c943',
	'env' => 'development'
);

$config_live = array(
	'appname' => 'test1',
	'apikey' => '4bc575ea590a0b297e526815a38e9565',
	'secretkey' => '8295a3d9b21c624ac0ff353733d227b8',
	'env' => 'production'
);


include_once LIB_DIR.'/zingme-sdk/BaseZingMe.php';
include_once LIB_DIR.'/zingme-sdk/ZME_Me.php';
include_once LIB_DIR.'/zingme-sdk/ZME_User.php';
include_once LIB_DIR.'/zingme-sdk/ZME_FeedDialog.php';

$config = $config_live;

$zm_Me = new ZME_Me($config_live);

$signed_request = $_REQUEST["signed_request"];

$uid = $zm_Me->getUserLoggedIn($signed_request);

echo "uid=" . $uid;
echo "<br>";


$secretkey = $config['secretkey'];
$userIdFrom = $uid;
$userIdTo = 0; //not support to userid_to
$actId = 1;
$tplId = 3;
$objectId = "";
$attachName = "Nhân vật đạt cấp 50";
$attachHref = "http://me.zing.vn/apps/phuckhoi";
$attachCaption = "pk.net.vn";
$attachDescription = "Nghe nói khi ai đạt cấp 50, sẽ xuất hiện một câu nói, chia sẻ câu nói này sẽ nhận được phần thưởng hậu hĩnh. Đạt cấp 50 đâu có gì khó!";
$mediaType = 1;
$mediaImage = "http://id.pk.net.vn/zm/zingapi/pk001.png";
$mediaSource = "http://me.zing.vn/apps/phuckhoi";
$actionLinkText = "Phục Khởi";
$actionLinkHref = "http://me.zing.vn/apps/phuckhoi";


$feedItem = new ZME_FeedItem($userIdFrom, $userIdTo, $actId, $tplId, $objectId, $attachName, $attachHref, $attachCaption, $attachDescription, $mediaType, $mediaImage, $mediaSource, $actionLinkText, $actionLinkHref);
$zm_FeedDialog = new ZME_FeedDialog($config);

$sig = $zm_FeedDialog->genFeedSigForDialog($feedItem);

echo "signature=" . $sig;
echo "<br>";

$feedId = 4584475422;
$validateKey = "fcc94127f2b6f628accc2931fd00d2b1";

$feedId_aftervalidate = $zm_FeedDialog->validateFeedId($feedId, $validateKey);
var_dump($feedId_aftervalidate);
echo "<br>";

?>

<a href="#" onclick="openFeedDialog();">openFeedDialog</a>
	
<script>
function fCallback(data) {
	if(data.action == 0) {
		//user cancel push feed
		//if game/app to track how many user press cancel/hide pushfeed can be keep track here
		alert('user cancel push feed');
	}
	else if(data.action == 1) {//push feed successful
		var feedId = data.feedId; //feedId of feed pushed
		var validateKey = data.validateKey; //validateKey used for app to verify feedId is published by this app/game
		var state = data.state;
		//if game/app need to validate feedId (with validateKey), app/game must redirect to another page with param feedid and validateKey and
		//use socialapi to validate feedId, with successful, app/game will know exactly user published feed successful.
		alert("feedid=" + feedId + "-validateKey=" + validateKey + "-state=" + state);
	}
}
	
function openFeedDialog(){
zmf.ui(
	{
		pub_key:"<?php echo $config['apikey']?>",
		sign_key:"<?php echo $sig?>",
		action_id:<?php echo $actId?>,
		uid_to: <?php echo $userIdTo?>,
		object_id: "",
		attach_name: "<?php echo $attachName?>",
		attach_href: "<?php echo $attachHref?>",
		attach_caption: "<?php echo $attachCaption?>",
		attach_des: "<?php echo $attachDescription?>",
		media_type:<?php echo $mediaType?>,
		media_img:"<?php echo $mediaImage?>",
		media_src:"<?php echo $mediaSource?>",
		actlink_text:"<?php echo $actionLinkText?>",
		actlink_href:"<?php echo $actionLinkHref?>",
		tpl_id:<?php echo $tplId?>,		
		suggestion: ["Chia se cho ban be", "Chia sẻ cho bạn bè", "Mời bạn bè"],
		state:<?php echo "123456"?>,
		callback : "fCallback"
	});
}
</script>

</body>
</html>