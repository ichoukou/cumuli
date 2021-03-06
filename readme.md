# Cumuli系统

[![Latest Stable Version](https://poser.pugx.org/wangdong/cumuli/version)](https://packagist.org/packages/wangdong/cumuli) 
[![Latest Unstable Version](https://poser.pugx.org/wangdong/cumuli/v/unstable)](https://packagist.org/packages/wangdong/cumuli) 
[![Total Downloads](https://poser.pugx.org/wangdong/cumuli/downloads)](https://packagist.org/packages/wangdong/cumuli) 
[![composer.lock available](https://poser.pugx.org/wangdong/cumuli/composerlock)](https://packagist.org/packages/wangdong/cumuli)

## 捐赠作者

<img src="resources/assets/img/alipay.png" width="240" alt="支付宝捐赠" />

## 开发前准备

1. 安装编辑器插件: http://editorconfig.org/#download
2. 安装composer命令: 参考https://getcomposer.org
3. 安装node.js >= 6.0，下载地址: https://nodejs.org
4. 安装mysql >= 5.7，其他版本未进行测试

## 配置国内加速

**配置composer**

```
composer config -g repo.packagist composer https://packagist.phpcomposer.com
```

**配置npm**

```
npm install -g nrm --registry=https://registry.npm.taobao.org
nrm use taobao
```

**配置node-sass**

> linux或mac配置

```
echo 'sass_binary_site=https://npm.taobao.org/mirrors/node-sass/' >> ~/.npmrc
```

> windows配置

打开 `环境变量 -> 新建`，进行如下设置

- 变量名(N): `SASS_BINARY_SITE`
- 变量值(V): `https://npm.taobao.org/mirrors/node-sass/`

## 初始化脚本

**通过composer方式**
```
composer create-project wangdong/cumuli cumuli
cd cumuli
php artisan install
```

**其他方式**
```
git clone https://github.com/repertory/cumuli.git cumuli
cd cumuli
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan install
```

## 开发模块

[模块开发文档](module/README.md)

## nginx配置

```
server {
    listen       80;
    server_name  cumuli.dev;
    root         /var/www/html/cumuli/public;

    location / {
        index  index.html index.htm index.php;
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}
```

## 开源协议

> 由于前端部分引入了 `jQuery EasyUI` 并仅作为类库使用，同时未对其进行任何修改

本项目采用MIT协议(EasyUI代码除外，仍需遵守GPL协议)，也就是双协议(MIT + GPL)
