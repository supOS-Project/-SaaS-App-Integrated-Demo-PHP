<?php

use think\Config;

/**
 * $url 请求地址
 * $params 请求参数对象
 * $method 请求方法
 * $header 请求头
 * $multi 请求参数方式
 */
function http($url, $params, $method = 'GET', $header = array(), $multi = false)
{
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $header
    );
    switch (strtoupper($method)) {
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
    }

    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) exit('请求发生错误：' . $error);
    return $data;
}

/**
 * 生成唯一ID
 */
function  uuid()
{
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8)
        . substr($chars, 8, 4)
        . substr($chars, 12, 4)
        . substr($chars, 16, 4)
        . substr($chars, 20, 12);
    return $uuid;
}

/**
 * 生成验证的数据
 */
function generateVerifyData($params)
{
    ksort($params);
    $data = '';
    foreach ($params as $key => $value) {
        if ($key != 'sign') {
            $data .= "$key=$value&";
        }
    }
    return substr($data, 0, strlen($data) - 1);
}

/**
 * 验签
 * @param data 原始字符串
 * @param sign 签名
 * @param publicKey 公钥
 * @return 是否验签通过
 */
function verifyBlueTronData($data, $sign)
{
    $publicKey = Config::get('blueTronConfig.publicKey');
    $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
        wordwrap($publicKey, 64, "\n", true) .
        "\n-----END PUBLIC KEY-----";
    $publicKey = openssl_get_publickey($publicKey);
    $result = openssl_verify($data,base64_decode($sign), $publicKey, 'SHA256');
    return ($result == 1) ? true : false;
}

/**
 * 生成蓝卓签名
 * @param string $httpUri
 * @param string $canonicalQueryString
 * @param string $appid
 * @param string $secret
 * @return string
 */
function generateBlueTronSign($httpUri = '', $canonicalQueryString = '', $appid = '', $secret = '')
{
    $xMcDate = date('Ymd') . 'T' . date('Hms') . 'Z';
    $HTTPSchema = 'GET';
    $HTTPURI = $httpUri;
    $HTTPContentType = 'application/json;charset=utf-8';
    $CanonicalQueryString = $canonicalQueryString;
    $CanonicalCustomHeaders = "x-mc-date:$xMcDate;x-mc-type:openAPI";

    $CanonicalRequestString = $HTTPSchema . "\n" .
        $HTTPURI . "\n" .
        $HTTPContentType . "\n" .
        $CanonicalQueryString . "\n" .
        $CanonicalCustomHeaders;

    $sig = hash_hmac('sha256', $CanonicalRequestString, $secret);

    return $appid . '-' . $sig;
}

/**
 * 获取httpUri和canonicalQueryString
 * @param string $type 区分获取的接口
 * @param string $code 获取特定的值需要的参数 例如 某公司的部门列表
 * @param string $region
 * @param string $instance_name
 * @return array
 */
function getHttpUriCanonicalQueryString($type = '', $code = '', $region = '', $instance_name = '')
{
    $return = [
        'httpUri' => '',
        'canonicalQueryString' => '',
    ];
    if (empty($type)) {
        return $return;
    }
    switch ($type) {
        case 'department': //部门列表
            $return['httpUri'] = '/ess-gate/' . $region . '/' . $instance_name . '/open-api/organization/v2/departments';
            $return['canonicalQueryString'] = 'current=1&pageSize=500';
            break;
        case 'companies': //公司列表
            $return['httpUri'] = '/ess-gate/' . $region . '/' . $instance_name . '/open-api/organization/v2/companies';
            $return['canonicalQueryString'] = 'current=1&pageSize=500';
            break;
        case 'positions': //岗位列表
            $return['httpUri'] = '/ess-gate/' . $region . '/' . $instance_name . '/open-api/organization/v2/positions';
            $return['canonicalQueryString'] = 'current=1&pageSize=500';
            break;
        case 'persons': //人员列表
            $return['httpUri'] = '/ess-gate/' . $region . '/' . $instance_name . '/open-api/organization/v2/persons';
            $return['canonicalQueryString'] = 'current=1&pageSize=500';
            break;
        case 'companies_department': //特定编码的公司的部门列表
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/companies/$code/departments";
            $return['canonicalQueryString'] = "companyCode=$code&current=1&pageSize=500";
            break;
        case 'companies_positions': //特定编码的公司的岗位列表
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/companies/$code/positions";
            $return['canonicalQueryString'] = "companyCode=$code&current=1&pageSize=500";
            break;
        case 'persons_view': //指定编号的人员详情
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/persons/$code";
            $return['canonicalQueryString'] = "personCode=$code";
            break;
        case 'persons_user_name': //指定用户详情
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/auth/v2/users/$code";
            $return['canonicalQueryString'] = "username=$code";
            break;
        case 'companies_view': //指定编码的公司详情
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/companies/$code";
            $return['canonicalQueryString'] = "companyCode=$code";
            break;
        case 'companies_persons': //指定编码的公司的人员列表
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/companies/$code/persons";
            $return['canonicalQueryString'] = "companyCode=$code&current=1&pageSize=500";
            break;
        case 'positions_view': //指定编码的岗位详情
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/positions/$code";
            $return['canonicalQueryString'] = "positionCode=$code";
            break;
        case 'positions_persons': //指定编码的岗位的人员列表
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/positions/$code/persons";
            $return['canonicalQueryString'] = "positionCode=$code&current=1&pageSize=500";
            break;
        case 'departments_view': //指定编码的部门详情
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/departments/$code";
            $return['canonicalQueryString'] = "departmentCode=$code";
            break;
        case 'departments_persons': //指定编码的部门的人员列表
            $return['httpUri'] = "/ess-gate/" . $region . "/" . $instance_name . "/open-api/organization/v2/departments/$code/persons";
            $return['canonicalQueryString'] = "departmentCode=$code&current=1&pageSize=500";
            break;
    }
    return $return;
}
