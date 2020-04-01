<?php
/*自定义函数*/
function IndexCurrent($typeid='',$current=''){
if (!$typeid) return $current;
}
function GetSonIs($topid,$class){
global $dsql;
$row = $dsql->GetOne("SELECT reid FROM `#@__arctype` WHERE topid= $topid");
if($row['reid']) return 'class="'.$class.'"';
else return 'style="display:none"';
}
function GetSonIf($topid,$str){
global $dsql;
$row = $dsql->GetOne("SELECT reid FROM `#@__arctype` WHERE topid= $topid");
if($row['reid']) return $str;
}
function GetTopTypename($id){
global $dsql;
$row = $dsql->GetOne("SELECT typename,topid FROM `#@__arctype` WHERE id= $id");
if($row['topid'] !== '0')$row = $dsql->GetOne("SELECT typename FROM `#@__arctype` WHERE id= $row[topid]");
return $row['typename'];
}
function GetTopenTypename($id){
global $dsql;
$row = $dsql->GetOne("SELECT entypename,topid FROM `#@__arctype` WHERE id= $id");
if($row['topid'] !== '0')$row = $dsql->GetOne("SELECT entypename FROM `#@__arctype` WHERE id= $row[topid]");
return $row['entypename'];
}
function GetTopTypebanner($id){
global $dsql;
$row = $dsql->GetOne("SELECT typebanner,topid FROM `#@__arctype` WHERE id= $id");
if($row['topid'] !== '0')$row = $dsql->GetOne("SELECT typebanner FROM `#@__arctype` WHERE id= $row[topid]");
if($row['typebanner'])return $row['typebanner'];
else return '/skin/img/banner.jpg';
}
function GetTopTypeurl($id){
global $dsql, $cfg_cmspath;
$row = $dsql->GetOne("SELECT typedir,defaultname,topid FROM `#@__arctype` WHERE id= $id");
if($row['topid'] !== '0')$row = $dsql->GetOne("SELECT typedir FROM `#@__arctype` WHERE id= $row[topid]");
if($row['defaultname'] == 'index.html')$row['defaultname'] = '';
return str_replace('{cmspath}',$cfg_cmspath,$row['typedir']) . '/' . $row['defaultname'];
}
function dede97_keywords($words=''){
	global $cfg_keywords;
	if($words=='')$words = $cfg_keywords;
	return htmlspecialchars($words);
}
function dede97_description($words=''){
	global $cfg_description;
	if($words=='')$words = $cfg_description;
	return htmlspecialchars($words);
}

function litimgurls($imgid=0)
{
    global $lit_imglist,$dsql;
    //获取附加表
    $row = $dsql->GetOne("SELECT c.addtable FROM #@__archives AS a LEFT JOIN #@__channeltype AS c 
                                                            ON a.channel=c.id where a.id='$imgid'");
    $addtable = trim($row['addtable']);
    
    //获取图片附加表imgurls字段内容进行处理
    $row = $dsql->GetOne("Select imgurls From `$addtable` where aid='$imgid'");
    
    //调用inc_channel_unit.php中ChannelUnit类
    $ChannelUnit = new ChannelUnit(2,$imgid);
    
    //调用ChannelUnit类中GetlitImgLinks方法处理缩略图
    $lit_imglist = $ChannelUnit->GetlitImgLinks($row['imgurls']);
    
    //返回结果
    return $lit_imglist;
}
/*字符过滤函数*/
function wwwcms_filter($str,$stype="inject") {
	if ($stype=="inject")  {
		$str = str_replace(
		       array( "select", "insert", "update", "delete", "alter", "cas", "union", "into", "load_file", "outfile", "create", "join", "where", "like", "drop", "modify", "rename", "'", "/*", "*", "../", "./"),
			   array("","","","","","","","","","","","","","","","","","","","","",""),
			   $str);
	} else if ($stype=="xss") {
		$farr = array("/\s+/" ,
		              "/<(\/?)(script|META|STYLE|HTML|HEAD|BODY|STYLE |i?frame|b|strong|style|html|img|P|o:p|iframe|u|em|strike|BR|div|a|TABLE|TBODY|object|tr|td|st1:chsdate|FONT|span|MARQUEE|body|title|\r\n|link|meta|\?|\%)([^>]*?)>/isU", 
					  "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
					  );
		$tarr = array(" ",
		              "",
					  "\\1\\2",
					  ); 
		$str = preg_replace($farr, $tarr, $str);
		$str = str_replace(
		       array( "<", ">", "'", "\"", ";", "/*", "*", "../", "./"),
			   array("&lt;","&gt;","","","","","","",""),
			   $str);
	}
	return $str;
}

/**
 *  载入自定义表单(用于发布)
 *
 * @access    public
 * @param     string  $fieldset  字段列表
 * @param     string  $loadtype  载入类型
 * @return    string
 */
 
function AddFilter($channelid, $type=1, $fieldsnamef, $defaulttid, $loadtype='autofield')
{
	global $tid,$dsql,$id;
	$tid = $defaulttid ? $defaulttid : $tid;
	if ($id!="")
	{
		$tidsq = $dsql->GetOne(" Select typeid From `#@__archives` where id='$id' ");
		$tid = $tidsq["typeid"];
	}
	$nofilter = (isset($_REQUEST['TotalResult']) ? "&TotalResult=".$_REQUEST['TotalResult'] : '').(isset($_REQUEST['PageNo']) ? "&PageNo=".$_REQUEST['PageNo'] : '');
	$filterarr = wwwcms_filter(stripos($_SERVER['REQUEST_URI'], "list.php?tid=") ? str_replace($nofilter, '', $_SERVER['REQUEST_URI']) : $GLOBALS['cfg_cmsurl']."/plus/list.php?tid=".$tid);
    $cInfos = $dsql->GetOne(" Select * From  `#@__channeltype` where id='$channelid' ");
	$fieldset=$cInfos['fieldset'];
	$dtp = new DedeTagParse();
    $dtp->SetNameSpace('field','<','>');
    $dtp->LoadSource($fieldset);
    $dede_addonfields = '';
    if(is_array($dtp->CTags))
    {
        foreach($dtp->CTags as $tida=>$ctag)
        {
            $fieldsname = $fieldsnamef ? explode(",", $fieldsnamef) : explode(",", $ctag->GetName());
			if(($loadtype!='autofield' || ($loadtype=='autofield' && $ctag->GetAtt('autofield')==1)) && in_array($ctag->GetName(), $fieldsname) )
            {
                $href1 = explode($ctag->GetName().'=', $filterarr);
				$href2 = explode('&', $href1[1]);
				$fields_value = $href2[0];
				$dede_addonfields .='<div class="param-item">';
				$dede_addonfields .= '<label>'.$ctag->GetAtt('itemname').'：</label>';
				switch ($type) {
					case 1:
						$dede_addonfields .= (preg_match("/&".$ctag->GetName()."=/is",$filterarr,$regm) ? '<a title="不限" class="" href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'">不限</a>' : '<a title="不限" class="" href="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'">不限</a>').'';
					
						$addonfields_items = explode(",",$ctag->GetAtt('default'));
						for ($i=0; $i<count($addonfields_items); $i++)
						{
							$href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);//echo $href;
							$dede_addonfields .= ($fields_value!=urlencode($addonfields_items[$i]) ? '<a title="'.$addonfields_items[$i].'" href="'.$href.'">'.$addonfields_items[$i].'</a>' : '<a title="'.$addonfields_items[$i].'" class="active" href="'.$href.'">'.$addonfields_items[$i].'</a>')."";
						}
					
						$dede_addonfields .='</div>';
					break;
					
					case 2:
						$dede_addonfields .= '<select name="filter"'.$ctag->GetName().' onchange="window.location=this.options[this.selectedIndex].value">
							'.'<option value="'.str_replace("&".$ctag->GetName()."=".$fields_value,"",$filterarr).'">不限</option>';
						$addonfields_items = explode(",",$ctag->GetAtt('default'));
						for ($i=0; $i<count($addonfields_items); $i++)
						{
							$href = stripos($filterarr,$ctag->GetName().'=') ? str_replace("=".$fields_value,"=".urlencode($addonfields_items[$i]),$filterarr) : $filterarr.'&'.$ctag->GetName().'='.urlencode($addonfields_items[$i]);
							$dede_addonfields .= '<option value="'.$href.'"'.($fields_value==urlencode($addonfields_items[$i]) ? ' selected="selected"' : '').'>'.$addonfields_items[$i].'</option>
							';
						}
						$dede_addonfields .= '</select><br/>
						';
					break;
				}
            }
        }
    }
	echo $dede_addonfields;
}

function Getfirstimg($aid){ 
global $cfg_basedir;
global $dsql;
$imglist = '';
$row = $dsql -> getone("Select imgurls From`dede_addonimages` where aid='$aid'"); // 
$imgurls = $row['imgurls']; 
preg_match_all("/{dede:img (.*)}(.*){\/dede:img/isU", $imgurls, $wordcount);
$count = count($wordcount[2]); 
if ($num > $count || $num == 0){ 
$num = $count; 
} 
for($i = 0;$i < $num;$i++){ 
$imglist .= "" . trim($wordcount[2][$i]) . ""; 
} 
$firstimg = substr($imglist,0,stripos($imglist,'.jpg'));
if($firstimg !=='')return $firstimg . '.jpg';
else{
	$firstimg = substr($imglist,0,stripos($imglist,'.png'));
	return $firstimg . '.png';
}
}