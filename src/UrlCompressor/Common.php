<?php

namespace UrlCompressor;

/**
 * @author Emrah TOY <code@emrahtoy.com>
 * @description Base common class
 *
 * Class Common
 * @package UrlCompressor
 */
class Common
{
    protected $dbConnection;

    /**
     * Cache Object
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $cache;
    /**
     * Result Object
     * @var Result
     */
    protected $result;
    protected $allowedChars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    protected $config = [
        'CheckUrl' => false,
        'ShortUrlTableName' => 'shortenedurls',
        'CustomShortUrlTableName' => 'customshortenedurls',
        'TrackTableName' => 'track',
        'DefaultLocation' => '',
        'Track'=>false
    ];
    protected $url;
    protected $shortCode;

    public function __construct($dbConnection, $config = [])
    {
        $this->result = new Result();
        $this->config = array_merge($this->config, $config);
        $this->dbConnection = $dbConnection;
        if (empty($this->dbConnection) || empty($this->config)) //TODO : you may like to fire an exception
            $this->result->error('Database connection can not established or you must fulfill config');
    }

    public function setCache(\Doctrine\Common\Cache\CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    public function getResult()
    {
        return $this->result->result();
    }

    protected function saveToCache()
    {
        $result = false;
        if (!empty($this->cache)) {
            $result = $this->cache->save($this->shortCode, $this->url);
            $this->result->add('cache', (($result) ? 'added or refreshed' : 'could not added'));
        } else {
            $this->result->add('cache', 'disabled');
        }

        return $result;
    }

    protected function fetchFromCache()
    {
        $result = false;
        if (!empty($this->cache)) {
            $control = $this->cache->fetch($this->shortCode);
            if ($control !== false) {
                $this->url = $control;
                $this->result->add('found_in', 'Cache');
                $this->result->add('cache', 'found');
                $this->result->add('custom_short_code', null);
                $result = true;
            } else {
                $this->result->add('cache', 'not found');
            }
        } else {
            $this->result->add('cache', 'disabled');
        }
        return $result;
    }

    /**
     * @param string $fieldName
     * @param string|integer $value
     * @return bool
     */
    protected function checkFromCustomDb($fieldName, $value)
    {
        $stmt = $this->dbConnection->prepare('SELECT short_code,long_url FROM ' . $this->config['CustomShortUrlTableName'] . ' WHERE ' . $fieldName . '=?');
        // check if the URL has already been shortened
        $stmt->execute(array($value));
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result !== false) {
            $this->url = $result['long_url'];
            $this->shortCode = $result['short_code'];
            $this->result->add('found_in', 'Database');
            $this->result->add('db', 'found');
            $this->result->add('custom_short_code', true);
            $result = true;
        } else {
            $this->result->add('db', 'not found');
        }
        return $result;
    }

    protected function checkFromDb($fieldName, $value, $checkCustom = false, $checkCache = false)
    {
        $controlCache = ($checkCache) ? $this->fetchFromCache() : false;
        if ($controlCache !== false)
            return true;

        $controlCustom = ($checkCustom) ? $this->checkFromCustomDb($fieldName, $value) : false;
        if ($controlCustom !== false)
            return true;

        $stmt = $this->dbConnection->prepare('SELECT short_code,long_url FROM ' . $this->config['ShortUrlTableName'] . ' WHERE ' . $fieldName . '=?');

        // check if the URL has already been shortened
        $stmt->execute(array($value));
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result !== false) {
            $this->url = $result['long_url'];
            $this->shortCode = $result['short_code'];
            $this->result->add('found_in', 'Database');
            $this->result->add('db', 'found');
            $this->result->add('custom_short_code', false);
            $result = true;
        } else {
            $this->result->add('db', 'not found');
        }
        return $result;
    }

    protected function urlCheck()
    {
        if ($this->config['CheckUrl']) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($ch);
            $response_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($response_status != '200') {
                return $this->result->error('URL could not be reached', ['response_status' => $response_status]);
            } else {
                $this->result->add('url_checked', true);
            }
        }
        return true;
    }

    public static function responseJson($data = [], $redirect = false)
    {
        $code = ($redirect) ? 301 : ((!isset($data['error']) || empty($data['error'])) ? 200 : 404);

        http_response_code($code);
        $data['redirect']=$redirect;
        if ($redirect) {
            header('Content-Type: application/json');
            if (!isset($data['long_url']) || empty($data['long_url'])) {
                $data['location'] = (isset($data['DefaultLocation'])) ? $data['DefaultLocation'] : '';
            } else {
                $data['location'] = $data['long_url'];
            }
            if (!empty($data['location'])) {
                header('Location: ' . $data['location']);
            }
            $data['warning'][] = 'Redirection did not occur due to lack of default redirection location';
        }
        die(json_encode($data, JSON_PRETTY_PRINT));
    }

}

