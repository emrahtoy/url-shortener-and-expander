<?php


namespace UrlCompressor;

/**
 * @author  Emrah TOY <code@emrahtoy.com>
 * @description Shortens the given url and returns short code
 *
 * Class Shorten
 * @package UrlCompressor
 * @version 0.1
 */
class Shortener extends Common
{

    public function shorten($url = '')
    {
        if (empty($url)){
            $this->result->error('Url can not be null or empty');
            return false;
        }


        $this->url = $url;
        $check = $this->checkFromDb('long_url', $url,true);
        if ($check === false) {
            //do shorten
            $check=$this->save();
        } else {
            $this->result->error('Url already shortened',['short_code' => $this->shortCode, 'long_url' => $this->url]);
        }
        return $check;
    }

    private function save()
    {
        if ($this->config['CheckUrl']) {
            $check = $this->urlCheck();
            if ($check !== true)
                return $check;
        }
        try {
            $this->dbConnection->beginTransaction();
            $stmt2 = $this->dbConnection->prepare('INSERT INTO ' . $this->config['ShortUrlTableName'] . " (long_url, created, creator) VALUES (?,?,?)");
            $stmt2->execute(array($this->url, time(), $_SERVER['REMOTE_ADDR']));
            $tmp = $this->dbConnection->lastInsertId();

            $this->shortCode = $this->getShortenedURLFromID((int)$tmp);

            if (empty($this->shortCode)) {
                $this->dbConnection->rollBack();
                $this->result->error('Could not create short code', ['long_url' => $this->url]);
                return false;
            }
            $stmt2 = $this->dbConnection->prepare('UPDATE ' . $this->config['ShortUrlTableName'] . " SET short_code=:short_code WHERE id=:id");
            $stmt2->execute(['short_code' => $this->shortCode, 'id' => $tmp]);
            $this->dbConnection->commit();
            $this->result->success('Created',['short_code' => $this->shortCode, 'long_url' => $this->url]);
            $this->saveToCache();
            return true;

        } catch (Exception $e) {
            $this->dbConnection->rollBack();
            $this->result->error($e->getMessage());
            return false;
        }
    }

    private function getShortenedURLFromID($id)
    {
        $id += 65536;
        $length = strlen($this->allowedChars);
        $out = '';
        while ($id > $length - 1) {
            $out = $this->allowedChars[(int)fmod($id, $length)] . $out;
            $id = floor($id / $length);
        }
        $out = $this->allowedChars[(int)$id] . $out;
        return $out;
    }


}