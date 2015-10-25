<?php
require_once 'UserCrawler.php';

use Crawler\UserCrawler;

if($argc <= 2){
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, 'arguments error');
    exit;
}
$crawler = new UserCrawler();

$serverName = iconv('GBK', 'utf-8', trim($argv[1]));
$playerName = iconv('GBK', 'utf-8', trim($argv[2]));

var_dump($crawler->baseInfo($playerName, $serverName));