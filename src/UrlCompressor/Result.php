<?php


namespace UrlCompressor;

/**
 * @author Emrah TOY <code@emrahtoy.com>
 * @description Extends the given short code and returns url
 *
 * Class Result
 * @package UrlCompressor
 */
class Result
{
    private $result = [];

    public function set($result)
    {
        $this->result = $result;
    }

    public function merge($resultToMerge)
    {
        $this->result = array_merge($this->result, $resultToMerge);
    }

    public function clear()
    {
        $this->result = [];
    }

    public function add($key, $value,$isCollection=false)
    {
        if ($isCollection || (isset($this->result[$key]) && is_array($this->result[$key]))) {
            $this->result[$key][] = $value;
        } else {
            $this->result[$key] = $value;
        }
    }

    public function get($key)
    {
        return $this->result[$key];
    }

    public function remove($key)
    {
        unset ($this->result[$key]);
    }

    public function result()
    {
        ksort($this->result);
        return $this->result;
    }

    public function error($message, $extra = [])
    {
        $this->add('error', $message);
        $this->remove('success');
        if (!empty($extra))
            $this->merge($extra);
    }

    public function success($message, $extra = [])
    {
        $this->add('success', $message);
        $this->remove('error');
        if (!empty($extra))
            $this->merge($extra);
    }
}