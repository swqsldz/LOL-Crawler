<?php namespace Crawler;
require_once 'helper.php';
require_once 'cookie.php';

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

    private $qquin;

    private $kw;

    private $areaId;

    private $vlist;

    private $cookie;

    private $parsedCookie = [];

    const USER_FOUND = true;

    public function __construct()
    {
        $this->cookie = getCookie();
        $this->handle = curl_init();
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->handle, CURLOPT_COOKIE, $this->cookie);
        $this->parseCookie();
    }

    public function search($playerName, $serverName)
    {
        $url = 'http://api.pallas.tgp.qq.com/core/search_player_with_ob?&key=' . urldecode($playerName);
        $result = json_decode($this->get($url));
        foreach ($result->data as $item) {
            if ($item->area_id === getAreaId($serverName)) {
                $this->qquin = $item->qquin;
                $this->kw = urlencode($playerName);
                $this->areaId = getAreaId($serverName);
                $this->vlist = $this->areaId . ':' . $this->qquin;

                return self::USER_FOUND;
            }
        }
        return !self::USER_FOUND;
    }

    public function baseInfo($playerName, $serverName)
    {
        if (!$this->search($playerName, $serverName)) {
            return null;
        }

        $this->addCookie('kw', $this->kw)->addCookie('vlist', $this->vlist);
        $url = 'http://api.pallas.tgp.qq.com/core/get_user_hot_info?dtag=profile&area_id=' . $this->areaId . '&qquin=' . $this->qquin;

        $info = json_decode($this->get($url), true)['data'];

        return array_merge($info, $this->recentData($this->areaId));
    }

    public function battles($playerName, $serverName, $limit, $offset)
    {
        if (!$this->search($playerName, $serverName)) {
            return null;
        }
        $queryString = $this->getQueryString();
        $url = 'http://api.pallas.tgp.qq.com/core/tcall?p=[[3,{' . $queryString . ',"bt_num":"0","bt_list":[],"champion_id":0,"offset":' . $offset . ',"limit":' . $limit . '}]]';
        return json_decode($this->get($url), true)['data'];
    }

    public function battleInfo($serverName, $battleId)
    {
        $areaId = getAreaId($serverName);
        $url = 'http://api.pallas.tgp.qq.com/core/tcall?dtag=profile&p=[[4,{"area_id":"' . $areaId . '","game_id":"' . $battleId . '"}]]';

        return json_decode($this->get($url), true)['data'];
    }

    protected function get($url)
    {
        curl_setopt($this->handle, CURLOPT_URL, $url);
        return curl_exec($this->handle);
    }

    protected function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    protected function parseCookie()
    {
        foreach (explode(';', $this->cookie) as $item) {
            $kv = explode('=', trim($item));
            $this->parsedCookie[$kv[0]] = $kv[1];
        }
    }

    protected function generateCookie()
    {
        $cookie = '';
        foreach ($this->parsedCookie as $key => $value) {
            $cookie .= $key . '=' . $value . '; ';
        }

        $this->cookie = $cookie;
    }

    protected function addCookie($key, $value)
    {
        if (array_key_exists($key, $this->parsedCookie)) {
            $this->parsedCookie[$key] = $value;
        } else {
            array_merge($this->parsedCookie, [$key => $value]);
        }
        $this->generateCookie();
        return $this;
    }

    protected function getQueryString()
    {
        //"qquin":"U1776034765420074132","area_id":"19"
        return '"qquin":"' . $this->qquin . '","area_id":"' . $this->areaId . '"';
    }

    protected function recentData()
    {
        $queryString = $this->getQueryString();
        $p = '[[63,{"items":[{' . $queryString . '}]}],[50,{' . $queryString . '}],[36,{' . $queryString . '}],[35,{"champion_id":0,' . $queryString . '}]]';
        $url = 'http://api.pallas.tgp.qq.com/core/tcall?&p=' . $p . '&_cache_time=300';

        $data = json_decode($this->get($url), true);
        return array_merge($data['data'][0]['items'][0], $data['data'][1]);

    }
}