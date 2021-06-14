<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// COS
$router->post('api/getsign', 'CosController@getSign2');

// 验证码
$router->get('api/admin/captcha/{number}', 'Admin\AdminController@getCheckVerifyCode');

// 后台管理系统
$router->group(['middleware' => 'api'], function () use ($router) {
    // 后台登录接口
    $router->post('api/admin/login', 'Admin\AdminController@login');
    // 后台获取管理员数据接口
    $router->post('api/admin/info', 'Admin\AdminController@getInfo');
    // 后台登出接口
    $router->post('api/admin/logout', 'Admin\AdminController@logout');

    // 后台后台获取管理员数据列表接口
    $router->post('api/admin/infolist', 'Admin\AdminController@getInfoList');
    // 后台禁用管理员状态接口
    $router->post('api/admin/disable', 'Admin\AdminController@disable');
    // 后台启用管理员状态接口
    $router->post('api/admin/enable', 'Admin\AdminController@enable');
    // 后台添加管理员用户接口
    $router->post('api/admin/addUser', 'Admin\AdminController@addUser');
    // 后台修改管理员用户接口
    $router->post('api/admin/updateUser', 'Admin\AdminController@updateUser');
    // 用户 选择 角色 接口
    $router->post('api/relrole/select', 'Admin\AdminController@selectRole');

    /**
     * 用户信息
     */
    // 后台获取用户数据列表接口
    $router->post('api/user/infolist', 'Admin\UserController@getInfoList');
    // 后台禁用用户状态接口
    $router->post('api/user/disable', 'Admin\UserController@disable');
    // 后台启用用户状态接口
    $router->post('api/user/enable', 'Admin\UserController@enable');
    // 修改用户信息接口
    $router->post('api/user/updata', 'Admin\UserController@updataUser');
    // 用户信息接口
    $router->post('api/user/details', 'Admin\UserController@details');


    /**
     * 订单留言 信息
     */
    // 数据列表接口
    $router->post('api/message/infolist', 'Admin\OrderMessageController@getInfoList');
    // 删除接口
    $router->post('api/message/modify', 'Admin\OrderMessageController@modify');
    // 留言 详情 接口
    $router->post('api/message/details', 'Admin\OrderMessageController@details');
    // 回复  接口
    $router->post('api/message/updata', 'Admin\OrderMessageController@updata');



    /**
     * 购物车 信息
     */
    // 数据列表接口
    $router->post('api/car/infolist', 'Admin\CarController@getInfoList');
    // 获取详细信息
    $router->post('api/car/details', 'Admin\CarController@details');
    // 修改
    $router->post('api/car/updata', 'Admin\CarController@updateCar');


    /**
     * 订单信息
     */
    // 数据列表接口
    $router->post('api/order/infolist', 'Admin\OrderController@getInfoList');
    // 单条数据详情接口
    $router->post('api/order/details', 'Admin\OrderController@details');
    // 订单更改信息 / 退款
    $router->post('api/order/updata', 'Admin\OrderController@updateOrder');
    // 订单发货
    $router->post('api/order/shipment', 'Admin\OrderController@shipment');


    /**
     * 商品管理 
     */
    $router->post('api/item/infolist', 'Admin\ItemController@getInfoList');
    // 单条数据详情接口
    $router->post('api/item/details', 'Admin\ItemController@details');
    // 商品更改信息
    $router->post('api/item/updata', 'Admin\ItemController@updateGoods');
    // 商品评论
    $router->post('api/item/comment', 'Admin\ItemController@getComment');
    // 商品增加
    $router->post('api/item/add', 'Admin\ItemController@goodsAdd');

    // 商品轮播图
    $router->post('api/item/rollimg/delete', 'Admin\ItemController@rollimgDelete');
    $router->post('api/item/rollimg/add', 'Admin\ItemController@rollimgAdd');




    /**
     * 系列 管理 
     */
    // 数据列表接口
    $router->post('api/serie/infolist', 'Admin\SerieController@getInfoList');
    // 系列名称 接口
    $router->post('api/seriename/infolist', 'Admin\SerieController@getAllserieNameList');
    // 更改信息
    $router->post('api/serie/updata', 'Admin\SerieController@updateSerie');
    // 新增 系列 接口
    $router->post('api/serie/add', 'Admin\SerieController@serieAdd');


    /**
     * 导航权限管理
     */
    // 数据列表接口
    $router->post('api/menu/infolist', 'Admin\NavController@getInfoList');
    // 修改接口
    $router->post('api/menu/modify', 'Admin\NavController@modify');
    // 添加接口
    $router->post('api/menu/add', 'Admin\NavController@add');
    // 删除接口
    $router->post('api/menu/delete', 'Admin\NavController@delete');

    /**
     * 动作权限管理
     */
    // 数据列表接口
    $router->post('api/action/infolist', 'Admin\ActionController@getInfoList');
    // 修改接口
    $router->post('api/action/modify', 'Admin\ActionController@modify');
    // 添加接口
    $router->post('api/action/add', 'Admin\ActionController@add');
    // 删除接口
    $router->post('api/action/delete', 'Admin\ActionController@delete');

    /**
     * 角色管理
     */
    // 数据列表接口
    $router->post('api/role/infolist', 'Admin\RoleController@getInfoList');
    // 禁用状态接口
    $router->post('api/role/disable', 'Admin\RoleController@disable');
    // 启用状态接口
    $router->post('api/role/enable', 'Admin\RoleController@enable');
    // 新增角色接口
    $router->post('api/role/addrole', 'Admin\RoleController@addRole');
    // 修改角色信息接口
    $router->post('api/role/updata', 'Admin\RoleController@updata');

    /**
     * 角色 - 权限 管理 
     */
    // 数据列表接口
    $router->post('api/roleauthority/infolist', 'Admin\RelRoleController@getInfoList');
    // 登录时 获取权限 接口
    $router->post('api/roleauthority/list', 'Admin\RelRoleController@roleAuthority');
    //  菜单权限修改
    $router->post('api/rolemenu/updata', 'Admin\RelRoleController@updata');
    //  动作权限修改
    $router->post('api/roleaction/updata', 'Admin\RelRoleController@actionUpdata');
});


// mall 客户端
$router->group([], function () use ($router) {
    // 首页
    $router->post('api/client/home/productList', 'Client\ItemController@productList');

    // 商品详情
    $router->post('api/client/product/detail', 'Client\ItemController@detail');
    // 商品搜索
    $router->post('api/client/product/search', 'Client\ItemController@search');
    // 商品系列 列表
    $router->post('api/client/product/serielist', 'Client\ItemController@getSerieList');
    // 评论
    $router->post('api/client/product/comment', 'Client\ItemController@getComment');

    // 订单
    $router->post('api/client/order/findOrderList', 'Client\OrderController@findOrderList');
    $router->post('api/client/order/orderSure', 'Client\OrderController@orderSure');
    $router->post('api/client/order/createOrder', 'Client\OrderController@createOrder');
    $router->post('api/client/order/orderPay', 'Client\OrderController@orderPay');
    $router->post('api/client/order/receipt', 'Client\OrderController@receipt');

    // 购物车
    $router->post('api/client/cart/addCart', 'Client\CarController@addCart');
    $router->post('api/client/cart/cartNum', 'Client\CarController@cartNum');
    $router->post('api/client/cart/findCart', 'Client\CarController@findCart');
    $router->post('api/client/cart/deleteCart', 'Client\CarController@deleteCart');

    // 用户地址
    $router->post('api/client/address/add', 'Client\UserController@addAddress');
    $router->post('api/client/address/get', 'Client\UserController@getAddress');
    $router->post('api/client/address/updata', 'Client\UserController@updataAddress');

    // 用户注册接口
    $router->post('api/client/user/register', 'Client\UserController@addUser');
    // 用户对商品评论
    $router->post('api/client/user/comment/add', 'Client\UserController@addComment');

    // 登录
    $router->post('api/client/user/login', 'Client\UserController@login');
    // 注册
    $router->post('api/client/user/regiter', 'Client\UserController@regiter');

    $router->post('api/client/test1', 'Client\UserController@test1');
    $router->post('api/client/test2', 'Client\UserController@test2');
});
