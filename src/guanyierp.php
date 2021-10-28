<?php

namespace lyhiving\guanyierp;

class guanyierp
{
    /** 配置文件 */
    public $config = [];
    public $data;
    public $url = 'https://v2.api.guanyierp.com/rest/erp_open';
    private $_error; //详细代码信息
    private $_errno; //出错代码编号
    public $orgi; //原始数据
    public $body;
    public $total; //内容的总数量

    /**
     *  $config['appkey'] = APPKEY;
     *  $config['secret'] = SECRET;
     *  $config['sessionkey'] = SESSIONKEY;
     */
    public function __construct($config = [])
    {
        if ($config) {
            $this->set('config', $config);
        }
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

    public function getErr($no = false)
    {
        return $this->error($no);
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
        $this->data['method'] = $method;
        $result = $this->webPost();
        return $this->handler($result, $filed);
    }

    //处理返回信息
    public function handler($data, $filed = null)
    {
        $this->orgi = $data;
        if (!$data) return false;
        if (!is_array($data)) return false;
        if (!isset($data['success'])) return false;
        if (!$data['success']) {
            $this->setErr(isset($data['errorDesc']) ? $data['errorDesc'] : true, isset($data['errorCode']) ? $data['errorCode'] : 500);
        }
        $this->setOK();
        if (isset($data['total'])) $this->set('total', $data['total']);
        return $filed && isset($data[$filed]) ? $data[$filed] : $data;
    }

    //获取店铺信息
    public function getShop($data = [], $key = 'shops')
    {
        return  $this->getTo('gy.erp.shop.get', $data, $key);
    }

    // 供应商查询
    public function getSupplier($data = [], $key = 'supplier_list')
    {
        return  $this->getTo('gy.erp.supplier.get', $data, $key);
    }

    // 会员查询
    public function getVip($data = [], $key = 'vips')
    {
        return  $this->getTo('gy.erp.vip.get', $data, $key);
    }

    // 获取库存
    public function getStock($data = [], $key = 'stocks')
    {
        return  $this->getTo('gy.erp.new.stock.get', $data, $key);
    }



    // 获取某个指定商品指定仓库库存
    public function getItemStock($warehouse_code, $item_code)
    {
        $data['page_no'] = 1;
        $data['page_size'] = 1;
        $data['warehouse_code'] = $warehouse_code;
        $data['item_code'] = $item_code;
        $result = $this->getStock($data);
        if (!$result || !$result[0]) return false;
        if (!isset($result[0]['qty'])) return 0;
        return $result[0]['qty'];
    }


    // 通过结算单设置库存
    public function setStock($data = [], $key = null)
    {
        return  $this->getTo('gy.erp.stock.count.add', $data, $key);
    }

    // 获取某个指定商品指定仓库库存，不允许负数
    public function setItemStock($warehouse_code, $item_code, $qry, $note = null)
    {
        $data = [];
        $data['warehouse_code'] = $warehouse_code;
        $data['note'] = $note;
        if (is_array($item_code)) {
            $data['details'] = $item_code;
        } else {
            $data['details'] = array(
                array(
                    'item_code' => $item_code,
                    'qty'   => $qry
                )
            );
        }
        return $this->setStock($data);
    }


    // 调整仓库库存
    public function adjustItemStock($warehouse_code, $item_code, $qry, $note = null)
    {
        $data = [];
        $data['warehouse_code'] = $warehouse_code;
        $data['note'] = $note;
        if (is_array($item_code)) {
            $data['detail_list'] = $item_code;
        } else {
            $data['detail_list'] = array(
                array(
                    'item_code' => $item_code,
                    'qty'   => $qry
                )
            );
        }
        return $this->adjustStock($data);
    }


    // 通过调整单设置库存
    public function adjustStock($data = [], $key = null)
    {
        return  $this->getTo('gy.erp.stock.adjust.add', $data, $key);
    }


    public function sign()
    {
        if (empty($this->data)) {
            return '';
        }
        $data = $this->jsonEncodeCh($this->data);
        $this->data['sign'] = strtoupper(md5($this->config['secret'] . $data . $this->config['secret']));
        return $this->data['sign'];
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
        if (!isset($this->data['method'])) {
            $this->setErr('Method is null', 400);
            return false;
        }
        if (!isset($this->data['appkey'])) {
            $this->data['appkey'] = $this->config['appkey'];
        }
        if (!isset($this->data['sessionkey'])) {
            $this->data['sessionkey'] = $this->config['sessionkey'];
        }
        if (is_null($url)) $url = isset($this->config['url']) && $this->config['url'] ? $this->config['url'] : $this->url;


        if (!isset($this->data['sign'])) {
            $this->sign();
        }
        if (1) {
            $this->body =  $this->url('POST', $url, urlencode($this->jsonEncodeCh($this->data)), ['header' => ['Content-Type:text/json;charset=utf-8']]);
        } else {
            $data_string = $this->jsonEncodeCh($this->data);
            echo 'request: ' . $data_string . "\n";
            $data_string = urlencode($data_string);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:text/json;charset=utf-8',
                'Content-Length:' . strlen($data_string)
            ));
            $this->body = curl_exec($ch);
            curl_close($ch);
        }
        $meta = json_decode($this->body, true);
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
