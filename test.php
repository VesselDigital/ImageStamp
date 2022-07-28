<?php

use ImageStamp\Helpers\Stamper;
define("IMAGE_STAMP_PATH", "/Applications/MAMP/htdocs/WordPress/vesseldigital/wp-content/plugins/image-stamp");
include_once __DIR__ . "/helpers/Stamper.php";
$stamper = new Stamper;
$img = "/Applications/MAMP/htdocs/WordPress/vesseldigital/wp-content/uploads/2022/07/strawberries-ga0cdab306_640.jpg";

$imgGD = imagecreatefromjpeg($img);
if($imgGD === false) {
    die("Failed");
}
if($stamper->_text($imgGD, "Hello World", "center", 1, 45, imagesx($imgGD), imagesy($imgGD)) === false) {
    die("Failed Text");
}

header('Content-Type: image/jpeg');
imagejpeg($imgGD);
imagedestroy($imgGD);