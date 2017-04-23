<?php

namespace UrlCompressor;

/**
 * @author Emrah TOY <code@emrahtoy.com>
 * @description Extends the given short code and returns url
 *
 * Class Extend
 * @package UrlCompressor
 */
class Expand extends Common
{


    public function expand($shortCode = '')
    {
        if (empty($shortCode)) {
            $this->result->error('Short code can not be null or empty');
            return $this->result->result();
        }


        $this->shortCode = $shortCode;

        $check = $this->checkFromDb('short_code', $shortCode, true, true);
        if ($check === false) {
            $this->result->error('Short code is not exist', ['short_code' => $this->shortCode]);
        } else {
            $this->result->success('Short code is exist', ['short_code' => $this->shortCode, 'long_url' => $this->url]);
            $this->saveToCache(); // update ttl
            if ($this->config['CheckUrl']) {
                $check = $this->urlCheck();
            }
            if ($this->config['Track']) {
                $tracked=$this->track();
                $this->result->add('tracked',$tracked);
            } else {
                $this->result->add('tracked',false);
            }
        }

        return $check;
    }

    private function track()
    {
        $stmt = $this->dbConnection->prepare('INSERT INTO ' . $this->config['TrackTableName'] . ' (short_code, visits) VALUES(:short_code,1) ON DUPLICATE KEY UPDATE    
visits=visits+1;');
        // check if the URL has already been shortened
        return $stmt->execute(['short_code' => $this->shortCode]);
    }

    private function getIDFromShortenedURL($shortCode)
    {
        $length = strlen($this->allowedChars);
        $size = strlen($shortCode) - 1;
        $shortCode = str_split($shortCode);
        $out = strpos($this->allowedChars, array_pop($shortCode));
        foreach ($shortCode as $i => $char) {
            $out += strpos($this->allowedChars, $char) * pow($length, $size - $i);
        }
        $out -= 65536;
        return $out;
    }

}