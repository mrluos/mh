<?php
include_once './common.php';
$id = $_GET['id'] ?? null;
$cid = $_GET['cid'] ?? null;
$method = $_GET['method'] ?? null;

function makeAllMh()
{

	$db = new MH();
	$list = $db->getAllMH();
	return $list;
}

function getZj($id)
{

	$db = new MH();
	$list = $db->getAll('select * from mh_zj where manhua_id =? order  by sort*1 asc', [$id]);
	return $list;
}

function getZjInfo($id, $cid)
{
	$getdata = 1;
	//	$list = $db->getAll('select * from mh_zj where manhua_id =? order  by sort asc', [$id]);
	$db = new MH();
	$list = $db->getAll('select * from mh_zj where manhua_id =? and id =? order  by sort*1 asc', [$id, $cid]);
//	print_r($list);
//	exit;
	if (empty($list)) {
		return jsonError('没有找到这个章节');
	}
	$info = $list[0];
	$dirPath = 'img/';
	$locPath = $dirPath . $id . '/' . $cid;
	$ids = [];
	$hasLoc = false;
	$sonIds = [];
	if (file_exists($dirPath . $id . '/')) {
		$dirList = scandir($dirPath . $id . '/');
		foreach ($dirList as $item) {
			if (is_numeric($item) && $item == $cid) {
				$hasLoc = true;
			}
		}
	}
	$info['img_loc'] = $hasLoc;


	$num = $info['pic_count'];
	if ($info['pic_count'] <= 0) {
		$num = 30;
	}
	$suffix = $info['image_suffix'];
	if ($info['image_suffix_check'] != 1) {
		$suffix = testForImgSuffix($suffix, $info['dir_str'], '2', false);
		$db->update('mh_zj', [
			'image_suffix' => $suffix,
			'image_suffix_check' => 1,
		], ['id' => $info['id']]);
	}
	$imgBase = 'http://www.xiximh.vip/' . $info['dir_str'];
	if ($hasLoc) {
		$num = getFileNumber($locPath);
		if ($num > 0) {
			$suffix = '.jpg';
			$imgBase = $locPath . '/';
		}
	}

	$imgUrlBase = $imgBase . '@#@' . $suffix;
	$info['imgUrlBase'] = $imgUrlBase;
	$l = array_fill(0, $num, 1);
	$imgs = [];
	foreach ($l as $k => $v) {
		$imgUrl = $imgBase . $k . $suffix;
		$imgs[] = $imgUrl;
	}
	$nextId = '';
	$nextInfo = $db->getAll('select * from mh_zj where manhua_id =? and sort = ? ', [$id, intval($info['sort']) + 1]);
	if (!empty($nextInfo)) {
		$nextInfo = $nextInfo[0];
		$nextId = "id=$id&cid=" . $nextInfo['id'];
	}
	$prevId = '';
	$prevInfo = $db->getAll('select * from mh_zj where manhua_id =?  and sort = ? ', [$id, intval($info['sort']) - 1]);
	if (!empty($prevInfo)) {
		$prevInfo=$prevInfo[0];
		$prevId = "id=$id&cid=" . $prevInfo['id'];
	}
	return [
		'item' => $info,
		'img' => $imgs,
		'next' => $nextId,
		'prev' => $prevId
	];

}

if ($method !== null) {
	switch ($method) {
		case 'init':
			jsonSuccess(makeAllMh());
			break;
		case 'zj':
			jsonSuccess(getZj($id));
			break;
		case 'zjinfo':
			jsonSuccess(getZjInfo($id, $cid));
			break;
	}
}



