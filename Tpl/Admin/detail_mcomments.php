<?php
include TPL_DIR . "/head.php";
function format_comment($comment, $maxLineSize=20, $maxCommentSize=200) {
		try {
			$result = '';
			if(strlen($comment) > $maxCommentSize) $comment = substr($comment, 0, $maxCommentSize);
			$length = strlen($comment);
			$lineCount = ceil($length / $maxLineSize);
			for($i = 0; $i < $lineCount; $i++) {
				$lineContent = substr($comment, $i*$maxLineSize, $maxLineSize);
				if($i > 0) {
					$result.= '<br />';
				}
				$result.= $lineContent;
			}
			return $result;
		} catch(Exception $e) {
			return $comment;
		}
	}
?>
<div id="middle">
    <div id="left-column">
      <h3>Góp ý</h3>
      <ul class="nav">
        <li><a href="?mod=Index&act=mComments">Danh sách góp ý</a></li>
      </ul>
</div>
      
    <div id="center-column">
      <div class="top-bar"> <a href="mailto:<?php  echo $comment['mail'];?>?Subject=<?php  if(isset($comment['title'])) echo "RE: ".$comment['title'];?>" class="button">Trả lời </a>
        <h1>Quản lý góp ý</h1>
        <div class="breadcrumbs"><a href="<?php echo $this->url ;?>">Trang chủ</a> / <a href="?mod=Index&act=mComments">Quản lý góp ý</a></div>
      </div>
      <br />
      <div class="select-bar" align="left">
       Tiêu đề: <b><?php  if(isset($comment['title'])) echo format_comment($comment['title']);?></b>
      </div>
      <div class="table" style="width:736px"> <img src="<?php echo $this->config['imgdir_admin'] ?>bg-th-left.gif" width="8" height="7" alt="" class="left" /> <img src="<?php echo $this->config['imgdir_admin'] ?>bg-th-right.gif" width="7" height="7" alt="" class="right" />
        <table class="listing" cellpadding="0" cellspacing="0" style="width:736px">
          <tr>
            <th class="first" width="7">&nbsp;</th>
            <th> Nội dung </th>
            <th class="last" width="7"></th>
          </tr>
          <tr>
            <td class="first style1">&nbsp;</td>
            <td align="left">
            <?php echo $comment['content'];?>
            </td>
            <td class="last">&nbsp;</td>
          </tr>
        </table>

      </div>
    </div>
  </div>
<?php
include TPL_DIR . "/foot.php";
?>