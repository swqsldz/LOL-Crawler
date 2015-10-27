<?php
//调用格式  php battle_info_crawler.php 大区名 战斗编号

require_once '../Crawler/UserCrawler.php';

use Crawler\UserCrawler;

if($argc <= 2){
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, 'arguments error');
    exit;
}
$crawler = new UserCrawler();


$serverName = iconv('GBK', 'utf-8', trim($argv[1]));
$battleId = iconv('GBK', 'utf-8', trim($argv[2]));

print_r($crawler->battleInfo($serverName, $battleId));