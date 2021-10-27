<?php

namespace lyhiving\guanyierp;

class guanyiweb
{
    /** 配置文件 */
    public $config = [];
    public $data;
    public $preurl = 'http://v2.guanyierp.com/';
    public $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36';
    private $_error; //详细代码信息
    private $_errno; //出错代码编号
    public $orgi; //原始数据
    public $total; //内容的总数量
    public $url;
    public $path;


    public function __construct($config = [])
    {
        if ($config) {
            $this->config($config);
        }
    }

    public function config($config){
        if(!is_array($config)){
            $_config = []; 
            $_config['Cookie'] = $config;
            $config = $_config;
        }
        if (!isset($config['User-Agent'])) {
            $config['User-Agent'] =  $this->useragent;
        }
        $this->set('config', $config);
        return $this;
    }

    public function initWeb($string)
    {
        if(!$string){
            $this->setErr('WEB参数未配备');
            return false;
        }
        $config = json_decode($string, true);
        if(isset($config['Cookie'])){
            $this->config($config);
        }else{
            $this->config($string);
        }
        $config =  $this->get('config');
        if(!isset($config['Cookie'])||!$config['Cookie']){
            return false;
        }
        return $this;
    }

    /**
     * 通过配置单独定义函数的配置
     */
    public function set($k, $v)
    {
        $this->$k = $v;
        return $this;
    }

    public function get($k)
    {
        return $this->$k;
    }

    //设置允许值
    public function setErr($error, $errno = 500)
    {
        $this->set('_error', $error);
        $this->set('_errno', $errno);
        return $this;
    }

    public function error($no = false)
    {
        return $this->get($no ? '_errno' : '_error');
    }

    //设置正常状态
    public function setOK()
    {
        $this->set('_error', null);
        $this->set('_errno', 0);
        return $this;
    }

    /**
     * 获取数据
     * $method 获取命令
     * $data 数据
     * $filed 指定主键
     */
    public function getTo($method, $data, $filed = null)
    {
        $this->data = $data;
        $this->path =  $method;

        $result = $this->webPost();
        return $this->handler($result, $filed);
    }

    //处理返回信息
    public function handler($data, $filed = null)
    {
        $this->orgi = $data;
        if (!$data) return false;
        if (!is_array($data)) return false;
        $this->setOK();
        if (isset($data['total'])) $this->set('total', $data['total']);
        return $filed && isset($data[$filed]) ? $data[$filed] : $data;
    }


    public function jsonEncodeCh($arr)
    {
        return urldecode(json_encode($this->urlEncodeArr($arr)));
    }

    public function urlEncodeArr($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $arr[$k] = $this->urlEncodeArr($v);
            }
        } elseif (!is_numeric($arr) && !is_bool($arr)) {
            $arr = urlencode($arr);
        }
        return $arr;
    }

    public function webPost($url = null)
    {
        if (!$this->data) {
            $this->setErr('Data is null', 400);
            return false;
        }
        if (!$this->path) {
            $this->setErr('Path is null', 400);
            return false;
        }
        $this->config = $this->get('config');
        $this->cookie = $this->config['Cookie'];
        if (!$this->cookie) {
            $this->setErr('Cookie is null', 400);
            return false;
        }

        $this->url =  $this->preurl . $this->path."?_dc=".ceil(str_replace('.','',microtime(true))/10);
        $header = [];
        $header[] = "Content-Type: application/x-www-form-urlencoded; charset=UTF-8";
        $header[] = "Origin: " . $this->preurl;
        $header[] = "Referer: " . $this->url;
        $header[] = "Cookie: " . $this->cookie;
        if(isset($this->config['User-Agent']) && $this->config['User-Agent'])$header[] = "User-Agent: " . $this->config['User-Agent'];
        $body =  $this->url('POST', $this->url, $this->data, ['header' => $header]);
        $meta = json_decode($body, true);
        if (!$meta) {
            $this->setErr('Response is not json format', 500);
            return false;
        }
        $this->setOK();
        return $meta;
    }

    // 访问远程
    public function url($method, $url, $data = [], $extopt = [], $timeout = null)
    {
        $method = strtoupper($method);
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!function_exists('curl_init') || ($extopt && isset($extopt['file_get_contents']) && $extopt['file_get_contents'])) {
            if (isset($extopt['file_get_contents'])) unset($extopt['file_get_contents']);
            if (is_array($data)) {
                $data = http_build_query($data, "", '&');
            }
            $opts = array(
                $scheme => array(
                    'method' => $method,
                    'header' => '',
                    'content' => $data,
                    'timeout' => 60,
                    'Connection' => "close"
                )
            );
            if (!is_numeric($timeout)) unset($opts[$scheme]['timeout']);
            if (is_null($data)) unset($opts[$scheme]['content']);

            if ($scheme == 'https') { //忽略证书部分
                $extopt["ssl"] = array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                );
            }
            if ($extopt) {
                if (isset($extopt['header']) && $extopt['header'] && is_array($extopt['header'])) {
                    $extopt['header'] = implode('\r\n', $extopt['header']);
                }
                $opts = array_merge($opts, $extopt);
            }
            $context = stream_context_create($opts);
            $content = file_get_contents($url, false, $context);
            return $content;
        } else {
            if (isset($extopt['file_get_contents'])) unset($extopt['file_get_contents']);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($method == 'POST') {
                curl_setopt($ch, CURLOPT_POST, 1);
                if (is_array($data)) {
                    $data = http_build_query($data, "", '&');
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            if ($method == 'GET') {
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            }
            if (isset($extopt['header']) && $extopt['header'] && is_array($extopt['header'])) {
                array_push($extopt['header'], 'Content-Length:' . strlen($data));

                curl_setopt($ch, CURLOPT_HTTPHEADER, $extopt['header']);
            }
            $content = curl_exec($ch);
            curl_close($ch);
            return $content;
        }
    }
}
