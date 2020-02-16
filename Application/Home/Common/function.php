<?php

// +----------------------------------------------------------------------
// | 众包 - 微信小程序 接口公共方法
// +----------------------------------------------------------------------
// | 北京西岐网络科技有限公司 Copyright (c) 2015 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 絕丨情 <zhb_jut@sina.com>
// +----------------------------------------------------------------------

/**
 * 格式化输出数据 数组-json 
 * @params $data 输出session
 * @author 絕丨情 <zhb_jut@sina.com>
 * @time  17-3-29 下午5:34
 */
function xformatOutPutJsonData($status = 'success', $data = array(), $msg = '网络错误！') {
    echo json_encode(array('status' => $status, 'msg' => $msg, 'info' => $data));
    unset($data);
    exit;
}

/**
 * 读取用户banner 
 * @params string $type baner 类型,  string $field 查询字段
 * @author 絕丨情 <zhb_jut@sina.com>
 * @time  17-3-29 下午5:45
 */
function getAdvertList($type = NULL) {
    if (empty($type)) {
        return false;
    }
    return D('AdvertView')->getList($type);
}

/**
 * 腾讯云短信发送
 * @param int $mobile
 * @param string $code
 * @return boolean
 */
//function sendValidateCode($mobile, $code) {
//    if ($mobile == '' || $code == '') {
//        return false;
//    }
//
//    Vendor('QcloudSms.SmsSingleSender');
//    try {
//        $sender = new \SmsSingleSender(C('QCLOUD_SMS_APPID'), C('QCLOUD_SMS_APPKEY'));
//        $params = [$code];
//        $result = $sender->sendWithParam("86", $mobile, C('QCLOUD_SMS_TEMPLID'), $params, "", "", "");
//        $rsp = json_decode($result, true);
//        if ($rsp['result'] == 0) {
//            return true;
//        } else {
//            return false;
//        }
//    } catch (\Exception $e) {
//        return false;
//    }
//}

/**
 * 阿里大于短信发送
 * @param string $mobile
 * @param string $code
 * @return boolean
 */
//function sendValidateCode($mobile,$code) {
//    if ( $mobile == '' || $code=='' ) {
//        return false;
//    }
//    
//    include 'alidayu/TopSdk.php';
//    date_default_timezone_set('Asia/Shanghai');
//    $c = new TopClient;
//    $c->appkey = C('SMS_APPKEY');
//    $c->secretKey = C('SMS_SECRETKEY');
//    $req = new AlibabaAliqinFcSmsNumSendRequest;
//    $req->setSmsType("normal");
//    $req->setSmsFreeSignName("西岐");
//    $req->setSmsParam("{\"code\":\"" . $code . "\"}");
//    $req->setRecNum($mobile);
//    $req->setSmsTemplateCode('SMS_67940088');
//    $resp = $c->execute($req);
//
//    if ($resp->result->success) {
//        return true;
//    } else {
//        return false;
//    }
//}

/**
 * 阿里云短信发送
 * @param string $mobile
 * @param string $code
 * @return boolean
 */
function sendValidateCode($mobile, $code) {
    if ($mobile == '' || $code == '') {
        return false;
    }

    $sms_config = C('ALIYUN_SMS');

    Vendor('aliyun-dysms-php-sdk.api_sdk.vendor.autoload');

    // 加载区域结点配置
    Aliyun\Core\Config::load();
    // 初始化SendSmsRequest实例用于设置发送短信的参数
    $request = new Aliyun\Api\Sms\Request\V20170525\SendSmsRequest();
    //可选-启用https协议
    //$request->setProtocol("https");
    // 必填，设置短信接收号码
    $request->setPhoneNumbers($mobile);
    // 必填，设置签名名称
    $request->setSignName($sms_config['smsSign']);
    // 必填，设置模板CODE
    $request->setTemplateCode($sms_config['templateCode']);
    // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
    $request->setTemplateParam(json_encode(array(// 短信模板中字段的值
        "code" => $code
                    ), JSON_UNESCAPED_UNICODE));
    // 可选，设置流水号
    //$request->setOutId("yourOutId");
    // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
    //$request->setSmsUpExtendCode("1234567");

    //产品名称:云通信流量服务API产品,开发者无需替换
    $product = "Dysmsapi";
    //产品域名,开发者无需替换
    $domain = "dysmsapi.aliyuncs.com";
    // 暂时不支持多Region
    $region = "cn-hangzhou";
    // 服务结点
    $endPointName = "cn-hangzhou";
    //初始化acsClient,暂不支持region化
    $profile = Aliyun\Core\Profile\DefaultProfile::getProfile($region, $sms_config['accessKeyID'], $sms_config['accessKeySecret']);
    // 增加服务结点
    Aliyun\Core\Profile\DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
    // 初始化AcsClient用于发起请求
    $acsClient = new Aliyun\Core\DefaultAcsClient($profile);
    // 发起访问请求
    $acsResponse = $acsClient->getAcsResponse($request);
    
    if($acsResponse->Code == 'OK'){
        return true;
    }else{
        return false;
    }
}
