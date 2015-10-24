<?php namespace Crawler;
require_once 'simple_html_dom.php';

use simple_html_dom;

/**
 * Class UserCrawler.php
 * @package     Crawler
 * @version     1.0.0
 * @copyright   Copyright (c) 2015 forehalo <http://www.forehalo.top>
 * @author      forehalo <forehalo@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html   LGPL
 */
class UserCrawler
{

    public function search($name, $serverName = ''){
        $searchUrl = "http://www.laoyuegou.com/enter/search/search.html?type=lol&name=" . urlencode($name);

        $handle = curl_init($searchUrl);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        $page = curl_exec($handle);

        $html = new simple_html_dom();
        $html->load($page);
        $count = trim($html->find('.search-result-box', 0)->children(0)->children(1)->innertext);

        $doms = $html->find('.search-result-list', 0)->children(0);
        $html->load($doms->innertext);
        $servers = [];
        while($count--){
            $server = $html->find('p', $count - 1);
            $url = $html->find('a', $count - 1);
            $servers[explode(' - ', $server->innertext)[0]] = $url->href;
        }
        return $serverName == '' ? $servers : $servers[$serverName];
    }
}