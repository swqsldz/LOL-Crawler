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
    private $handle;

    private $html;

    public function __construct()
    {
        $this->handle = curl_init();
        $this->html = new simple_html_dom();
    }


    public function search($name, $serverName = '')
    {
        $searchUrl = "http://www.laoyuegou.com/enter/search/search.html?type=lol&name=" . urlencode($name);

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->handle, CURLOPT_URL, $searchUrl);
        $page = curl_exec($this->handle);

        $this->html->load($page);
        $count = trim($this->html->find('.search-result-box', 0)->children(0)->children(1)->innertext);

        $doms = $this->html->find('.search-result-list', 0)->children(0);
        $this->html->load($doms->innertext);
        $servers = [];
        while ($count--) {
            $server = $this->html->find('p', $count - 1);
            $url = $this->html->find('a', $count - 1);
            $servers[explode(' - ', $server->innertext)[0]] = $url->href;
        }
        return $serverName == '' ? $servers : $servers[$serverName];
    }

    public function baseInfo($name, $serverName)
    {
        $userInfo = [];
        $keys = ['场次', '胜率'];
        $url = $this->search($name, $serverName);

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_exec($this->handle);

        $info = curl_getinfo($this->handle);
        $url = $info['redirect_url'];

        curl_setopt($this->handle, CURLOPT_URL, $url);
        $page = curl_exec($this->handle);

        $this->html->load($page);

        $userInfo['rank'] = $this->html->find('.info-attention span', 0)->plaintext;
        $s5 = preg_split('/\s+/', trim($this->html->find('.location-list', 0)->children(0)->children(1)->children(1)->plaintext));
        array_pop($s5);
        $userInfo['complex']['S5排位赛'] = array_combine($keys, $s5);
        $userInfo['complex']['S4排位赛']['场次'] = $this->html->find('.various-tips', 0)->plaintext;
        $userInfo['complex']['S4排位赛']['胜率'] = $this->html->find('.various-tips', 1)->plaintext;
        $userInfo['complex']['匹配赛']['场次'] = $this->html->find('.various-tips', 2)->plaintext;
        $userInfo['complex']['匹配赛']['胜率'] = $this->html->find('.various-tips', 3)->plaintext;
        $userInfo['complex']['大乱斗']['场次'] = $this->html->find('.various-tips', 4)->plaintext;
        $userInfo['complex']['大乱斗']['胜率'] = $this->html->find('.various-tips', 5)->plaintext;

        return $userInfo;
    }

    public function battles($playerName, $serverName)
    {
        
    }
}