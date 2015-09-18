<?php

/**
 * Created by PhpStorm.
 * User: nelson
 * Date: 9/17/2015
 * Time: 10:34 AM
 */
class CurlWorker
{
    private $curl_info;
    private $base_url;
    private $depth = 0;
    private static $instance;
    const MAX_DEPTH = 1;
    private function __construct($url)
    {
        $this->base_url = $url;

    }
    public static function getInstance($url = null)
    {
        if (null === static::$instance) {
            static::$instance = new static($url);
        }
        return static::$instance;
    }
    public function curl()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $info = curl_getinfo($ch);

        curl_close($ch);
    }

}