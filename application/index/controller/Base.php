<?php

namespace app\index\controller;

use think\Controller;
use think\Cache;
use think\Config;
use app\index\model\Tenant;

class Base extends Controller
{
    //构造
    public function _initialize()
    {
        $sessionId = session_id();
        //获取缓存中的认证信息
        $authInfo = Cache::get($sessionId);
        //判断认证信息是否存在，如果不存在则需要去认证
        if (!$authInfo) {
            $this->redirectAuth();
        } else {
            //如果缓存存在判断是否即将过期
            if (!Cache::get("expiresIn")) {
                $this->refreshToken($sessionId, $authInfo);
            }
        }
    }

    /**
     * 重定向
     */
    private function redirectAuth()
    {
        $tenant = new Tenant();
        $query = $this->request->param();
        $tenantInfo = null;
        if ($query) {
            $tenantInfo = $tenant->where('tenant_id', $query['tenantId'])->find();
        } else {
            $tenantInfo = $tenant->find();
        }
        $scope =  'openid,' . $tenantInfo['region'] . ',' . $tenantInfo['instance_name'];
        //未登录的情况下直接重定向
        $url = Config::get('sysCofig.app_url') . url('bluetron/index') . "?tenantId=" . $tenantInfo['tenant_id'];
        $authorizeUrl = Config::get('blueTronConfig.auth_url')
            . 'oauth/authorize?responseType=code&appid='
            . Config::get('blueTronConfig.appId')
            . '&redirectUri=' . $url
            . '&state=1&scope=' . $scope;
        dump($authorizeUrl);
        //302跳转到认证地址进行auth的认证
        $this->redirect($authorizeUrl, 302);
    }

    /**
     * 刷新token
     * $sessionId sessionID
     * $authInfo 缓存的登录信息
     */
    private function refreshToken($sessionId, $authInfo)
    {
        //组装oauth的刷新地址
        $refreshUrl = Config::get('blueTronConfig.auth_url') . 'oauth/refreshToken';
        $params['refreshToken'] = $authInfo['accessToken'];
        $data = http($refreshUrl, $params, 'GET');
        if ($data) {
            $data = json_decode($data);
            if ($data->code != 1 && $data->code !='1') {
                $this->redirectAuth();
            } else {
                $authInfo['accessToken'] = $data->data->accessToken;
                $authInfo['expiresIn'] = time();
                Cache::set("expiresIn", $data->data->expiresIn, $data->data->expiresIn);
                $authInfo['refreshToken'] = $data->data->refreshToken;
                Cache::set($sessionId, $authInfo);
            }
        } else {
            exit('刷新蓝卓accessToken失败');
        }
    }
}
