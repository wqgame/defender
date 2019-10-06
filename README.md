# Defender
A security component for php-based site.

## 功能
1. ua拦截
2. ip黑白名单、地区拦截
3. 接口频次拦截
4. 其他WAF功能（TODO）


## 使用说明

#### 安装
```
composer require 'wqgame/defender'
```

#### UA拦截示例
```php
use Defender\UA;

// 屏蔽指定UA列表
$blocked_list = ['chrome', 'Android', 'XiaoMi'];
if (UA::isBlockedByList($blocked_list)) {
    exit('自动获取的UA被屏蔽');
}
// 第二个参数指定UA而非自动获取
if (UA::isBlockedByList($blocked_list, 'Molliza Firefox xxx')) {
    exit('指定UA被屏蔽');
}
// 第三个参数，是否大小写敏感，默认false忽略大小写
if (UA::isBlockedByList($blocked_list, '', true)) {
    exit('自动获取的UA被屏蔽（大小写敏感）');
}
```

#### IP拦截示例
```php
use Defender\IP;

// 屏蔽指定IP列表
$blocked_list = ['127.0.0.1', '163.177.65.160/24'];
if (IP::isBlockedByList($blocked_list)) {
    exit('自动获取的IP被屏蔽');
}
if (IP::isBlockedByList($blocked_list, '127.0.0.1')) {
    exit('指定IP被屏蔽');
}

// 屏蔽指定地区列表
$blocked_list = ['美国', '上海', '网吧', '联通'];
if (IP::isBlockedByDistrictList($blocked_list)) {
    exit('IP被屏蔽');
}
```

#### 频次拦截示例
> 依赖框架的Cache组件，该需要实现get、set方法。

```php
use Defender\Frequency;

// 注入Cache组件
Frequency::setCacher('\thinkphp\Cache');

// 根据Key来判断是否被阻止
$key = 'my/index';
$times_1min = 60;
$times_1hour = 2000;
$times_1day = 40000;
if (Frequency::isBlocked($key, $times_1min, $times_1hour, $times_1day)) {
    exit('操作过于频繁');
}

// 正常逻辑走完后，增加1次调用
Frequency::increase($key);
```