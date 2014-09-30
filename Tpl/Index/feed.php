<form action="api.php?mod=user" method="post" name="feedForm" id="feedForm">
<input type="hidden" name="fType" value="<?php echo $content; ?>">
<div id="feed">
<div class="ficon" align="center"></img><img src="<?php echo $conf['imgdir'].$data['icons'][1]?>" align="middle" ></img></div>
<div class="fsubject" align="justify"><?php echo $data['userMsg']?></div>
</div>
</form>