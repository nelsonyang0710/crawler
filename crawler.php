<?php
/**
 * Created by PhpStorm.
 * User: nelson
 * Date: 9/17/2015
 * Time: 9:21 AM
 */
require_once("CurlWorker.php");
$url = $argv[1];
$url = 'http://www.care2.com';
try
{
    $curl_worker = new CurlWorker($url);
    $curl_worker->curl();
}
catch (Exception $e)
{
    echo $e->getMessage();
    die();
}
$result = $curl_worker->getResult();
$total_request_count = 0;
$total_download_size = 0;
$host = $curl_worker->getHost();
foreach ($result as $type)
{
    $total_request_count += $type['count'];
    $total_download_size += $type['size'];
}
$total_request_count = number_format($total_request_count);
$total_download_size = number_format($total_download_size);

echo "Processed URL : $host\n\n";

echo "Total Requests : $total_request_count\n";
echo "Total Size  : $total_download_size bytes\n";

echo "By Content Type\n";
foreach ($result as $type => $type_info)
{
    $uc_type = ucwords($type);
    $request_count = number_format($type_info['count']);
    $request_size = number_format($type_info['size']);
    echo "$uc_type : $request_count requests, $request_size bytes\n";
}