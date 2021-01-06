<?php
include_once "./common.php";


function dispatch(&$args)
{
	print_r($args);
	// script name
	array_shift($args);
	switch ($args[0]) {
		case  '-1':
			getPicCount(1, $args[1]);
			break;
		case '1000':
			getImgToLoc($args[1], $args[2]);
			break;
		case '1001':
			getImgToLoc(null, null, $args[1], $args[2]);
			break;
		case '2000':
			updateImgSuffix($args[1], $args[2]);
			break;
		default:
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