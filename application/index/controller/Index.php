<?php

namespace app\index\controller;

use app\index\model\User;
use app\index\model\Tenant;

class Index extends Base
{
    public function index($tenantId)
    {
        $user = new User();
        if (!$tenantId) {
            $tenant = new Tenant();
            $tenantInfo = $tenant->find();
            if ($tenantInfo) {
                //这里其实应该用当前登录用户对应的租户ID去做查询，我这边偷懒了下，根据各自业务进行修改
                $tenantId =  $tenantInfo['tenant_id'];
            }
        }
        $user_list = $user->where('tenant_id', $tenantId)->select();
        $this->assign('user_list', $user_list);
        return $this->fetch('index');
    }
}
