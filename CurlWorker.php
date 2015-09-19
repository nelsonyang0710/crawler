<?php

/**
 * Created by PhpStorm.
 * User: nelson
 * Date: 9/17/2015
 * Time: 10:34 AM
 */
class CurlWorker
{
    private $curl_info = array();
    private $curl_error_count = 0;
    private $request_url = null;
    private $base_url = null;
    private $request_uri = null;

    /**
     * @param $url
     * @throws Exception
     */
    public function __construct($url)
    {
        $this->filterUrl($url);
    }

    /**
     * @param null $url
     * @throws Exception
     */
    public function curl($url = null)
    {
        $ch = curl_init();
        if ($url)
        {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        else
        {
            curl_setopt($ch, CURLOPT_URL, $this->request_url);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //to handle redirect request
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //to handle https request
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        if(!curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            curl_close($ch);
            if ($url)
            {
                if (strpos($info['content_type'],'html') !== false)
                {
                    $this->curl_info['html']['count'] ++;
                    $this->curl_info['html']['size'] += $info['size_download'];
                }
                else if (strpos($info['content_type'],'image') !== false)
                {
                    $this->curl_info['images']['count'] ++;
                    $this->curl_info['images']['size'] += $info['size_download'];
                }
                else if (strpos($info['content_type'],'css') !== false)
                {
                    $this->curl_info['css']['count'] ++;
                    $this->curl_info['css']['size'] += $info['size_download'];
                }
                else if (strpos($info['content_type'],'javascript') !== false)
                {
                    $this->curl_info['javascript']['count'] ++;
                    $this->curl_info['javascript']['size'] += $info['size_download'];
                }
                else if (!empty($this->curl_info[$info['content_type']]))
                {
                    $this->curl_info[$info['content_type']]['count'] ++;
                    $this->curl_info[$info['content_type']]['size'] += $info['size_download'];
                }
                else
                {
                    $this->curl_info[$info['content_type']] = array('count' => 1, 'size' => $info['size_download']);
                }
            }
            else
            {
                if (strpos($info['content_type'],'html') !== false)
                {
                    $this->curl_info['html'] = array('count' => 1, 'size' => $info['size_download']);
                    $this->curl_info['images'] = array('count' => 0, 'size' => 0);
                    $this->curl_info['css'] = array('count' => 0, 'size' => 0);
                    $this->curl_info['javascript'] = array('count' => 0, 'size' => 0);
                    preg_match_all('/(href|src)=["\'](\S*)["\']/', $content, $matches);
                    $unique_result = array_unique($matches[2]);
                    foreach ($unique_result as $html_url)
                    {
                        $html_url = $this->filterUrl($html_url);
                        if ($html_url != $this->request_url && !empty($html_url))
                        {
                            $this->curl($html_url);
                        }
                    }
                }
                else
                {
                    $this->curl_info[$info['content_type']] = array('count' => 1, 'size' => $info['size_download']);
                }
            }
        }
        else
        {
            $this->curl_error_count++;
            curl_close($ch);
        }
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->curl_info;
    }

    /**
     * @param $url
     * @return string
     * @throws Exception
     */
    private function filterUrl($url)
    {
        if (!empty($url))
        {
            $url_info = parse_url($url);
            if (is_null($this->request_url) || is_null($this->base_url))
            {
                if (empty($url_info['host']))
                {
                    throw new Exception('You have to provide a valid Url.');
                }
                else
                {
                    $this->base_url = (empty($url_info['schema']) ? 'http://' : $url_info['schema'] . '://') . $url_info['host'] ;
                    $this->request_uri = $this->base_url .(empty($url_info['path']) ? '' : $url_info['path']);
                    $this->request_url = $this->request_uri . (empty($url_info['query']) ? '' : '?' .$url_info['query']);
                }
            }
            else
            {
                if (!empty($url_info['host']))
                {
                    return (empty($url_info['schema']) ? 'http://' : $url_info['schema'] . '://') . $url_info['host'] . (empty($url_info['path']) ? '' : $url_info['path']) . (empty($url_info['query']) ? '' : '?' .$url_info['query']);
                }
                else if (!empty($url_info['path']))
                {
                    if (strpos($url_info['path'],'/') === 0)
                    {
                        return $this->base_url . (empty($url_info['path']) ? '' : $url_info['path']) . (empty($url_info['query']) ? '' : '?' .$url_info['query']);
                    }
                    else
                    {
                        return rtrim($this->request_uri, '/') . '/' . $url_info['path'] . (empty($url_info['query']) ? '' : '?' . $url_info['query']);
                    }
                }
            }
        }
        //for invalid path, return empty stating instead of throw exception
        return '';
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return parse_url($this->request_url, PHP_URL_HOST);
    }
}