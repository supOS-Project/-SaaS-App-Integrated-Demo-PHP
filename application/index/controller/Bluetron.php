<?php

namespace app\index\controller;

use app\index\model\Tenant;
use think\Controller;
use think\Config;
use think\Cache;
use bluetron\result;
use think\Log;

class Bluetron extends Controller
{

    /**
     * 蓝卓auth重定向携带code和原本的sessionId参数
     */
    public function index($code = '', $tenantId)
    {
        if ($code) {
            $this->getAccessToken($code, $tenantId);
        } else {
            $this->error('code不能为空');
        }
    }

    /**
     * 获取accessToken
     */
    public function getAccessToken($code, $tenantId)
    {
        $sessionId = session_id();
        $getAccessTokenUrl = Config::get('blueTronConfig.auth_url') . 'oauth/accessToken';
        $params['grantType'] = 'authorization_code';
        $params['appid'] = Config::get('blueTronConfig.appId');
        $params['secret'] = Config::get('blueTronConfig.appSecret');
        $params['code'] = $code;
        $data = http($getAccessTokenUrl, $params);
        if ($data) {
            $data = json_decode($data);
            $authInfo['accessToken'] = $data->data->accessToken;
            $authInfo['expiresIn'] = time();
            Cache::set("expiresIn", $data->data->expiresIn, $data->data->expiresIn);
            $authInfo['refreshToken'] = $data->data->refreshToken;
            $authInfo['userName'] = $data->data->userName;
            $authInfo['companyCode'] = $data->data->companyCode;
            $authInfo['personCode'] = $data->data->personCode;
            Cache::set($sessionId,$authInfo);
            $this->redirect('Index/index?tenantId=' . $tenantId);
        } else {
            exit('刷新蓝卓accessToken失败');
        }
    }

    /**
     * 开通租户
     */
    public function open_tenant()
    {
        $params = $this->request->param();
        $result = new result();
        $result->code = 0;
        $result->data = null;
        if (!$params) {
            $result->msg = '参数不能为空';
            return json($result);
        }
        if (!verifyBlueTronData(generateVerifyData($params), $params['sign'])) {
            $result->msg =  '签名校验不通过';
            return json($result);
        }


        //校验完毕后数据是存在的则可以入库操作
        $tenant = new Tenant();
        $tenant->instance_id = $params['instanceId'];
        $tenant->instance_name = $params['instanceName'];
        $tenant->start_time = $params['startDate'];
        $tenant->end_time = $params['endDate'];
        $tenant->app_id = $params['appId'];
        $tenant->region = $params['region'];
        $tenant->tenant_id = uuid();
        $tenant->status = 1;
        $tenant->save();

        $result->code = 200;
        $result->msg =  '开通租户成功';
        $result->data = ['tenantId' => $tenant->tenant_id];
        return json($result);
    }

    /**
     * 租户查询
     */
    public function get_tenant_status()
    {
        $params = $this->request->param();
        $result = new result();
        $result->code = 0;
        $result->data = null;
        if (!$params) {
            $result->msg =  '参数不能为空';
            return json($result);
        }
        if (!verifyBlueTronData(generateVerifyData($params), $params['sign'])) {
            $result->msg =  '签名校验不通过';
            return json($result);
        }

        $tenant = new Tenant();
        $info = $tenant->where('tenant_id', $params['tenantId'])->find();
        $result->code = 200;
        $result->msg =  '查询租户状态成功';
        $data['tenantId'] = $info['tenant_id'];
        $data['tenantUrl'] = '';
        $result->data = $data;
        return json($result);
    }

    /**
     * 租户续租
     */
    public function relet_tenant()
    {
        $params = $this->request->param();
        $result = new result();
        $result->code = 0;
        $result->data = null;
        if (!$params) {
            $result->msg =  '参数不能为空';
            return json($result);
        }
        if (!verifyBlueTronData(generateVerifyData($params), $params['sign'])) {
            $result->msg =  '签名校验不通过';
            return json($result);
        }

        $tenant = new Tenant();
        $tenant->save([
            'end_time'  => $params['endDate']
        ], ['tenant_id' => $params['tenantId']]);

        $result->code = 1;
        $result->msg =  '租户续租成功';
        $result->data = null;
        return json($result);
    }

    /**
     * 租户停止
     */
    public function stop_tenant()
    {
        $params = $this->request->param();
        $result = new result();
        $result->code = 0;
        $result->data = null;
        if (!$params) {
            $result->msg =  '参数不能为空';
        }
        if (!verifyBlueTronData(generateVerifyData($params), $params['sign'])) {
            $result->msg =  '签名校验不通过';
            return json($result);
        }

        $tenant = new Tenant();
        $tenant->save([
            'status'  => 0
        ], ['tenant_id' => $params['tenantId']]);

        $result->code = 1;
        $result->msg =  '租户停止成功';
        $result->data = null;
        return json($result);
    }
}
