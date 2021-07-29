<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    //对蓝卓的开放API映射
    'open-api/app/tenants' => 'index/bluetron/open_tenant',
    'open-api/app/tenants/status' => 'index/bluetron/get_tenant_status',
    'open-api/app/renew' => 'index/bluetron/relet_tenant',
    'open-api/app/stop' => 'index/bluetron/stop_tenant',
];
