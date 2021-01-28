<?php
include_once "./common.php";


function dispatch(&$args)
{
	print_r($args);
	// script name
	array_shift($args);
	$first = '';
	if (!isset($args[0])) {
		$first = '';
	} else {
		$first = $args[0];
	}
	switch ($first) {
		case  '-1':
			getPicCount(1, $args[1]);
			break;
		case '1000':
			getImgToLoc($args[1], $args[2]);
			break;
		case '1001':
			getImgToLoc(null, null, $args[1], $args[2]);
			break;
		case '1002':
			getImgToLoc($args[1], $args[2], null, null, 'asc');
			break;
		case '2000':
			updateImgSuffix($args[1], $args[2]);
			break;
		case '2002':
			getAllZJEx($args[1]);
			break;
		case '3000':
			getMHByHZW();
			break;
		case '4000':
			getPage(1, 1);
			break;
		case '4001':
			getPage(1, 2);
			break;
		case '4002':
			getPage(1, 3);
			break;
		default:
			echo <<<HTML
'-1'  : getPicCount(1, args[1]);	
'1000': getImgToLoc(args[1], args[2]);指定漫画ID和章节ID下载图片
'1001': getImgToLoc(null, null, args[1], args[2]); 下载图片
'2000': updateImgSuffix(args[1], args[2]); 更新图片后缀
'2002': getAllZJEx(args[1]); 获取章节
'3000': getMHByHZW();
'4000':getPage(1, 1);
'4001':getPage(1, 2);
'4002':getPage(1, 3);
HTML;
			die('Invalid Request');
	}

}

function is_cli()
{
	return preg_match("/cli/i", php_sapi_name()) ? true : false;
}

is_cli() || die('Bad Request');

dispatch($argv);
//getAllZJMaxImgEX(14071);

//$db->add();
