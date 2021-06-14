<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CosController extends Controller
{
    public $local;
    public function __construct()
    { }

    public function getSign(Request $request)
    {
        return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '']);
    }


    public function getSign2(Request $request)
    {
        $key = $request['key'];
        if ($_FILES["file"]["error"] > 0) {
            return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '文件错误']);
        } else {
            if (file_exists("admin/" . $_FILES["file"]["name"])) {
                return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '文件存在']);
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], 'admin/' . $key);
            }
        }
        return response()->json(['ServerTime' => time(), 'ServerNo' => 200, 'ResultData' => '200']);
    }
}
