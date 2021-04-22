<?php
include __DIR__ . '/../autoload.php';

use lyhiving\guanyierp\guanyierp;

$config = [
    'appkey' =>  '[APPKEY]',
    'secret' =>  '[SECRET]',
    'sessionkey' =>  '[SESSIONKEY]'
];


$erp = new guanyierp($config);


//获取店铺信息
$data = [];
$data['page_no'] = '1';
$data['page_size'] = '1';
// $data['code'] = '106';
echo '---getShop Start--' . PHP_EOL;
$result = $erp->getShop($data);
var_dump($result);
// var_dump($erp->get('orgi'));
// var_dump($erp->total);
// var_dump($erp->error(1));
echo '---getShop End--' . PHP_EOL;

//获取供应商信息
$data = [];
$data['page_no'] = 1;
$data['page_size'] = 1;
echo '---getSupplier Start--' . PHP_EOL;
$result = $erp->getSupplier($data);
var_dump($result);
echo '---getSupplier End--' . PHP_EOL;


//获取会员信息
$data = [];
$data['page_no'] = 1;
$data['page_size'] = 1;
echo '---getVip Start--' . PHP_EOL;
$result = $erp->getVip($data);
var_dump($result);
echo '---getVip End--' . PHP_EOL;

//获取商品库存信息
$data = [];
$data['page_no'] = 1;
$data['page_size'] = 1;
$data['item_code'] = 201495;
$data['warehouse_code'] = 110;
echo '---getStock Start--' . PHP_EOL;
$result = $erp->getStock($data);
var_dump($result);
echo '---getStock End--' . PHP_EOL;


//获取单商品在指定仓库的库存
echo '---getItemStock Start--' . PHP_EOL;
$result = $erp->getItemStock(110, 201495);
var_dump($result);
echo '---getItemStock End--' . PHP_EOL;



//批量设置商品新库存
$data = [];
$data['note'] = 'Test setStock for demo test';
$data['warehouse_code'] = 110;
$data['details'] = [
    [
        'item_code' => 201495,
        'qty'   => 66
    ]
];
echo '---setStock Start--' . PHP_EOL;
// $result = $erp->setStock($data);
// var_dump($result);
echo '---setStock End--' . PHP_EOL;



//批量设置商品新库存
$data = [];
$note = 'Test setItemStock for demo test';
$warehouse_code = 110;
$item_code = 201495;
$qry = 68;
// $item_code = 201497;
// $qry = 77;

$items = [
    [
        'item_code' => 201497,
        'qty'   => 12
    ],
    [
        'item_code' => 201491,
        'qty'   => 33
    ]
];


echo '---setItemStock Start--' . PHP_EOL;
$result = $erp->setItemStock($warehouse_code, $item_code, $qry, $note);
var_dump($result);
$result = $erp->setItemStock($warehouse_code, $items, null, 'Test setItemStock for batch test');
var_dump($result);
echo '---setItemStock End--' . PHP_EOL;
