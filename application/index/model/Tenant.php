<?php
namespace app\index\model;

use think\Model;

class Tenant extends Model
{
    protected $table = 'sys_tenant';
    protected $pk = 'tenant_id';
}