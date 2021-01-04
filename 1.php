<?php
include_once './common.php';
$id = $_GET['id'] ?? null;
$cid = $_GET['cid'] ?? null;

$db = new MH();
$html = [];
if ($id) {
//	$list = $db->getAll('select * from mh_zj where manhua_id =? order  by sort asc', [$id]);

	$list = $db->getAll('select * from mh_zj where manhua_id =? order  by sort asc', [$id]);

//	$list = [$list[0]];
	$ke = 0;
	foreach ($list as $k1 => $val) {
		if (!$cid) {
			$html[] = '<a class="nav2 title" href="1.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">' . $val['title'] . '</a>';
		}
		if ($cid != $val['id']) {
			continue;
		}
		$ke = $k1;
		$html[] = '<a class="nav2" href="1.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">' . $val['title'] . '</a>';
		if ($val['pic_count'] > 0) {

			$l = array_fill(0, $val['pic_count'], 1);
			$suffix = $val['image_suffix'];
			foreach ($l as $k => $v) {
				$imgUrl = 'http://www.xiximh.vip/' . $val['dir_str'] . $k . $suffix;
				$html[] = '<img class="catalog" src="' . $imgUrl . '"/>';
			}
		} else {
			$html[] = '<p>暂时没有数据...</p>';
		}
	}
	if ($cid) {
		$val = isset($list[$ke - 1]) ? $list[$ke - 1] : null;
		$nextVal = isset($list[$ke + 1]) ? $list[$ke + 1] : null;
		$append = [];
		$append[] = '<a class="nav2" href="1.php">首页</a>';
		if ($cid) {
			$append[] = '<a class="nav2" href="1.php?id=' . $id . '">返回</a>';
		} else {
			$append[] = '<a class="nav2" href="1.php?id=' . $id . '">返回</a>';
		}

		if ($val) {
			$append[] = '<a class="nav2" href="1.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">上一页</a>';
		}
		if ($nextVal) {
			$append[] = '<a class="nav2" href="1.php?id=' . $nextVal['manhua_id'] . '&cid=' . $nextVal['id'] . '">下一页</a>';
		}
		$html = array_merge($append, $html);
	} else {
		$val = isset($list[$ke - 1]) ? $list[$ke - 1] : null;
		$append = [];
		$append[] = '<a class="nav2" href="1.php">首页</a>';
		$html = array_merge($append, $html);
	}
} else {
	$list = $db->getAllMH();
	$html = [];
	foreach ($list as $ke => $val) {
		$html[] = '<a class="catalog" href="1.php?id=' . $val['id'] . '">' . $val['title'] . '</a>';
	}
}

$html = implode('', $html);
echo <<<EOF
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style>
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
	</style>
</head>

<body>
{$html}
</body>
</html>
EOF;
