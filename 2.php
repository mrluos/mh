<?php
include_once './common.php';
$id = $_GET['id'] ?? null;
$cid = $_GET['cid'] ?? null;
$getpage = $_GET['getpage'] ?? null;


$db = new MH();
$html = [];
if ($id) {
//	$list = $db->getAll('select * from mh_zj where manhua_id =? order  by sort asc', [$id]);

	$list = getAllZJEx($id);
//	print_r($list);
//	exit();
//	$list = [$list[0]];
	$ke = 0;
	foreach ($list as $k1 => $val) {
		if (!$cid) {
			$html[] = '<a class="nav2 title" href="2.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">' . $val['title'] . '</a>';
		}
		if ($cid != $val['id']) {
			continue;
		}
		$ke = $k1;
		$html[] = '<a class="nav2" href="2.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">' . $val['title'] . '</a>';;
		$l = array_fill(0, 10, 1);
		$suffix = $val['image_suffix'];
		foreach ($l as $k => $v) {
			$imgUrlBase = 'http://www.xiximh.vip/' . $val['dir_str'] . '@#@' . $suffix;
			$imgUrl = 'http://www.xiximh.vip/' . $val['dir_str'] . $k . $suffix;
			$html[] = '<img data-base="' . $imgUrlBase . '" class="catalog" src="' . $imgUrl . '"/>';
		}

	}
	if ($cid) {
		if ($getpage) {
			$c = getPicCount($list[$ke], 10);
			echo json_encode($c);
			exit;
		}
		$val = isset($list[$ke - 1]) ? $list[$ke - 1] : null;
		$nextVal = isset($list[$ke + 1]) ? $list[$ke + 1] : null;
		$append = [];
		$append[] = '<a class="nav2" href="2.php">首页</a>';
		if ($cid) {
			$append[] = '<a class="nav2" href="2.php?id=' . $id . '">返回</a>';
		} else {
			$append[] = '<a class="nav2" href="2.php?id=' . $id . '">返回</a>';
		}

		if ($val) {
			$append[] = '<a class="nav2 prev" href="2.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">上一页</a>';
		}
		if ($nextVal) {
			$append[] = '<a class="nav2 next" href="2.php?id=' . $nextVal['manhua_id'] . '&cid=' . $nextVal['id'] . '">下一页</a>';
		}
		$html = array_merge($append, $html);
	} else {
		$val = isset($list[$ke - 1]) ? $list[$ke - 1] : null;
		$append = [];
		$append[] = '<a class="nav2" href="2.php">首页</a>';
		$html = array_merge($append, $html);
	}
} else {
	$list = $db->getAllMH();
	$html = [];
	foreach ($list as $ke => $val) {
		$html[] = '<a class="catalog" href="2.php?id=' . $val['id'] . '">' . $val['title'] . '</a>';
	}
}

$html = implode('', $html);
echo <<<EOF
<html>
<head>
	<script src="http://lib.sinaapp.com/js/jquery/1.7.2/jquery.min.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style>
	.btn-more{
position: fixed;
    bottom: 0;
    width: 100px;
    padding: 10px;
    margin-top: 20px;
    left: 50%;
    margin-left: -100px;
	}
		img{
		width: 100%;
		height: auto;
			display: block;
			margin: 0 auto;
		}
		.catalog{
		display: block;
		}.nav2{
		width: 100%;
		height: auto;
			display: inline;
			margin: 0 auto;
			padding: 5px;
		}
		.nav2.title{
			display: block;
		}
		.nav2.next{
		padding: 20px;
position: fixed;
    /* top: 50%; */
    right: 0;
    width: 100px;
    bottom: 0;
    z-index: 99;
		}
		.nav2.prev{
		padding: 20px;
		position: fixed;
    /* top: 50%; */
    left: 0;
    width: 100px;
    bottom: 0;
    z-index: 99;
			
		}
		#body{
		margin-bottom: 100px;
		}
	</style>
</head>

<body>
<div id="body">
{$html}
</div>
<button class="btn-more">更多</button>
<script>
$(function() {
  var count = 10;

  var url=$("img").data('base');
  $("button").on("click",function() {
        var _img =[];
      count +=10;
    for (var i = count-10;i<count;i++){
        var _url = url.replace('@#@',i);
        _img.push('<img src="'+_url+'"/>')
    }
          _img=_img.join('');
        $("#body").append(_img);
  })
  
})
</script>
</body>
</html>
EOF;
