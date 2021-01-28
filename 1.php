<?php
include_once './common.php';
$id = $_GET['id'] ?? null;
$cid = $_GET['cid'] ?? null;
$getdata = $_GET['getdata'] ?? null;

$db = new MH();
$html = [];
$gobackHtml = '';
$prevHtml = '';
$nextHtml = '';
$titleHtml = '';
$dirPath = 'img/';
$ids = [];
$getdataAry = [];
if (file_exists($dirPath)) {
	$dirList = scandir($dirPath);
	foreach ($dirList as $item) {
		if (is_numeric($item)) {
			$ids[$item] = $item;
		}
	}
}
if ($id) {
//	$list = $db->getAll('select * from mh_zj where manhua_id =? order  by sort asc', [$id]);

	$list = $db->getAll('select * from mh_zj where manhua_id =? order  by sort*1 asc', [$id]);

//	$list = [$list[0]];
	$ke = 0;
	$sonIds = [];
	if (file_exists($dirPath . $id . '/')) {
		$dirList = scandir($dirPath . $id . '/');
		foreach ($dirList as $item) {
			if (is_numeric($item)) {
				$sonIds[$item] = $item;
			}
		}
	}
//	$dirAry=getFileName($dirPath  );
	$getdataAry['catalog'] = [];
	foreach ($list as $k1 => $val) {
		$addCls = 'false';
		if (isset($sonIds[$val['id']])) {
			$addCls = 'true';
		}
		if (!$cid) {
			$html[] = '<a id="catalog-' . $val['id'] . '" class="catalog ' . $addCls . '" href="1.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">' . $val['title'] . '</a>';
		}

		if ($cid != $val['id'] && !$getdata) {
			continue;
		}

		$ke = $k1;
		$titleHtml = '<a class="foot-a" >' . $val['title'] . '</a>';
		//检查是否有本地目录
		$hasLoc = false;
		$locPath = $dirPath . $val['manhua_id'] . '/' . $val['id'];
		if (file_exists($locPath)) {
			$hasLoc = true;
		}
		$num = $val['pic_count'];
		if ($val['pic_count'] <= 0) {
			$num = 10;
		}
		$suffix = $val['image_suffix'];

		$imgBase = 'http://www.xiximh.vip/' . $val['dir_str'];
		if ($hasLoc) {
			$num = getFileNumber($locPath);
			if ($num > 0) {
				$suffix = '.jpg';
				$imgBase = $locPath . '/';
			} else {
				$suffix = '.jpg';
//				print_r($val);
				$num = 10;
			}
		}
		if ($getdata && !$hasLoc) {
			continue;
		}
		$imgUrlBase = $imgBase . '@#@' . $suffix;
		$l = array_fill(0, $num, 1);
		$imgs = [];
		foreach ($l as $k => $v) {
			$imgUrl = $imgBase . $k . $suffix;
			$imgs[] = $imgUrl;
			$html[] = '<img data-base="' . $imgUrlBase . '" class="img-wrap" src="' . $imgUrl . '"/>';
		}
		$getdataAry['catalog'][] = [
			'item' => $val,
			'img' => $imgs,
		];

	}
	if ($cid) {
		$val = isset($list[$ke - 1]) ? $list[$ke - 1] : null;
		$nextVal = isset($list[$ke + 1]) ? $list[$ke + 1] : null;
		$append = [];
		if ($cid) {
			$gobackHtml = '<a class="footer-a" href="1.php?id=' . $id . '">返回</a>';
		} else {
			$gobackHtml = '<a class="footer-a" href="1.php?id=' . $id . '">返回</a>';
		}

		if ($val) {
			$prevHtml = '<a class="footer-a" href="1.php?id=' . $val['manhua_id'] . '&cid=' . $val['id'] . '">上一页</a>';
		}
		if ($nextVal) {
			$nextHtml = '<a class="footer-a" href="1.php?id=' . $nextVal['manhua_id'] . '&cid=' . $nextVal['id'] . '">下一页</a>';
		}
		$html = array_merge($append, $html);
	} else {
		$val = isset($list[$ke - 1]) ? $list[$ke - 1] : null;
		$append = [];
		$html = array_merge($append, $html);
	}
} else {
	$list = $db->getAllMH();
	$html = [];
	$append = [];
	foreach ($list as $ke => $val) {
		$loc = 'false';
		if (isset($ids[$val['id']])) {
			$loc = 'true';
		}
		$imgBase = 'http://www.xiximh.vip/' . $val['cover'];
		if ($loc == 'true') {
			$append[] = '<a class="catalog ' . $loc . '" href="1.php?id=' . $val['id'] . '"><img class="img-cover hide" src="' . $imgBase . '"/>[ ' . $val['id'] . ' ]' . $val['title'] . '</a>';
		} else {
			$html[] = '<a class="catalog ' . $loc . '" href="1.php?id=' . $val['id'] . '"><img class="img-cover hide" src="' . $imgBase . '"/>[ ' . $val['id'] . ' ]' . $val['title'] . '</a>';
		}

	}
	$html = array_merge($append, $html);
}

if ($getdata) {
//	$html = array_slice($html, 0, 200);
	ob_clean();;
	$rs = json_encode($getdataAry, 1);
	exit($rs);
}
$html = implode('', $html);

echo <<<EOF
<html>
<head>
	<script src="http://lib.sinaapp.com/js/jquery/1.7.2/jquery.min.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum=1.0,minimum=1.0,user-scalable=0" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<style>
		body{
			margin: 0;padding: 0;
			overflow-x: hidden;
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
			/*margin-bottom: 100px;*/
			padding: 70px 0px;
		}
		.catalog{
			display: block;
		    padding: 5px;
		    border-bottom: 1px solid #eaeaea;
		}
		.catalog.true{
			color: red;
		}
		.header{
			position: fixed;
		    padding: 15px;
		    background: #b12828;
		    width: 100%;
		    color: white;
		    top: 0;
		    display: flex;
		}
		.footer{
			bottom:0;
			position: fixed;
		    display: flex;
		    background: #b12828;
		    width: 100%;
		    color: white;
		}
		.prev,.next,.more,.go-back{
		    flex: 1;
		    padding: 15px 0;
		    text-align: center;
		}
		.footer-a{
		    width: 100%;
		    display: block;
		    text-align: center;
		    color: white;
		    text-decoration: underline;
		}
		.home{
			width: 50px;;
		}
		.header >.footer-a{
				text-align: left;
		}
		.go-top{
		    position: fixed;
		    right: 5px;
		    bottom: 83px;
		    width: 60px;
		    height: 60px;
		    background: #d4cabd;
		    line-height: 55px;
		    text-align: center;
		    border-radius: 50%;
		}
		.show{
		bottom: 150px;
		}
.img{
		bottom: 250px;
		}
		.hide{
		display: none;
		}
			div#catalog-wrap {
		position: fixed;
		bottom: -60%;
		background: #fdfdfd;
		/* width: 100%; */
		left: 0;
		height: 50%;
		padding: 20px;
		right: 0;
		overflow: hidden;
		border-top: 1px solid #ffffff;
		box-shadow: 1px -1px 5px 3px #d0d0d0;
		overflow-y: auto;
	}
	a.catalog-a-vr {
		display: block;
		padding: 5px;
		font-size: 14px;
		background: #b12828;
		margin: 6px 0;
		box-shadow: 1px 1px 2px #b12828;
		color: #fff;
	}
	span.close-wrap {
		width: 100%;
		display: block;
		text-align: right;
	}
	</style>
</head>
<body>
<div class="header hide">
<a href="1.php" class="footer-a home">首页</a>
{$titleHtml}
</div>
<div id="body">
{$html}
</div>
<div class="footer hide">
<div class="prev">{$prevHtml}</div>
<div class="more">
<a class="btn-more footer-a">更多</a>
</div>
<div class="go-back">{$gobackHtml}</div>
<div class="next">{$nextHtml}</div>
</div>
<div class="go-top img">img</div>
<div class="go-top top">Top</div>
<div class="go-top show">show</div>
<div id="catalog-wrap">
	<span class="close-wrap">X</span>
</div>
<img id="img-wrap" style="width: 1px;height: 1px;display: none">
<script>
$(function() {
    /**
 * Trigger a callback when the selected images are loaded:
 * @param {String} selector
 * @param {Function} callback
  */
var onImgLoad = function(selector, callback){
    $(selector).each(function(){
        if (this.complete || /*for IE 10-*/ $(this).height() > 0) {
            callback.apply(this);
        }
        else {
            $(this).on('load', function(){
                callback.apply(this);
            });
        }
    });
};
/**
 * Trigger a callback when 'this' image is loaded:
 * @param {Function} callback
 */
(function($){
    $.fn.imgLoad = function(callback) {
        return this.each(function() {
            if (callback) {
                if (this.complete || /*for IE 10-*/ $(this).height() > 0) {
                    callback.apply(this);
                }
                else {
                    $(this).on('load', function(){
                        callback.apply(this);
                    });
                }
            }
        });
    };
})(jQuery);
  var count = 10;
  
   var imglen = 0;
        var loadImg = 0;
        var curIndex = 0
        function getimg(_list) {
            if (_list.length == 0) return;
            imglen += _list.length * 1;
            $("#img-count").html(imglen);
            _list.map(res => {
                
                $("img").attr('src', res).on('imgLoad', function () {
                    loadImg++;
                    $("#img-load").html(loadImg);
                });
            })
        }
       
  var url=$("img").data('base');
  if(!url){
      $(".btn-more").remove();
  }
  $(".btn-more").on("click",function() {
	var _img =[];
	count +=10;
	for (var i = count-10;i<count;i++){
	var _url = url.replace('@#@',i);
	_img.push('<img src="'+_url+'"/>')
	}
	_img=_img.join('');
	$("#body").append(_img);
  })
  $(".go-top.top").on("click",function(){
      $("body").scrollTop(0);
  });
  $(".go-top.img").on("click",function(){
       if(show1){
            $(".img-cover").addClass('hide')
        }else{
            $(".img-cover").removeClass('hide')
        }
        show1 =!show1;
  });
  var show =false;  var show1 =true;
  $(".go-top.show").on("click",function() {
        if(show){
            $(".header").addClass('hide')
            $(".footer").addClass('hide')
        }else{
            $(".header").removeClass('hide')
            $(".footer").removeClass('hide')
        }
        show =!show;
  })
    $(".go-top.show").click();
	$(".go-top.img").click();  
})
</script>
</body>
</html>
EOF;