<?php
/**
 * Created by PhpStorm.
 * User: nelson
 * Date: 9/17/2015
 * Time: 9:21 AM
 */
require_once("CurlWorker.php");
$url = $argv[1];
CurlWorker::getInstance($url)->cure();
$result = CurlWorker::getInstance()->getResult();