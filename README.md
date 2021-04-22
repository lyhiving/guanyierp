## 管易ERP

管易云PHP SDK。





## 安装

使用 Composer

```json
composer require lyhiving/guanyierp
```
或直接在composer.json 添加
```json
{
    "require": {
            "lyhiving/guanyierp": "1.*"
    }
}
```

## 用法

### 初始化

请通过 [后台](http://v2.guanyierp.com/index)->控制面板->应用授权->云ERP授权 获取对应。

```php
use lyhiving\guanyierp\guanyierp;

$config = [
    'appkey' =>  '[APPKEY]',
    'secret' =>  '[SECRET]',
    'sessionkey' =>  '[SESSIONKEY]'
];

$erp = new guanyierp($config);

```



### 通用获取方法getTo：

//获取方法，具体看[接口文档](http://support.guanyierp.com/hc/kb/category/1005768/)

```php
$method='gy.erp.shop.get'; 
$data =[];
$filed ='shops';
$result = $erp->getTo($method, $data, $filed);

```

### 常见变量的获取：
```php
$error = $erp->error(); //出错信息，为否时表示无出错
$errno = $erp->error(true); //出错代号

$total = $erp->total; //条目总数，一般出现在请求列表
$orgi = $this->get('orgi'); //原始的返回内容

```


### 获取店铺信息：

方法：*gy.erp.shop.get*

[说明地址](http://support.guanyierp.com/hc/kb/article/1234935/)

```php
$data=[];
$data['page_no'] = '1';
$data['page_size'] = '10';
$result = $erp->getShop($data);
```


## 其他常用的方法

### 供应商查询：

方法：*gy.erp.supplier.get*

[说明地址](http://support.guanyierp.com/hc/kb/article/1234935/)

`里面提到的返回字段supplier是错的，实际上是supplier_list。`

```php
$data=[];
$data['page_no'] = '1';
$data['page_size'] = '10';
$result = $erp->getShop($data);
```


### 会员信息查询：
方法：*gy.erp.vip.get*

[说明地址](http://support.guanyierp.com/hc/kb/article/1234990/)

`里面提到的返回字段vip是错的，实际上是vips。有见及此，使用本Class应该尽量通过getTo的方法进行`

```php
$data=[];
$data['page_no'] = '1';
$data['page_size'] = '10';
$result = $erp->getShop($data);
```

### 库存信息查询：
方法：*gy.erp.new.stock.get*

[说明地址](http://support.guanyierp.com/hc/kb/article/1235063/)


```php
$data=[];
$data['page_no'] = 1;
$data['page_size'] = 1;
$data['item_code'] = 201495;
$data['warehouse_code'] = 110;
$result = $erp->getStock($data);
```

### 获取单商品在指定仓库的库存：

```php
$result = $erp->getItemStock(201495,110);
```


### 设置指定仓库某个商品库存：
方法：*gy.erp.stock.count.add*

[说明地址](http://support.guanyierp.com/hc/kb/article/1235058/)

```php
$note = 'Test setItemStock for demo test';
$warehouse_code = 110;
$item_code = 201495;
$qry = 68;
$result = $erp->setItemStock($warehouse_code, $item_code, $qry, $note);

```

如果需要一次设置多个商品库存，直接将$item_code 换成数组。如：

```php
$note = 'Test setItemStock for demo test';
$warehouse_code = 110;
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
$result = $erp->setItemStock($warehouse_code, $items, null, 'Test setItemStock for batch test');
```


