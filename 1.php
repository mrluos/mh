<?php
include_once './common.php';
$id = $_GET['id'] ?? null;

$db = new MH();
$html = [];
if ($id) {
	$list = $db->getAll('select * from mh_zj', []);
	foreach ($list as $ke => $val) {
		if ($val['pic_count'] > 0) {
			$l = array_fill(0, $val['pic_count'], 1);
			$suffix = $val['image_suffix'];
			foreach ($l as $k => $v) {
				$imgUrl = 'http://www.xiximh.vip/' . $val['dir_str'] . $k . $suffix;
				$html[] = '<img class="catalog" src="' . $imgUrl . '"/>';
			}
		}
	}
} else {
	$list = $db->getAllMH();
	$html = [];
	foreach ($list as $ke => $val) {
		$html[] = '<a class="catalog" href="1.php?id=' . $val['id'] . '">' . $val['id'] . '</a>';
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
		}
	</style>
</head>

<body>
{$html}
</body>
</html>
EOF;
