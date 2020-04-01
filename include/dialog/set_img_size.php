<?php
require("../common.inc.php");
if($cfg_html_editor != 'kindeditor')exit();
session_start();
if($_SESSION['dede_admin_type']<8)exit('没有权限');
if(isset($_POST['width']) && isset($_POST['height']))
{
	$width = intval($_POST['width']);
	$height = intval($_POST['height']);
	if(!file_put_contents('img_size_max.php','<?php $img_width_max='.$width.';$img_height_max='.$height.';'))exit('保存出错没有权限');
}
include('img_size_max.php');
?>
<!DOCTYPE Html>
<html>
<meta charset="utf-8">
<style>
body{padding:0;margin:0;font-size:12px;text-align:center}
input{height:16px;width:40px}
</style>
<body>
<form action="" method="post">
图片宽度(0为不压缩):<input value="<?php echo $img_width_max ?>" name="width">
高度:<input value="<?php echo $img_height_max ?>" name="height">
 <button type="submit">确定</button>
</form>
</body>
</html>