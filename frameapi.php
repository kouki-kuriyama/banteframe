<?php
/*
  番長フレーム合成API(ばんてふれーむ) for PHP

	■動作には以下の環境が必要です
	　PHP 5.2.1 以降
	　GD Graphics Library

	・フレーム素材は /materials/ の中に保存してください。(透過PNG形式)
	・フレーム素材は全て 740x555 に統一して保存してください。
	・元になる背景素材は /source/ に保存してください。
	・全てリクエストはGETで行います。

	■フレームの指定
	　引数 frame で指定します。
	　/materials/に保存されているフレームのファイル名を取ります。(拡張子不要)

	　(例)frame1.pngをフレームに使用する
	　　frameapi.php?frame=frame1

	■背景素材の指定
	　引数 source で指定します。
	　/source/に保存されているフレームのファイル名を取ります。(拡張子必要)

	　(例)back1.jpgを背景に使用する
	　　frameapi.php?frame=frame1&source=back1.jpg

	■フレームサイズの指定
	　引数 framesize で指定します。(小数値指定可)
	　1で等倍、2で1/2倍、3で1/3倍・・・となります。
	　指定しない場合は等倍サイズになります。

	　(例)フレーム1/2倍で合成する
	　　frameapi.php?frame=frame1&source=back1.jpg&framesize=1

*/

$source_background = $_GET["source"];
$frame_material = $_GET["frame"];
$framesize = $_GET["framesize"];

if( !$framesize ){ $framesize = 1; }

//FrameMaterial,BackgroundSource path(Default)
$source_background_path = "./source/{$source_background}";
$frame_path = "./materials/{$frame_material}.png";

//Get BackgroundSource infomation
$sourceinfo = getimagesize($source_background_path);

//Import BackgroundSource
switch( $sourceinfo[2] ){
	case 1:
		$photo_source = imageCreateFromGif($source_background_path);
	break;
	case 2:
		$photo_source = imageCreateFromJpeg($source_background_path);
	break;
	case 3:
		$photo_source = imageCreateFromPng($source_background_path);
	break;
	default:
		return false;
	break;
}

//Import FrameMaterial
$framebase = imageCreateFromPng($frame_path);

//BackgroundSource Resize
$max_width = 740;
$max_height = 555;
$width = $sourceinfo[0];
$height = $sourceinfo[1];
 
if( $width > $max_width) {
	$height *= $max_width / $width;
	$width = $max_width;
}

if( $height > $max_height) {
	$width *= $max_height / $height;
	$height = $max_height;
}

//Frame PositionX( Expand Framesize )
if( $sourceinfo[0] < $max_width ){
	$tc_x = $max_width - ($max_width - $width);
	$position_x = $tc_x - ($max_width/$framesize);
}else{
	$tc_x = $max_width;
	if( $framesize == 1 ){
		$position_x = 0;
	}else{
		$position_x = $tc_x - ($max_width/$framesize);
	}
}

//Frame PositionY( Expand Framesize )
if( $height[1] < $max_height ){
	$tc_y = $max_height - ($max_height - $height);
	$position_y = $tc_y - ($max_height/$framesize);
}else{
	$tc_y = $max_height;
	if( $framesize == 1 ){
		$position_y = 0;
	}else{
		$position_y = $tc_y - ($max_height/$framesize);
	}
}

//Make Canvas( [$tc_x] x [$tc_y] )
$background = imageCreateTrueColor($tc_x,$tc_y);
imageAlphaBlending($background,false);
imageSaveAlpha($background,true);
$transparent = imageColorAllocateAlpha($background, 0xFF, 0x00, 0xFF, 127);
imageFill($background, 0, 0, $transparent);

//Add SourceBackground
imageCopyResampled($background,$photo_source,0,$sourceY,0,0,$width,$height,$sourceinfo[0],$sourceinfo[1]);
imageAlphaBlending($background, true);

//Add Frame
imagecopyresized($background,$framebase,$position_x,$position_y,0,0,740/$framesize,555/$framesize,740,555);

//Output
header("content-type: image/png");
imagePng($background);
ImageDestroy($background);
ImageDestroy($photo_source);
ImageDestroy($framebase);

?>
