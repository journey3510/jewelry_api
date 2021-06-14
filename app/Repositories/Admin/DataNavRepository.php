<?php

namespace App\Repositories\Admin;

use App\Repositories\BaseRepository;

class DataNavRepository
{
    use BaseRepository;

    // 表名  角色-导航栏权限表
    protected static $table = 'data_menu';


    public function unionForm(Request $request)
    { }
}
