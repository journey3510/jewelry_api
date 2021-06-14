<?php

namespace App\Http\Controllers;


use App\Library\Tools\Common\Common;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }



    public function webHook()
    {
        // 此处用Jobs做 体验更佳
        $webhook = "";
        //        $msg = [
        //            'msgtype' => 'text',
        //            'text' => [
        //                'content' => '由服务器端推送测试内容 @群内成员 : ' . date('Y-m-d H:i'),
        //                "mentioned_mobile_list" => ["15347074299", '15878295286']
        //            ]
        //        ];

        $msg = [
            "msgtype" =>  "markdown",
            "markdown" => [
                "content" => "实时新增用户反馈<font color='warning'>132例</font>,请相关同事注意. \r
             > 类型: <font color='comment'>用户反馈</font>
             > 普通用户反馈: <font color='comment'>117例</font>
             > VIP用户反馈: <font color='comment'>15例</font>
             > 报错代码: `/vagrant/journey/Service/app/Http/Controllers/ExampleController.php:132:Class `"
            ]
        ];

        $data_string = json_encode($msg);

        $res = Common::curl($webhook, $data_string);
        return $res;
    }
}
