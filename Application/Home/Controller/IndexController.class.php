<?php

namespace Home\Controller;

use Common\Controller\HomeBaseController;

/**
 * 首页Controller
 */
class IndexController extends HomeBaseController {
    /*
     * 登录
     */
	// public function index(){
    //     $data = '宋立军';
    //     $url = "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIE9pgmv7ZGibPv4yqyGmbibg22YjmbBMK1wDGaSGX5CxF77hUXq680nzlOe6UH9RTaN40VMtzkWiabA/132";
    //     $file = file_get_contents($url);
    //     $filename = './Public/Avatar/' .$data. '.jpg';
    //     //dump($filename);
        
    //     $im = file_put_contents($filename, $file);
    //     dump($im);
    //     return $im;
    // }
    public function getopenid() {
        $data = I('post.');
        if($data['openid']){
            $map['openid'] = array('eq', $data['openid']);
            $wx_member = M('user')->field('id,openid,name,avatar,is_match,phone,lon,lat,address as pos')->where($map)->find();
            if($wx_member){
                if(!$wx_member['avatar']||$wx_member['avatar']==''){
                    $file = file_get_contents($data['avatar']);
                    $filename = './Public/Avatar/0_' .$openid. '.jpg';
                    $im = file_put_contents($filename, $file);
                    if($im){
                        $update['avatar'] = str_replace('http','https',IMG_PATH). '/Public/Avatar/0_'.$openid. '.jpg';
                    }
                }
                $update['last_sign'] = date('Y-m-d', time());
                M('user')->where('id='.$wx_member['id'])->save($update);
                $wx_member['userToken'] = S('user_' . $wx_member['id']);
                // S("landlord".$openid, $wx_member);
                //获取我的位置
                // if ($wx_member['is_place'] == 1) {
                //     $myPosition = M('circles')->field('city_name as city,lon,lat,area_name,name')->where('id='. $wx_member['shangquan'])->find();
                //     $wx_member['lon'] = $userinfo['lon'];
                //     $wx_member['lat'] = $userinfo['lat'];
                //     $wx_member['pos'] = $myPosition['area_name'].$myPosition['name'];
                // }else if ($wx_member['is_place'] == 2) {
                //     $myPosition = M('subways')->field('city,lon,lat,line,station')->where('id='. $wx_member['ditie'])->find();
                //     $wx_member['lon'] = $userinfo['lon'];
                //     $wx_member['lat'] = $userinfo['lat'];
                //     $wx_member['pos'] = $myPosition['line'].$myPosition['station'];
                // }
                $wx_member['nickname'] = base64_decode($wx_member['name']);
                // $wx_member['session_key'] = S($openid . '_key');
                xformatOutPutJsonData('success', $wx_member, '');
            }else{
                xformatOutPutJsonData('fail', 1, '注册失败，请联系客服');
            }
        }
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wx8f2ed65c8aee3563&secret=4f2e7944beac651f1b5d49dd53e36328&js_code=' . $data['code'] . '&grant_type=authorization_code';
        $result = file_get_contents($url);
        $result_arr = json_decode($result, TRUE);
        //xformatOutPutJsonData('success', $result_arr, '');
        $openid = $result_arr['openid'];
        S($openid . '_key', $result_arr['session_key']);
        if ($openid != '') {
            $map = array();
            $map['openid'] = array('eq', $openid);
//            $map['is_login'] = array('eq', 1);
            $wx_member = M('user')->field('id,openid,name,avatar,is_match,phone,lon,lat,address as pos')->where($map)->find();
            if ($wx_member == false) {
                $file = file_get_contents($data['avatar']);
                $filename = './Public/Avatar/0_' .$openid. '.jpg';
                $im = file_put_contents($filename, $file);
                if($im)
                    $data['avatar'] = str_replace('http','https',IMG_PATH). '/Public/Avatar/0_'.$openid. '.jpg';
                $inserData = array(
                    'openid' => $openid,
                    //'nickname' => $data['nickname'],
                    'name' => base64_encode($data['nickname']),
                    'avatar' => $data['avatar'],
                    'gender' => $data['gender'],
                    'sex' => $data['gender'],
                    'province' => $data['province'],
                    'city' => $data['city'],
//                    'is_login' => 0,
                );
                $userid = M('user')->add($inserData);
                //尝试写入明文nickname
                $update['nickname'] = $data['nickname'];
                M('user')->where('id='.$userid)->save($update);
                
                $inserData['id'] = $userid;
                $inserData['nickname'] = $data['nickname'];
                $userToken = $this->getTokenCode($userid,"roomer");
                $inserData['userToken'] = $userToken;
                $update['userToken'] = $userToken;
                $update['last_sign'] = date('Y-m-d', time());
                M('user')->where('id='.$userid)->save($update);
                // S($openid, $inserData);
                xformatOutPutJsonData('success', $inserData, '');
            } else {
//                $user['nickname'] = $data['nickname'];
//                $user['name'] = base64_encode($data['nickname']);
//                $user['avatar'] = $data['avatar'];
//                M('user')->where('id=' . $wx_member['id'])->save($user);
                if(!$wx_member['avatar']||$wx_member['avatar']==''){
                    $file = file_get_contents($data['avatar']);
                    $filename = './Public/Avatar/0_' .$openid. '.jpg';
                    $im = file_put_contents($filename, $file);
                    if($im){
                        $update['avatar'] = str_replace('http','https',IMG_PATH). '/Public/Avatar/0_'.$openid. '.jpg';
                        // M('user')->where('id='.$wx_member['id'])->save($update);
                    }
                }
                $update['last_sign'] = date('Y-m-d', time());
                M('user')->where('id='.$wx_member['id'])->save($update);
                $wx_member['userToken'] = S('user_' . $wx_member['id']);
                $wx_member['nickname'] = base64_decode($wx_member['name']);
                //获取我的位置
                // if ($wx_member['is_place'] == 1) {
                //     $myPosition = M('circles')->field('city_name as city,lon,lat,area_name,name')->where('id='. $wx_member['shangquan'])->find();
                //     $wx_member['lon'] = $userinfo['lon'];
                //     $wx_member['lat'] = $userinfo['lat'];
                //     $wx_member['pos'] = $myPosition['area_name'].$myPosition['name'];
                // }else if ($wx_member['is_place'] == 2) {
                //     $myPosition = M('subways')->field('city,lon,lat,line,station')->where('id='. $wx_member['ditie'])->find();
                //     $wx_member['lon'] = $userinfo['lon'];
                //     $wx_member['lat'] = $userinfo['lat'];
                //     $wx_member['pos'] = $myPosition['line'].$myPosition['station'];
                // }
                // unset($wx_member['is_place']);
                // unset($wx_member['shangquan']);
                // unset($wx_member['ditie']);
                // S($openid, $wx_member);
                xformatOutPutJsonData('success', $wx_member, '');
            }
        }
        xformatOutPutJsonData('fail', '', '网络错误123！');
    }
//     public function getLandlordOpenid(){
//         $data = I('post.');
//         if($data['openid']){
//             $map['openid'] = array('eq', $data['openid']);
//             $wx_member = M('landlord')->field('id,openid,name,avatar,is_match,phone')->where($map)->find();
//             //如果之前没存好图片则重新存一下
//             if(!$wx_member['avatar']||$wx_member['avatar']==''){
//                 $file = file_get_contents($data['avatar']);
//                 $filename = './Public/LandlordAvatar/0_' .$openid. '.jpg';
//                 $im = file_put_contents($filename, $file);
//                 if($im){
//                     $update['avatar'] = str_replace('http','https',IMG_PATH). '/Public/LandlordAvatar/0_'.$openid. '.jpg';
//                     M('landlord')->where('id='.$wx_member['id'])->save($update);
//                 }
//             }
//             $wx_member['userToken'] = S('user_landlord' . $wx_member['id']);
//             // S("landlord".$openid, $wx_member);
//             xformatOutPutJsonData('success', $wx_member, '');
//         }
//         $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wx8f2ed65c8aee3563&secret=4f2e7944beac651f1b5d49dd53e36328&js_code=' . $data['code'] . '&grant_type=authorization_code';
//         $result = file_get_contents($url);
//         $result_arr = json_decode($result, TRUE);
//         $openid = $result_arr['openid'];
//         S($openid . '_landlord', $result_arr['session_key']);
//         if ($openid != '') {
//             $map = array();
//             $map['openid'] = array('eq', $openid);
// //            $map['is_login'] = array('eq', 1);
//             $wx_member = M('landlord')->field('id,openid,name,avatar,is_match,phone')->where($map)->find();
//             if ($wx_member == false) {
//                 $file = file_get_contents($data['avatar']);
//                 $filename = './Public/LandlordAvatar/0_' .$openid. '.jpg';
//                 $im = file_put_contents($filename, $file);
//                 if($im)
//                     $data['avatar'] = str_replace('http','https',IMG_PATH). '/Public/LandlordAvatar/0_'.$openid. '.jpg';
//                 $inserData = array(
//                     'openid' => $openid,
//                     //'nickname' => $data['nickname'],
//                     'name' => $data['name'],
//                     'avatar' => $data['avatar'],
//                     'gender' => $data['gender'],
//                     'province' => $data['province'],
//                     'city' => $data['city'],
// //                    'is_login' => 0,
//                 );
//                 $userid = M('landlord')->add($inserData);
                
//                 $inserData['id'] = $userid;
//                 $userToken = $this->getTokenCode("landlord".$userid);
//                 $inserData['userToken'] = $userToken;
//                 $update['userToken'] = $userToken;
//                 M('landlord')->where('id='.$userid)->save($update);
//                 xformatOutPutJsonData('success', $inserData, '');
//                 // S("landlord".$openid, $inserData);
//             } else {
//                 //如果之前没存好图片则重新存一下
//                 if(!$wx_member['avatar']||$wx_member['avatar']==''){
//                     $file = file_get_contents($data['avatar']);
//                     $filename = './Public/LandlordAvatar/0_' .$openid. '.jpg';
//                     $im = file_put_contents($filename, $file);
//                     if($im){
//                         $update['avatar'] = str_replace('http','https',IMG_PATH). '/Public/LandlordAvatar/0_'.$openid. '.jpg';
//                         M('landlord')->where('id='.$wx_member['id'])->save($update);
//                     }
//                 }
//                 $wx_member['userToken'] = S('user_landlord' . $wx_member['id']);
//                 // S("landlord".$openid, $wx_member);
//                 xformatOutPutJsonData('success', $wx_member, 'test20190330');
//             }
//         }
//         xformatOutPutJsonData('fail', '', '网络错误123！');
//     }


    
    /**
     * 海外地址解析
     * @param string address
     * @param string region 所在城市 例如'Berlin'
    */
    public function overseaGeocoder(){
        $Data = I('get.');
        $address = $Data['address'];
        $region = $Data['region'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://apis.map.qq.com/ws/geocoder/v1/?address=". urlencode($address) . "&region=" . urlencode($region)."&key=" .urlencode("CFDBZ-GXW6U-CGSVR-2RA4C-4R5A3-L6BB3")."&oversea=".urlencode(1)."&language=".urlencode("cn"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        //$url = "https://apis.map.qq.com/ws/geocoder/v1?address=" . $address . "&region=" . $region."&key=CFDBZ-GXW6U-CGSVR-2RA4C-4R5A3-L6BB3&oversea=1&language=cn";
        // xformatOutPutJsonData('success', $address, $url);
        //$url = stripslashes($url);//删除自动添加的反斜杠
        //xformatOutPutJsonData('test', stripslashes($url), $url);
        //$res = A('Home/Chat')->request_get(rawurlencode($url));
        //dump($res);
        xformatOutPutJsonData('success', $data, "");
    }


//修改用户性别
    public function getsex() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $res = M('user')->where('id=' . $uid)->save($data);
        if ($res == FALSE) {
            xformatOutPutJsonData('fail', '', '网络错误3！');
        }
    }

//修改用户性别
    public function getRoomsex() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $roommate = M('user_primary_matching')->where('uid=' . $uid)->find();
        if ($roommate) {
            $map['sex'] = $data['sex'];
            $res = M('user_primary_matching')->where('uid=' . $uid)->save($map);
            if ($res == FALSE) {
                xformatOutPutJsonData('fail', '', '网络错误3！');
            }
        } else {
            $map['sex'] = $data['sex'];
            $map['uid'] = $data['id'];
            M('user_primary_matching')->add($map);
        }
    }

    //获取生活习惯
    public function getxiguan() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $map['a.id'] = $data['id'];
        $data = M('user')->alias('a')
                ->field('a.pet,a.smoking,a.bedlate,a.lovers,b.pet as b_pet,b.smoking as b_smoking,b.bedlate as b_bedlate,b.lovers as b_lovers')
                ->join('LEFT JOIN xqwl_user_primary_matching b ON a.id =b.uid')
                ->where($map)
                ->find();
        xformatOutPutJsonData('success', $data, "M()->getLastSql()");
    }
    //修改生活习惯
        public function getxigaun() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //dump($data);
        switch ($data['types']) {
            case 0:
                $map['pet'] = $data['vau'];
                break;
            case 1:
                $map['smoking'] = $data['vau'];
                break;
            case 2:
                $map['bedlate'] = $data['vau'];
                break;
            case 3:
                $map['lovers'] = $data['vau'];
                break;
        }
        if ($data['status'] == 1) {
            $res = M('user')->where('id=' . $uid)->save($map);
        } else {
            $res = M('user_primary_matching')->where('uid=' . $uid)->save($map);
        }
        //dump(M()->getLastSql());
        if ($res === FALSE) {
            xformatOutPutJsonData('fail', '', '网络错误3！');
        }
    }

//获取租住时长
    public function getTenantLong() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('test', $data['token'], S('user_' . $data['id']));
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $res['data'] = M('tenant_long')->field('id,name')->select();
        foreach ($res['data'] as $k => $v) {
            $name[] = $v['name'];
        }
        $res['name'] = $name;
        $user = M('user')->field('checkintime,checkoutime,tenant_long')->where('id=' . $uid)->find();
        if (!empty($res)) {
            xformatOutPutJsonData('success', $res, $user);
        }
    }

//存储
    public function setTenantLong() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if(empty(strtotime($data['checkintime'])) || strtotime($data['checkintime'])<0){
            xformatOutPutJsonData('error', 1, '最早入住时间格式错误');
        }
        if(empty(strtotime($data['checkoutime'])) || strtotime($data['checkoutime'])<0){
            xformatOutPutJsonData('error', 1, '最晚入住时间格式错误');
        }
        if(strtotime($data['checkintime']) > strtotime($data['checkoutime'])){
            xformatOutPutJsonData('error', 1, '最晚入住时间不能小于最早入住时间');
        }
        $map['checkintime'] = $data['checkintime'];
        $map['checkoutime'] = $data['checkoutime'];
        $map['tenant_long'] = $data['Tenant'];
        $map['is_match'] = 1;
        $res = M('user')->where('id=' . $uid)->save($map);
        if ($res !== FALSE) {
            xformatOutPutJsonData('success', $res, '');
        }
    }

//获取商圈
    public function getShangquan() {
        $Data = I('get.');
        $uid = $Data['id'];
        $city = $Data['city'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if($city==""){
            $wx_member = M('user')->field('is_place,shangquan,ditie')->where('id='.$uid)->find();
            //获取我的位置
            if ($wx_member['is_place'] == 1) {
                $myPosition = M('circles')->field('city_name')->where('id='. $wx_member['shangquan'])->find();
                $wx_member['city'] = $myPosition['city_name'];
                $city =  $myPosition['city_name'];
            }else if ($wx_member['is_place'] == 2) {
                $myPosition = M('subways')->field('city')->where('id='. $wx_member['ditie'])->find();
                $wx_member['city']  = $myPosition['city'];
                $city =  $myPosition['city'];
            }
        }
        //商圈
        $data['shangquan'] = M('circles')->field('area_name as name')->where('city_name="' . $city .'"')->group('area_name')->select();
        foreach ($data['shangquan'] as $k => $v) {
//            if ($k == 0) {
//                $data['shangquan'][$k]['Class'] = 'bac';
//                $data['shangquan'][$k]['Img'] = '../image/xiangyou.png';
//                $data['shangquan'][$k]['status'] = 'shangquan';
//                $data['shangquan'][$k]['id'] = $k + 1;
//            } else {

                // $data['shangquan'][$k]['Class'] = '';
                // $data['shangquan'][$k]['Img'] = '../image/xiangyou_h.png';
                $data['shangquan'][$k]['status'] = 'shangquan';
                $data['shangquan'][$k]['id'] = $k + 1;
                
//            }
            $shangquan[] = M('circles')->field('id as infoid,name')->where('city_name="' . $city .'" AND area_name="' . $v['name'].'"')->select();
        }
        foreach ($shangquan as $key => $value) {
            foreach ($value as $a => $b) {
//                    if ($a == 0) {
//                        $shangquan[$key][$a]['Class'] = 'bac';
//                        $shangquan[$key][$a]['id'] = $a + 1;
//                    } else {

                    // $shangquan[$key][$a]['Class'] = '';
                    $shangquan[$key][$a]['id'] = $a + 1;
//                    }
            }
        }
        //地铁
        $data['ditie'] = M('subways')->field('DISTINCT line')->where('city="' . $city .'"')->select();
        foreach ($data['ditie'] as $k => $v) {
//            if ($k == 0) {
//                $data['ditie'][$k]['Class'] = 'bac';
//                $data['ditie'][$k]['Img'] = '../image/xiangyou.png';
//                $data['ditie'][$k]['status'] = 'ditie';
//                $data['ditie'][$k]['name'] = $v['title'];
//                $data['ditie'][$k]['id'] = $k + 1;
//            } else {

                // $data['ditie'][$k]['Class'] = '';
                // $data['ditie'][$k]['Img'] = '../image/xiangyou_h.png';
                $data['ditie'][$k]['status'] = 'ditie';
                $data['ditie'][$k]['name'] = $v['title'];
                $data['ditie'][$k]['id'] = $k + 1;
//            }
            $ditie[] = M('subways')->field('id as infoid,station')->where('city="' . $city .'" AND line="' . $v['line'].'"')->select();   
        }
        foreach ($ditie as $key => $value) {
            foreach ($value as $a => $b) {
//                    if ($a == 0) {
//                        $ditie[$key][$a]['Class'] = 'bac';
//                        $ditie[$key][$a]['name'] = $b['title'];
//                        $ditie[$key][$a]['id'] = $a + 1;
//                    } else {
                    $ditie[$key][$a]['name'] = $b['title'];
                    // $ditie[$key][$a]['Class'] = '';
                    $ditie[$key][$a]['id'] = $a + 1;
//                    }
            }
        }
        $res['data'][0] = $data;
        $res['zisq'][0]['shangquan'][0]['num'] = 0;
        $res['zisq'][0]['shangquan'][0]['xia'] = $shangquan;
        $res['zisq'][0]['ditie'][0]['num'] = 0;
        $res['zisq'][0]['ditie'][0]['xia'] = $ditie;
        $res['addid'] = '';
        $res['ditie'] = '';
//        $res['addid'] = $shangquan[0][0]['infoid'];
//        $res['ditie'] = $ditie[0][0]['infoid'];
//        var_dump($res);die;
        if (!empty($res)) {
            xformatOutPutJsonData('success', $res, $wx_member);
        }
    }
//编辑租住地点
//     public function editShangquan() {
//         $Data = I('get.');
//         $uid = $Data['id'];
//         if (empty($uid)) {
//             xformatOutPutJsonData('fail', '', '网络错误1！');
//         }
//         if ($Data['token'] !== S('user_' . $Data['id'])) {
//             xformatOutPutJsonData('fail', '', '网络错误2！');
//         }
//         //获取用户信息
//         $userinfo=M('user')->field('shangquan,ditie,is_place')->where('id='.$uid)->find();
//         if(!empty($userinfo['shangquan'])){
//             //获取用户商圈上级
//             $trading=M('trading')->field('pid')->where('id='.$userinfo['shangquan'])->find();
//         }
//         if(!empty($userinfo['ditie'])){
//             //获取用户地铁上级
//             $metro=M('metro')->field('pid')->where('id='.$userinfo['ditie'])->find();
//         }
//         //商圈
//         $data['shangquan'] = M('trading')->field('id,name')->where('pid=0')->select();
//         foreach ($data['shangquan'] as $k => $v) {
//             //默认选中样式
// //                if($v['id']==$trading['pid']){
// //                    $data['shangquan'][$k]['Class'] = 'bac';
// //                    $data['shangquan'][$k]['Img'] = '../image/xiangyou.png';
// //                }else{
//                      $data['shangquan'][$k]['Class'] = '';
//                      $data['shangquan'][$k]['Img'] = '../image/xiangyou_h.png';
// //                }
//                 $data['shangquan'][$k]['status'] = 'shangquan';
//                 $data['shangquan'][$k]['id'] = $k + 1;
//             $shangquan[] = M('trading')->field('id as infoid,name')->where('pid=' . $v['id'])->select();
//             foreach ($shangquan as $key => $value) {
//                 foreach ($value as $a => $b) {
//                     //默认选中样式
// //                    if($b['infoid']==$userinfo['shangquan']){
// //                        $shangquan[$key][$a]['Class'] = 'bac';
// //                    }else{
//                         $shangquan[$key][$a]['Class'] = '';
// //                    }
//                         $shangquan[$key][$a]['id'] = $a + 1;
//                 }
//             }
//         }
//         //地铁
//         $data['ditie'] = M('metro')->field('id,title')->where('pid=0')->select();
//         foreach ($data['ditie'] as $k => $v) {
//             //默认选中样式
// //            if($v['id']==$metro['pid']){
// //                $data['ditie'][$k]['Class'] = 'bac';
// //                $data['ditie'][$k]['Img'] = '../image/xiangyou.png';
// //            }else{
//                 $data['ditie'][$k]['Class'] = '';
//                 $data['ditie'][$k]['Img'] = '../image/xiangyou_h.png';
// //            }
//                 $data['ditie'][$k]['status'] = 'ditie';
//                 $data['ditie'][$k]['name'] = $v['title'];
//                 $data['ditie'][$k]['id'] = $k + 1;
//             $ditie[] = M('metro')->field('id as infoid,title')->where('pid=' . $v['id'])->select();
//             foreach ($ditie as $key => $value) {
//                 foreach ($value as $a => $b) {
//                     //默认选中样式
// //                    if($b['infoid']==$userinfo['ditie']){
// //                        $ditie[$key][$a]['Class'] = 'bac';
// //                    }else{
//                         $ditie[$key][$a]['Class'] = '';
// //                    }
//                         $ditie[$key][$a]['name'] = $b['title'];
//                         $ditie[$key][$a]['id'] = $a + 1;
//                 }
//             }
//         }
// //        $type=array();
// //        $type[0]['id']=1;
// //        $type[0]['name']='地铁';
// //        $type[0]['Class']='wo';
// //        if(intval($userinfo['is_place'])==1){
// //            $type[0]['status']='no';
// //            $type[0]['color']='#333333';
// //            $type[0]['imgSRC']='';
// //        }else{
// //            $type[0]['status']='active';
// //            $type[0]['color']='#fc3a6e';
// //            $type[0]['imgSRC']='../image/xuanxuan.png';
// //        }
// //        $type[1]['id']=2;
// //        $type[1]['name']='商圈';
// //        $type[1]['Class']='shiyou';
// //        if(intval($userinfo['is_place'])==2){
// //            $type[1]['status']='no';
// //            $type[1]['color']='#333333';
// //            $type[1]['imgSRC']='';
// //        }else{
// //            $type[1]['status']='active';
// //            $type[1]['color']='#fc3a6e';
// //            $type[1]['imgSRC']='../image/xuanxuan.png';
// //        }
                
//         $res['data'][0] = $data;
//         $res['zisq'][0]['shangquan'][0]['num'] = 0;
//         $res['zisq'][0]['shangquan'][0]['xia'] = $shangquan;
//         $res['zisq'][0]['ditie'][0]['num'] = 0;
//         $res['zisq'][0]['ditie'][0]['xia'] = $ditie;
//         //默认id
//         $res['addid'] = '';
//         $res['ditie'] = '';
// //        $res['type'] = $type;
        
//         if (!empty($res)) {
//             xformatOutPutJsonData('success', $res, '');
//         }
//     }

//保存租住地点
    public function editAddress() {
        $Data = I('post.');
        $uid = $Data['id'];
//        $uid = 363;
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $map['city_no'] = $Data['city'];
        $map['lon'] = $Data['lon'];
        $map['lat'] = $Data['lat'];
        $map['address'] = $Data['address'];
        $map['is_match'] = $Data['is_match'];
        // if ($Data['status'] == 'shangquan') {
        //     $map['shangquan'] = $Data['addid'];
        //     $map['is_place'] = 1;
        //     $myPosition = M('circles')->field('area_name,name,lon,lat')->where('id='. $Data['addid'])->find();
        //     $myPosition['name'] = $myPosition['area_name'].$myPosition['name'];
        //     unset($myPosition['area_name']);
        // } else {
        //     $map['ditie'] = $Data['addid'];
        //     $map['is_place'] = 2; //当前用户选择的是地铁 默认是商圈
        //     $myPosition = M('subways')->field('line,station,lon,lat')->where('id='. $Data['addid'])->find();
        //     $myPosition['name'] = $myPosition['line'].$myPosition['station'];
        //     unset($myPosition['line']);
        //     unset($myPosition['station']);
        // }
        // $info = M('user')->field('is_match')->where('id=' . $uid)->find();
        // if($info['is_match']==0){
        //     $map['is_match'] = 1;
        // }
        $res = M('user')->where('id=' . $uid)->save($map);
        if ($res !== FALSE) {
            xformatOutPutJsonData('success', $res, '');
        }
    }

//初级匹配
//     public function primaryMatch() {
//         $Data = I('get.');
//         $uid = $Data['id'];
// //        $uid = 782;
//         if (empty($uid)) {
//             xformatOutPutJsonData('fail', '', '网络错误1！');
//         }
//         if ($Data['token'] !== S('user_' . $uid)) {
//             xformatOutPutJsonData('fail', '', '网络错误2！');
//         }
//         //规则分数
//         $corresponding = $this->getMatchNum();
//         //获取用户想找的室友
//         $userpm = M('user_primary_matching')->field('sex,pet,smoking,bedlate,lovers')->where('uid=' . $uid)->find();
//         //当前用户基本信息
//         $userinfo = M('user')->where('id=' . $uid)->find();
//         //符合初级匹配条件的用户
//         if($userpm['sex']!=3)
//             $map['a.sex'] = $userpm['sex'];
//         if($userpm['pet']==0)
//             $map['a.pet'] = 0;
//         if($userpm['smoking']==0)
//             $map['a.smoking'] = 0;
//         if($userpm['bedlate']==0)
//             $map['a.bedlate'] = 0;
//         if($userpm['lovers']==0)
//             $map['a.lovers'] = 0;
// //        $map['a.id'] != $uid;
//         $map['a.id'] = array('neq', $uid);
//         $mymatchuser = M('user')->alias('a')
//                 ->field('a.*,b.sex as b_sex,b.pet as b_pet,b.smoking as b_smoking,b.bedlate as b_bedlate,b.lovers as b_lovers')
//                 ->join('LEFT JOIN xqwl_user_primary_matching b ON a.id =b.uid')
//                 ->where($map)
//                 ->select();
//         //var_dump(M()->getLastSql());
//         //删除那些我不满足对方需求的结果
//         foreach ($mymatchuser as $key => $value) {
//             if(($value['b_sex']==3||$value['b_sex']==$userinfo['sex'])//性别匹配
//                 &&($value['b_pet']==1||$value['b_pet']==0)//匹配宠物
//                 &&($value['b_smoking']==1||$value['b_smoking']==0)//匹配抽烟
//                 &&($value['b_bedlate']==1||$value['b_bedlate']==0)//匹配晚睡
//                 &&($value['b_lovers']==1||$value['b_lovers']==0))//匹配情侣
//                 $matchuser[] = $value;
//         }
//         //dump($matchuser);

//         //获取我的位置
//         if ($userinfo['is_place'] == 1) {
//             $myPosition = M('circles')->field('city_name as city,lon,lat')->where('id='. $userinfo['shangquan'])->find();
//         }else{
//             $myPosition = M('subways')->field('city,lon,lat')->where('id='. $userinfo['ditie'])->find();
//         }
//         //设置不同城市权重
//         $PosWeight = M('city')->field('city_score')->where('c_name="'.$myPosition['city'].'"')->find();

//         foreach ($matchuser as $key => $value) {
//             $fraction = 0;
//             if ($value['is_place'] == 1) {
//                 $hisPosition = M('circles')->field('city_name as city,lon,lat')->where('id='. $value['shangquan'])->find();
//             }else{
//                 $hisPosition = M('subways')->field('city,lon,lat')->where('id='. $value['ditie'])->find();
//             }

//             //租住城市不一致
//             if($myPosition['city']!=$hisPosition['city'])
//                 continue;
//             $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
           
//             //10公里以内分数为正，10公里以外分数为负
//             $fraction = bcmul(10-$distance, $PosWeight['city_score'])/10;
//             // if($fraction<-150){
//             //     $fraction=-150;
//             // }
//             // if($value['id']==1877)
//             //     xformatOutPutJsonData($fraction,$hisPosition, $myPosition);
//             //我的最早入住时间
//             $u_longtime = strtotime($userinfo['checkintime']);
//             //我的最晚入住时间
//             $u_nighttime = strtotime($userinfo['checkoutime']);
//             //匹配最早入住时间
//             $p_longtime = strtotime($value['checkintime']);
//             //匹配最晚入住时间
//             $p_nighttime = strtotime($value['checkoutime']);
//             //计算交集 先计算交集是否大于7天或者小于7天 满足这俩条件的任何一个在进行匹配租房时长
//             if ($u_longtime < $p_longtime && $u_nighttime <= $p_nighttime) {
//                 $time = $this->gettimeDifference($u_nighttime, $p_longtime);
//                 if ($time < 7 && $time > 0) {
// //                    $fraction+= 30;
//                     $fraction+= $corresponding['enancytime'];
//                 } elseif ($time >= 7) {
// //                    $fraction+= 50;
//                     $fraction+= $corresponding['q_enancytime'];
//                 }
//             } elseif ($u_longtime > $p_longtime && $u_nighttime >= $p_nighttime) {
//                 $time = $this->gettimeDifference($p_nighttime, $u_longtime);
//                 if ($time < 7 && $time > 0) {
// //                    $fraction+= 30;
//                     $fraction+= $corresponding['enancytime'];
//                 } elseif ($time >= 7) {
// //                    $fraction+= 50;
//                     $fraction+=$corresponding['q_enancytime'];
//                 }
//             } elseif ($u_longtime >$p_longtime && $u_nighttime < $p_nighttime) {
//                 $time = $this->gettimeDifference($u_nighttime, $u_longtime);
//                     if ($time < 7 && $time > 0) {
//     //                    $fraction+= 30;
//                         $fraction+= $corresponding['enancytime'];
//                     } elseif ($time >= 7) {
//     //                    $fraction+= 50;
//                         $fraction+= $corresponding['q_enancytime'];
                        
//                     }
//             }elseif($u_longtime<$p_longtime && $p_nighttime>$u_nighttime){
//                 $time = $this->gettimeDifference($p_nighttime, $p_longtime);
//                     if ($time < 7 && $time > 0) {
//     //                    $fraction+= 30;
//                         $fraction+= $corresponding['enancytime'];
//                     } elseif ($time >= 7) {
//     //                    $fraction+= 50;
//                         $fraction+= $corresponding['q_enancytime'];
                        
//                     }
//             }elseif($u_longtime==$p_longtime && $u_nighttime==$u_nighttime){
//                 $fraction+= $corresponding['q_enancytime'];
//             }
//             //租住时长匹配
//             $score = $this->CalculateRenting($userinfo['tenant_long'], $value['tenant_long']);
//             $fraction += $score;
//             $ratio = bcdiv($fraction, $this->getMatchSum(1,$PosWeight['city_score']), 4) * 100;
//             if($ratio>=100)
//                 $ratio = 99.9;
//             $matchuser[$key]['ratio'] = $ratio;
//             $matchuser[$key]['pipeidu'] = $ratio . '%';
//             $matchuser[$key]['address'] = $value['province'];
//             if (empty($value['avatar'])) {
//                 if ($value['sex'] == 1) {
//                     $matchuser[$key]['Tximg'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnan1.png';
//                 } else {
//                     $matchuser[$key]['Tximg'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnv1.png';
//                 }
//             }
//             // else {
//             //     if ($value['is_true'] == 1) {
//                     $matchuser[$key]['Tximg'] = $value['avatar'];
//             //     } else {
//             //         $matchuser[$key]['Tximg'] = IMG_PATH . $value['avatar'];
//             //     }
//             // }
//             if (empty($value['name'])) {
//                 $matchuser[$key]['nickName'] = '【真.无名】';
//             } else {
//                 $matchuser[$key]['nickName'] = base64_decode($value['name']);
//             }
//             if ($value['sex'] == 1) {
//                 $matchuser[$key]['sex'] = 'man';
//             } else {
//                 $matchuser[$key]['sex'] = 'women';
//             }
//             if ($value['b_sex'] == 1) {
//                 $matchuser[$key]['lookFor'] = '男室友';
//             } else if ($value['b_sex'] == 2){
//                 $matchuser[$key]['lookFor'] = '女室友';
//             }else {
//                 $matchuser[$key]['lookFor'] = '男女室友';
//             }
//             unset($fraction);
//         }
//         //根据匹配分数排序
//         $data = $this->arraySort($matchuser, 'ratio', 'desc');
//         $result = array_values($data);
//         $result = array_slice($result,0,30);
//         //匹配分大于0显示
//         foreach ($result as $key => $value) {
//             if ($value['ratio'] > 0) {
//                 if (intval($value['is_place']) == 1) {
//                     $xing = M('circles')->field('city_name,area_name,name')->where('id=' . $value['shangquan'])->find();
//                     $value['zuzhupos'] = $xing['area_name'] . $xing['name'];
//                 } else {
//                     $xing = M('subways')->field('city,line,station')->where('id=' . $value['ditie'])->find();
//                     $value['zuzhupos'] = $xing['line'] . $xing['station'];
//                 }
//                 $value['labels'][] = '无房';

//                 //删除不需要的属性
//                 unset($value['address']);
//                 unset($value['age']);
//                 unset($value['avatar']);
//                 unset($value['b_bedlate']);
//                 unset($value['b_lovers']);
//                 unset($value['b_pet']);
//                 unset($value['b_sex']);
//                 unset($value['b_smoking']);
//                 unset($value['bedlate']);
//                 unset($value['budget']);
//                 unset($value['checkintime']);
//                 unset($value['checkoutime']);
//                 unset($value['city']);
//                 unset($value['constellation']);
//                 unset($value['customs']);
//                 unset($value['ditie']);
//                 unset($value['entertainment']);
//                 unset($value['gender']);
//                 unset($value['hometown']);
//                 unset($value['is_match']);
//                 unset($value['is_place']);
//                 unset($value['is_true']);
//                 unset($value['lovers']);
//                 unset($value['motion']);
//                 unset($value['name']);
//                 unset($value['nickname']);
//                 unset($value['openid']);
//                 unset($value['personality']);
//                 unset($value['pet']);
//                 unset($value['phone']);
//                 unset($value['province']);
//                 unset($value['ratio']);
//                 unset($value['school']);
//                 unset($value['shangquan']);
//                 unset($value['smoking']);
//                 unset($value['tenant_long']);
//                 unset($value['unionid']);
//                 unset($value['usertoken']);
//                 unset($value['weixin']);
//                 unset($value['work']);
//                 unset($value['zhiye']);
//                     $res[] = $value;
//             }
//         }
//         if (!empty($result)) {
//             xformatOutPutJsonData('success', $res, '');
//         } else {
//             xformatOutPutJsonData('error', 1, '');
//         }
//     }


//双向高级匹配
public function seniorBothMatch() {
    $Data = I('get.');
    $uid = $Data['id'];
    if (empty($uid)) {
        xformatOutPutJsonData('fail', '', '网络错误1！');
    }
    if ($Data['token'] !== S('user_' . $uid)) {
        xformatOutPutJsonData('error', 1, '网络错误2！');
    }
    //规则分数
    $corresponding = $this->getMatchNum();
    //设置室友各个特征的权重
    $weight = array(
        'distance' => 300,//距离权重
        'subway_line' => 100,//同一条地铁线路加分
        'time' => 0,//入住时间匹配
        'budget' => 40,//价格匹配
        'tag' => 5,//标签权重
        'age' => 10,//年龄权重匹配
        'school' => 10,//同一个学校加分
        'work' => 10,//工作匹配加分
        'hometown' => 5,//家乡匹配加分
    );
    //获取用户想找的室友
    $userpm = M('user_primary_matching')->field('sex,pet,smoking,bedlate,lovers')->where('uid=' . $uid)->find();
    //当前用户基本信息
    $userinfo = M('user')->where('id=' . $uid)->find();
    //符合初级匹配条件的用户
    if($userpm['sex']!=3)
        $map['a.sex'] = $userpm['sex'];
    if($userpm['pet']==0)
        $map['a.pet'] = 0;
    if($userpm['smoking']==0)
        $map['a.smoking'] = 0;
    if($userpm['bedlate']==0)
        $map['a.bedlate'] = 0;
    if($userpm['lovers']==0)
        $map['a.lovers'] = 0;
    $map['a.id'] = array('neq', $uid);
    $map['a.checkintime'] = array('ELT', $userinfo['checkoutime']);
    $map['a.checkoutime'] = array('EGT', $userinfo['checkintime']);
    $map['a.is_match'] = 2;
    //$map['a.id'] = $data['toid'];
    $mymatchuser = M('user')->alias('a')
            ->field('a.*,b.sex as b_sex,b.pet as b_pet,b.smoking as b_smoking,b.bedlate as b_bedlate,b.lovers as b_lovers')
            ->join('LEFT JOIN xqwl_user_primary_matching b ON a.id =b.uid')
            ->where($map)
            ->select();
    //var_dump(M()->getLastSql());
    //dump($mymatchuser);
    //删除那些我不满足对方需求的结果
    foreach ($mymatchuser as $key => $value) {
        if(($value['b_sex']==3||$value['b_sex']==$userinfo['sex'])//性别匹配
            &&($value['b_pet']==1||$value['b_pet']==$userinfo['pet'])//匹配宠物
            &&($value['b_smoking']==1||$value['b_smoking']==$userinfo['smoking'])//匹配抽烟
            &&($value['b_bedlate']==1||$value['b_bedlate']==$userinfo['bedlate'])//匹配晚睡
            &&($value['b_lovers']==1||$value['b_lovers']==$userinfo['lovers']))//匹配情侣
            $matchuser_temp[] = $value;
    }
    //dump($matchuser_temp);
    //删除那些被我拉黑的人
    $heimingdan = M('shoucang')->field('heimingdan')->where('uid=' . $uid)->find();
    if($heimingdan){
        $shanchumingdan = explode(",",$heimingdan['heimingdan']);
        foreach ($matchuser_temp as $key => $value) {
            $index = array_search($value['id'],$shanchumingdan);
            if($index === FALSE)
                $matchuser[] = $value;
        }
    }else{
        $matchuser = $matchuser_temp;
    }
    //dump($matchuser);
    $nianlingduan = M('age')->field('aid,a_name')->order('aid')->select();

    //获取我的位置
    $myPosition = M('city')->field('c_name as city')->where('cid='. $userinfo['city_no'])->find();
    if($userinfo['ditie']>0){
        //找出所有跟当前用户地铁站在一条地铁线路的地铁站
        $room_subway = M('subways')->field('city,station')->where("id=".$userinfo['ditie'])->find();
        $room_line = M('subways')->field('city,line')->where('city="'.$room_subway['city'].'" AND station="'.$room_subway['station'].'"')->select();
        $subways_line = array();
        foreach($room_line as $k=>$v){
            $subways_result =  M('subways')->field('id')->where('city="'.$v['city'].' "AND line="' .$v['line'].'"')->select();
            $subways_line = array_merge($subways_line, array_column($subways_result, 'id'));
        }
    }
    //设置不同城市权重
    // $PosWeight = M('city')->field('city_score')->where('c_name="'.$myPosition['city'].'"')->find();

    foreach ($matchuser as $key => $value) {
        // //我的最早入住时间
        // $u_longtime = strtotime($userinfo['checkintime']);
        // //我的最晚入住时间
        // $u_nighttime = strtotime($userinfo['checkoutime']);
        // //匹配最早入住时间
        // $p_longtime = strtotime($value['checkintime']);
        // //匹配最晚入住时间
        // $p_nighttime = strtotime($value['checkoutime']);
        // //计算租房入住时间交集 没有交集就跳过
        // $start = max($u_longtime,$p_longtime);
        // $end = min($p_nighttime,$u_nighttime);
        // $time = $this->gettimeDifference($end,$start);
        // if($time<0)
        //     continue;
        $hisPosition = M('city')->field('c_name as city')->where('cid='. $value['city_no'])->find();
        //租住城市不一致
        // if($myPosition['city']!=$hisPosition['city'])
        //     continue;
        $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
        //15公里以内分数为正，15公里以外分数为负，如果是一条线上的再加分
        if($distance>20)
            continue;
        else if($distance>15)
            $distance = 15;
        //10公里分数为0，0公里分数为满分，如果是一条线上的再加分
        $fraction = A('Home/Landlord')->getExpValue(0,$weight['distance'],15,0.1,$distance);
        if (intval($value['ditie']) > 0){
            $index = array_search($value['ditie'],$subways_line);
            if($index !== FALSE){
                $fraction += $weight['subway_line']/ceil($distance);
            }
        }
        //租住时长匹配
        $fraction+= $this->CalculateRenting($userinfo['tenant_long'], $value['tenant_long']);
        // $matchuser[$key]['租住时长匹配'] = $fraction;
        if (!empty($value['personality'])) {
            //标签匹配
            $personnality1 = explode(',',$userinfo['personality']);
            $personnality2 = explode(',',$value['personality']);
            $gexing = array_intersect($personnality1, $personnality2);
//                $fraction = bcadd($fraction, count($gexing) * 5);
            //单个标签对应匹配分数$corresponding['tag'];
            // $tag = intval($corresponding['tag']);
            //总的个性标签数
            $label = M('taste')->where("pid=1")->count();
            //相同的标签数
            $labelsum = $label-(count($personnality1)+count($personnality2)-2*count($gexing));
            $fraction = bcadd($fraction, $labelsum * $weight['tag']/$label);
        }
        if (!empty($value['motion'])) {
            $yundong1 = explode(',',$userinfo['motion']);
            $yundong2 = explode(',',$value['motion']);
            $gexing = array_intersect($yundong1, $yundong2);
//                $fraction = bcadd($fraction, count($gexing) * 5);
            //单个标签对应匹配分数$corresponding['tag'];
            // $tag = intval($corresponding['tag']);
            //总的个性标签数
            $label = M('taste')->where("pid=2")->count();
            //相同的标签数
            $labelsum = $label-(count($yundong1)+count($yundong2)-2*count($gexing));
            $fraction = bcadd($fraction, $labelsum * $weight['tag']/$label);
        }
        if (!empty($value['entertainment'])) {
            $entertainment1 = explode(',',$userinfo['entertainment']);
            $entertainment2 = explode(',',$value['entertainment']);
            $gexing = array_intersect($entertainment1, $entertainment2);
//                $fraction = bcadd($fraction, count($gexing) * 5);
            //单个标签对应匹配分数$corresponding['tag'];
            // $tag = intval($corresponding['tag']);
            //总的个性标签数
            $label = M('taste')->where("pid=3")->count();
            //相同的标签数
            $labelsum = $label-(count($entertainment1)+count($entertainment2)-2*count($gexing));
            $fraction = bcadd($fraction, $labelsum * $weight['tag']/$label);
        }
        // $matchuser[$key]['标签匹配'] = $fraction;
        if (!empty($value['age'])) {
            //匹配年龄
            if (intval($userinfo['age']) > intval($value['age'])) {
                $Agedifference = bcsub($userinfo['age'], $value['age']);
            } else {
                $Agedifference = bcsub($value['age'], $userinfo['age']);
            }
            if($Agedifference>40)
                $Agedifference = 40;
            A('Home/Landlord')->getExpValue(0,$weight['age'],40,0.1,$Agedifference);
            // if ($Agedifference < 2) {
            // //  $fraction+=20;
            //     $fraction+=intval($corresponding['age']);
            // } elseif ($Agedifference < 4 && $Agedifference >= 2) {
            // //   $fraction+=10;
            //     $fraction+=intval($corresponding['age1']);
            // } elseif ($Agedifference > 5) {
            //     $fraction+=$corresponding['age3'];
            // }
        }
        // $matchuser[$key]['年龄匹配'] = $fraction;
        //暂时不需要年龄段
        // foreach ($nianlingduan as $k => $v) {
        //     if((intval($value['age']))==intval($v['aid'])){
        //         $matchuser[$key]['age'] = $v['a_name'];
        //         break;
        //     }
        // }
        if (!empty($value['budget'])) {
            //预算匹配
            // $scorebug = $this->Budget($userinfo['budget'], $value['budget']);
            // $fraction+= $scorebug;
            A('Home/Landlord')->getExpValue(0,$weight['budget'],5,0.1,abs(intval($value['budget'])-intval($userinfo['budget'])));
        }
        // $matchuser[$key]['预算匹配'] = $fraction;
        if (!empty($value['school'])) {
            //学校匹配
            if ($userinfo['school'] == $value['school']) {
//                    $fraction+=20;
                $fraction+=$weight['school'];
            }
        }
        // $matchuser[$key]['学校匹配'] = $fraction;
//         if (!empty($value['constellation'])) {
//             //星座匹配
//             if ($userinfo['constellation'] == $value['constellation']) {
// //                    $fraction+=20;
//                 $fraction+=intval($corresponding['constellation']);
//             }
//         }
        if (!empty($value['hometown'])) {
            //家乡省份匹配
            $p = implode(',', $value['hometown']);
            $u = implode(',', $userinfo['hometown']);
            if ($p[0] == $u[0]) {
//                    $fraction+=20;
                // $fraction+=intval($corresponding['address']);
                $fraction += $weight['hometown'];
            }
        }
        // $matchuser[$key]['家乡匹配'] = $fraction;
        if (!empty($value['work'])) {
            //职业匹配
            if ($userinfo['work'] == $value['work']) {
//                    $fraction+=20;
                // $fraction+=intval($corresponding['work']);
                $fraction += $weight['work'];
            }
        }
        // $matchuser[$key]['职业匹配'] = $fraction;
        if (!empty($value['work'])) {
            $work = M('work')->field('w_name')->where('id=' . $value['work'])->find();
            $matchuser[$key]['zhiye'] = $work['w_name'];
        }
        // $ratio = bcdiv($fraction, $this->getMatchSum(2,$PosWeight['city_score']), 4) * 100;
        $ratio = bcdiv($fraction, $weight['distance']+$weight['budget']+$weight['tag']+$weight['age']+$weight['school']+$weight['work']+$weight['hometown'], 4) * 100;
        if($ratio>=100)
            $ratio = 99.9;
        $matchuser[$key]['ratio'] = $ratio;
        $matchuser[$key]['pipeidu'] = $ratio;
//            $matchuser[$key]['Tximg'] = $value['avatar'];
        // $matchuser[$key]['address'] = $value['province'];
//            $matchuser[$key]['nickName'] = base64_decode($value['name']);
        if (empty($value['avatar'])) {
            if ($value['sex'] == 1) {
                $matchuser[$key]['Tximg'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnan1.png';
            } else {
                $matchuser[$key]['Tximg'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnv1.png';
            }
        } 
        else {
            // if ($value['is_true'] == 1) {
                $matchuser[$key]['Tximg'] = $value['avatar'];
            // } else {
            //     $matchuser[$key]['Tximg'] = IMG_PATH . $value['avatar'];
            // }
        }
        if (empty($value['name'])) {
            xformatOutPutJsonData('fail', '', 'name为空');
        } else {
            $matchuser[$key]['nickName'] = base64_decode($value['name']);
        }
        if ($value['sex'] == 1) {
            $matchuser[$key]['sex'] = 'man';
        } else {
            $matchuser[$key]['sex'] = 'women';
        }

        unset($fraction);
    }
    $data = $this->arraySort($matchuser, 'ratio', 'desc');
    $result = array_values($data);
    //匹配分值显示限制
    foreach ($result as $key => $value) {
        if ($value['ratio'] > 0) {
            //显示租住地点
            $value['zuzhupos'] = $value['address'];
            //加几个标签
            if($value['school']==$userinfo['school'])
                $value['labels'][] = '校友';
            if($value['work']==$userinfo['work'])
                $value['labels'][] = '同行';
            $p = implode(',', $value['hometown']);
            $u = implode(',', $userinfo['hometown']);
            if ($p[0]!=null && $p[0] == $u[0]) {
                $value['labels'][] = '老乡';
            }
            if($value['has_room']==1)
                $value['labels'][] = '有房';
            else
                $value['labels'][] = '无房';
            //$value['nianlingduans'] = $nianlingduan;
            //删除不需要的属性
            unset($value['address']);
            unset($value['avatar']);
            unset($value['b_bedlate']);
            unset($value['b_lovers']);
            unset($value['b_pet']);
            unset($value['b_sex']);
            unset($value['b_smoking']);
            unset($value['bedlate']);
            unset($value['budget']);
            unset($value['checkintime']);
            unset($value['checkoutime']);
            unset($value['city']);
            unset($value['constellation']);
            unset($value['customs']);
            unset($value['ditie']);
            unset($value['entertainment']);
            unset($value['gender']);
            unset($value['has_room']);
            unset($value['hometown']);
            unset($value['is_match']);
            unset($value['lovers']);
            unset($value['motion']);
            unset($value['name']);
            unset($value['nickname']);
            unset($value['openid']);
            unset($value['personality']);
            unset($value['pet']);
            unset($value['phone']);
            unset($value['province']);
            unset($value['ratio']);
            unset($value['school']);
            unset($value['smoking']);
            unset($value['tenant_long']);
            unset($value['unionid']);
            unset($value['usertoken']);
            unset($value['weixin']);
            unset($value['work']);
            unset($value['zhiye']);
            $res[] = $value;
        }   
    }
    if(!$Data['count'])
        $Data['count'] = 30;//现在设置初始查看30个室友，需要和小程序一起改动
    else if($Data['count'] > 30)
        $Data['count'] = 200;//现在设置最多查看200个室友，需要和小程序一起改动
    if (!empty($res)) {
        xformatOutPutJsonData('success', array_slice($res,0,intval($Data['count'])), count($res));
    } else {
        xformatOutPutJsonData('success', 1, '');
    }
}

    
    /*
     * 获取用户基本信息
     */

    public function getUserinfo() {
        $Data = I('get.');
        $uid = $Data['id'];//当前用户id
//        $uid = 453;   
        if (empty($Data['pipei'])) {
            xformatOutPutJsonData('fail', '', '网络错误4！');
        }else if(!$Data['pipei']||$Data['pipei']<0){
            //通过二维码扫描进来的没有匹配度，以后看需要加啥
            //$res['type'] = 'minicode';
            if ($Data['token'] == S('user_' . $Data['id'])) {

            }else if ($Data['token'] == S('user_landlord' . $Data['id'])) {
                $uid = '-'.$uid;
            }else if ($Data['token'] == 'visit') {

            }else {
                xformatOutPutJsonData('fail', '', '网络错误2！');
            }
        }else{
            if (empty($uid)) {
                xformatOutPutJsonData('fail', '', '网络错误1！');
            }
            if ($Data['token'] == S('user_' . $Data['id'])) {

            }else if ($Data['token'] == S('user_landlord' . $Data['id'])) {
                $uid = '-'.$uid;
            }else{
                xformatOutPutJsonData('fail', '', '网络错误2！');
            }
        }
        if (empty($Data['pid'])) {
            xformatOutPutJsonData('fail', '', '网络错误3！');
        }
 //       $pipei = M('user')->field('is_place')->where('id=' . $uid)->find();
        $map['a.id'] = $Data['pid'];//匹配用户id
        $data = M('user')->alias('a')
//                ->field('a.*,b.sex as lookfor,c.s_name,d.w_name')
                ->field('a.*,b.sex as lookfor,b.pet as b_pet,b.smoking as b_smoking,b.bedlate as b_bedlate,b.lovers as b_lovers')
                ->join('LEFT JOIN xqwl_user_primary_matching b ON a.id =b.uid')
//                ->join('LEFT JOIN xqwl_school c ON a.school =c.id')
//                ->join('LEFT JOIN xqwl_work d ON a.work =d.id')
                ->where($map)
                ->find();
        
        if (empty($data['name'])) {
            xformatOutPutJsonData('fail', '', 'name为空');
        } else {
            $data['nickname'] = base64_decode($data['name']);
        }
        if (!empty($data['school'])) {
            $school = M('school')->field('s_name')->where('sid=' . $data['school'])->find();
            $data['biyeschool'] = $school['s_name'];
        }
        if (!empty($data['work'])) {
            $work = M('work')->field('w_name')->where('id=' . $data['work'])->find();
            $data['zhiye'] = $work['w_name'];
        }
        if (!empty($data['constellation'])) {
            $work = M('constellation')->field('c_name')->where('id=' . $data['constellation'])->find();
            $data['xingzuo'] = $work['c_name'];
        }
        if (!empty($data['budget'])) {
            $work = M('budget')->field('b_name')->where('id=' . $data['budget'])->find();
            $data['yusuan'] = $work['b_name'];
        }
        if (!empty($data['tenant_long'])) {
            $work = M('tenant_long')->field('name')->where('id=' . $data['tenant_long'])->find();
            $data['zutime'] = $work['name'];
        }


        if ($data['sex'] == 1) {
            $data['sex'] = 'man';
        } else {
            $data['sex'] = 'women';
        }
        if ($data['lookfor'] == 1) {
            $data['lookfor'] = '男室友';
        }else if ($data['lookfor'] == 2) {
            $data['lookfor'] = '女室友';
        } else {
            $data['lookfor'] = '男女室友';
        }
        if ($data['lovers'] == 1) {
            $xiguan[] = '情侣';
        } else {
            $xiguan[] = '单身';
        }
        if ($data['bedlate'] == 1) {
            $xiguan[] = '晚睡';
        } else {
            $xiguan[] = '早睡';
        }
        if ($data['smoking'] == 1) {
            $xiguan[] = '抽烟';
        } else {
            $xiguan[] = '不抽烟';
        }
        if ($data['pet'] == 1) {
            $xiguan[] = '养宠物';
        } else {
            $xiguan[] = '不养宠物';
        }
        
        if ($data['b_lovers'] == 1) {
            $shiyouxiguan[] = '允许情侣';
        } else {
            $shiyouxiguan[] = '必须单身';
        }
        if ($data['b_bedlate'] == 1) {
            $shiyouxiguan[] = '允许晚睡';
        } else {
            $shiyouxiguan[] = '必须早睡';
        }
        if ($data['b_smoking'] == 1) {
            $shiyouxiguan[] = '允许抽烟';
        } else {
            $shiyouxiguan[] = '严禁抽烟';
        }
        if ($data['b_pet'] == 1) {
            $shiyouxiguan[] = '允许养宠物';
        } else {
            $shiyouxiguan[] = '不许养宠物';
        }

//        if (!empty($data['shangquan'])) {
//            $xing = M('trading')->field('pid,name')->where('id=' . $data['shangquan'])->find();
//            $quan = M('trading')->field('name')->where('id=' . $xing['pid'])->find();
//            $data['zuzhupos'] = $quan['name'] . $xing['name'];
//        }
//        xformatOutPutJsonData('test', $pipei, '');
        $data['zuzhupos'] = $data['address'];
        // if (intval($data['is_place']) == 1) {
        //     $xing = M('trading')->field('pid,name')->where('id=' . $data['shangquan'])->find();
        //     $quan = M('trading')->field('name')->where('id=' . $xing['pid'])->find();
        //     $data['zuzhupos'] = $quan['name'] . $xing['name'];
        // } else {
        //     $xing = M('metro')->field('pid,title')->where('id=' . $data['ditie'])->find();
        //     $quan = M('metro')->field('title')->where('id=' . $xing['pid'])->find();
        //     $data['zuzhupos'] = $quan['title'] . $xing['title'];
        // }
//        $data['touxiang'] = $data['avatar'];
        if (empty($data['avatar'])) {
            if ($data['sex'] == 'man') {
                $data['touxiang'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnan1.png';
            } else {
                $data['touxiang'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnv1.png';
            }
        } else {
            $data['touxiang'] = $data['avatar'];
        }
        if (empty($data['name'])) {
            xformatOutPutJsonData('fail', '', 'name为空');
        } else {
            $data['nickname'] = base64_decode($data['name']);
        }
        // $data['address'] = $data['province'];
        $data['jiaxiang'] = $data['hometown'];
        $data['dianhua'] = $data['phone'];
//        $data['wx'] = $data['weixin'];
//        $data['biyeschool'] = $data['s_name'];
//        $data['zhiye'] = $data['w_name'];
        $data['zuiztime'] = date('Y-m-d',strtotime($data['checkintime']));
        $data['zuiwtime'] =date('Y-m-d',strtotime($data['checkoutime']));
        $data['xiguan'] = implode('、', $xiguan);
        $data['shiyouxiguan'] = implode('、', $shiyouxiguan);
        $data['pipeidu'] = $Data['pipei'];
        //获取标签
        $label[0]['tit'] = "个性";
        $label[0]['color'] = "#f4b1f6";
        $label[0]['text'] = [];
        $label[1]['tit'] = "运动";
        $label[1]['color'] = "#4957b8";
        $label[1]['text'] = [];
        $label[2]['tit'] = "娱乐";
        $label[2]['color'] = "#ffb02f";
        $label[2]['text'] = [];
        if (!empty($data['personality'])) {
            $personality = explode(',', $data['personality']);
            foreach ($personality as $key => $value) {
                $biaoqian1 = M('taste')->field('tasname')->where('id=' . $value)->find();
                array_push($label[0]['text'], $biaoqian1['tasname']);
            }
        }

        if (!empty($data['motion'])) {
            $motion = explode(',', $data['motion']);
            foreach ($motion as $key => $value) {
                $biaoqian2 = M('taste')->field('tasname')->where('id=' . $value)->find();
                array_push($label[1]['text'], $biaoqian2['tasname']);
            }
        }
        if (!empty($data['entertainment'])) {
            $entertainment = explode(',', $data['entertainment']);
            foreach ($entertainment as $key => $value) {
                $biaoqian3 = M('taste')->field('tasname')->where('id=' . $value)->find();
                array_push($label[2]['text'], $biaoqian3['tasname']);
            }
        }
        if(!empty($uid)){
            //返回当前是否已经被收藏或者拉黑
            $data['shoucang'] = 0;
            $data['heimingdan'] = 0;   
            $result3 = M('shoucang')->field('shoucang,heimingdan')->where('uid=' . $uid)->find();
            if($result3){
                $myShoucang = explode(",",$result3['shoucang']);
                $myHeimingdan = explode(",",$result3['heimingdan']);
                $index = array_search($Data['pid'],$myShoucang);
                if(!($index===FALSE))
                    $data['shoucang'] = 1;
                $index = array_search($Data['pid'],$myHeimingdan);
                if(!($index===FALSE))
                    $data['heimingdan'] = 1;    
            }
            //加标签
            $userinfo = M('user')->where('id=' . $uid)->find();
            //加几个标签
            if($data['school']==$userinfo['school'])
                $data['labels'][] = '校友';
            if($data['work']==$userinfo['work'])
                $data['labels'][] = '同行';
            $p = implode(',', $data['hometown']);
            $u = implode(',', $userinfo['hometown']);
            if ($p[0]!=null && $p[0] == $u[0]) {
                $data['labels'][] = '老乡';
            }
        }
        if($data['has_room']==1)
            $data['labels'][] = '有房';
        else
            $data['labels'][] = '无房';

        $res['data'] = $data;
        $res['biaoqian'] = $label;
        if (!empty($data)) {
            xformatOutPutJsonData('success', $res, '');
        }
    }

    /*
     * 获取支持的租住城市信息
     */

    public function getSupportCity() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //获取国家
        $data['countries'] = M('city')->field('DISTINCT country')->select();
        foreach ($data['countries'] as $k => $v) {
                // $data['countries'][$k]['id'] = $k + 1;
                $cities[] = M('city')->field('cid ,c_name as city,lon,lat')->where('country="' . $v['country']. '"')->select();   
        }
        $data['cities'] = $cities;

        $user=  M('user')->field('city_no,lon,lat,address')->where('id='.$uid)->find();
        xformatOutPutJsonData('success', $data, $user);
    }

    /*
     * 获取附近的租房信息
     */

    public function getNeighborInfo() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        /*设置要选取的房源地点的经纬度界限
            1.同一条经线上，纬度相差1°，其距离相差约111千米。
            2..在同一条纬线上（假设此纬线的纬度为Φ），经度相差1°对应的实际弧长大约为111×cosΦ千米。
            假设只显示方圆3公里以内的室友，那么先找出纬度相差0.027度，经度相差0.03度
        */
        $sqlCondition = "lon<".($Data['lon']+0.03). "AND lon>".($Data['lon']-0.03) . "AND lat<".($Data['lat']+0.027)." AND lat>" .($Data['lat']-0.027);
        $data['user'] =  M('user')->where($sqlCondition)->count();
        if($data['user']==0){
            $data['user'] = "未知";
        }
        $dankeCount = M('danke')->where($sqlCondition)->count();
        $dankePrice = M('danke')->where($sqlCondition)->avg('normal_price');
        $woaiwojiaCount = M('woaiwojia')->where($sqlCondition)->count();
        $woaiwojiaPrice = M('woaiwojia')->where($sqlCondition)->avg('normal_price');
        $landlordPrice = M('landlord_room')->where($sqlCondition. "AND publish=1")->avg('price');
        $landlordCount = M('landlord_room')->where($sqlCondition. "AND publish=1")->count();
        $data['room_count'] = $dankeCount + $woaiwojiaCount + $landlordCount;
        if($data['room_count']>0)
            $data['room_price'] = ($dankeCount*$dankePrice + $woaiwojiaCount*$woaiwojiaPrice + $landlordCount*$landlordPrice)/$data['room_count'];
        else{
            $data['room_count'] = "未知";
            $data['room_price'] = "未知";
        }
        // xformatOutPutJsonData('test2', $woaiwojiaCount, $woaiwojiaPrice);
        xformatOutPutJsonData('success', $data, M()->getLastSql());
    }

    /*
     * 获取高级匹配基本信息
     */

    public function getSeniorInfo() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //获取年龄段
        $data['age'] = M('age')->field('aid,a_name')->select();
        //获取城市
        $data['erji_city'] = M('city')->field('cid,c_name as name')->select();
        //获取学校
        foreach ($data['erji_city'] as $key => $value) {
            $data['erji_school'][] =  M('school')->field('sid,s_name as name')->where('s_city="' . $value['name'] .'"')->select();
        }
        //获取职业
        $data['work'] = M('work')->field('id,w_name')->select();
        //获取星座
        $data['constellation'] = M('constellation')->field('id,c_name')->select();
        //获取预算
        $country = M('user')->alias('a')
                ->field('a.*,b.country as country')
                ->join('LEFT JOIN xqwl_city b ON a.city_no =b.cid')
                ->where("a.id = ".$uid)
                ->find();
        $data['budget'] = M('budget')->field('id,b_name')->where("country='".$country['country']."'")->select();
        xformatOutPutJsonData('success', $data, '');
    }

    /*
     * 设置用户高级匹配的基本信息
     */

    public function setUserSenior() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
//         $isMob="/^1[34578]{1}[12356789]{1}\d{8}$/";
//         $isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
//         var_dump(preg_match($isMob,$Data['phone']));die;
//         if(!preg_match($isMob,$Data['phone']) && !preg_match($isTel,$Data['phone']))
//         {
//             xformatOutPutJsonData('error', '1', '手机或电话号码格式不正确.如果是固定电话，必须形如(0315-87876787)!');
//         }

        //检查个性签名文字是否合法
        $msgCheck = A('Home/Chat')->messageCheck($Data['personal']);
        if($Data['personal']=="" || $msgCheck['errcode']!=87014){
            $map['age'] = $Data['age'];
            $map['school'] = $Data['school'];
            $map['work'] = $Data['zhiye'];
            $map['constellation'] = $Data['xingzuo'];
            $map['budget'] = $Data['yusuan'];
            $map['phone'] = $Data['phone'];
            $map['personal'] = $Data['personal'];
            $map['has_room'] = $Data['youwufang'];
            $hometown = str_replace("&quot;", "", $Data['hometown']);
            $hometowns = str_replace("[", "", $hometown);
            $hometowns = str_replace("]", "", $hometowns);
            $map['hometown'] = $hometowns;
            $res = M('user')->where('id=' . $uid)->save($map);
            if ($res !== FALSE) {
                xformatOutPutJsonData('success', '', '');
            }
            xformatOutPutJsonData('fail', 1, "未知错误");
        }else{
            xformatOutPutJsonData('fail', 1, "个性签名内容有违法违规内容");
        }
    }

    /*
     * 获取高级匹配标签
     */

    public function getSeniorLabel() {
        $Data = I('get.');
        $uid = $Data['id'];
//        $uid = 20;
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $data[0] = array(
            'tit' => "个性",
            'img' => "/image/gexing.png",
            'img1' => "/image/gengduo0.png",
            'height' => "130px",
            'color' => "#f4b1f6",
            'index' => 0,
        );
        $data[1] = array(
            'tit' => "运动",
            'img' => "/image/yundong.jpg",
            'img1' => "/image/gengduo1.png",
            'height' => "130px",
            'color' => "#4957b8",
            'color1' => "#fff",
            'index' => 1,
        );
        $data[2] = array(
            'tit' => "娱乐",
            'img' => "/image/yule.jpg",
            'img1' => "/image/gengduo2.png",
            'height' => "130px",
            'color' => "#ffb02f",
            'index' => 2,
        );
        //获取用户标签项
        $userLabel = M('user')->field('personality,motion,entertainment')->where('id=' . $uid)->find();
        //获取个性
        $gexing = M('taste')->field('id,tasname')->where('pid=1')->select();
        foreach ($gexing as $key => $value) {
            if (!empty($userLabel['personality'])) {
                $personality = explode(',', $userLabel['personality']);
                if (in_array($value['id'], $personality)) {
                    $data[0]['biaoqian'][$key]['status'] = 1;
                } else {
                    $data[0]['biaoqian'][$key]['status'] = 0;
                }
            } else {
                $data[0]['biaoqian'][$key]['status'] = 0;
            }
            $data[0]['biaoqian'][$key]['id'] = $key;
            $data[0]['biaoqian'][$key]['indexid'] = $value['id'];
            $data[0]['biaoqian'][$key]['text'] = $value['tasname'];
        }
        //获取运动大数组
        $yundong = M('taste')->field('id,tasname')->where('pid=2')->select();
        foreach ($yundong as $key => $value) {
            $motion = explode(',', $userLabel['motion']);
            if (!empty($userLabel['motion'])) {
                if (in_array($value['id'], $motion)) {
                    $data[1]['biaoqian'][$key]['status'] = 1;
                } else {
                    $data[1]['biaoqian'][$key]['status'] = 0;
                }
            } else {
                $data[1]['biaoqian'][$key]['status'] = 0;
            }
            $data[1]['biaoqian'][$key]['text'] = $value['tasname'];
            $data[1]['biaoqian'][$key]['id'] = $key;
            $data[1]['biaoqian'][$key]['indexid'] = $value['id'];
        }
        //获取娱乐
        $yule = M('taste')->field('id,tasname')->where('pid=3')->select();
        foreach ($yule as $key => $value) {
            if (!empty($userLabel['entertainment'])) {
                $entertainment = explode(',', $userLabel['entertainment']);
                if (in_array($value['id'], $entertainment)) {
                    $data[2]['biaoqian'][$key]['status'] = 1;
                } else {
                    $data[2]['biaoqian'][$key]['status'] = 0;
                }
            } else {
                $data[2]['biaoqian'][$key]['status'] = 0;
            }
            $data[2]['biaoqian'][$key]['id'] = $key;
            $data[2]['biaoqian'][$key]['indexid'] = $value['id'];
            $data[2]['biaoqian'][$key]['text'] = $value['tasname'];
        }
        //定义选中的标签数组
        $array = [[], [], []];
        if ($personality) {
            foreach ($personality as $key => $value) {
                array_push($array[0], $value);
            }
        }
        if ($motion) {
            foreach ($motion as $key => $value) {
                array_push($array[1], $value);
            }
        }
        if ($entertainment) {
            foreach ($entertainment as $key => $value) {
                array_push($array[2], $value);
            }
        }
        if (empty($array[0][0])) {
            unset($array[0][0]);
        }
        if (empty($array[1][0])) {
            unset($array[1][0]);
        }
        if (empty($array[2][0])) {
            unset($array[2][0]);
        }
        $res['data'] = $data;
        $res['array'] = $array;
        xformatOutPutJsonData('success', $res, '');
    }

    /*
     * 设置高级匹配标签
     */

    public function setSeniorLabel() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
//        $type = $Data['types'];
        $map = $Data['array'];
        $data = str_replace("&quot;", "", $map);
        $datas = json_decode($data);
        //个性
        $where['personality'] = implode(',', $datas[0]);
        //运动
        $where['motion'] = implode(',', $datas[1]);
        //娱乐
        $where['entertainment'] = implode(',', $datas[2]);
        //设置用户级别状态
        $where['is_match']=2;
        $res = M('user')->where('id=' . $uid)->save($where);
        if ($res !== FALSE) {
            xformatOutPutJsonData('success', '', '');
        }
    }

    /*
     * 设置高级状态
     */

    public function setMatch() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $res = M('user')->where('id=' . $uid)->save(array('is_match' => 2));
        if ($res !== FALSE) {
            xformatOutPutJsonData('success', '', '');
        }
    }

    /*
     * 修改用户信息
     */

    public function setUseredit() {
        $Data = $_GET;
        $uid = $Data['id'];
        $map = $Data['map'];
        $status = $Data['guanbi'];
        // if(intval($status)!=10 && intval($status)!=12){
        //     if(empty($map)){
        //         xformatOutPutJsonData('error', '1', '不能为空!');
        //     }
        // }
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //dump($Data);
        $user = M('user')->field('checkintime')->where('id=' . $uid)->find();
        switch ($status) {
            case 0:
                $data['tenant_long'] = $map;
                break;
            case 1:

                break;
            case 2:
                $data['school'] = $map;
                break;
            case 3:
                $data['work'] = $map;
                break;
            case 4:
                $data['constellation'] = $map;
                break;
            case 5:
                $data['budget'] = $map;
                break;
            case 6:
                
//                $isMob="/^1[34578]{1}[12356789]{1}\d{8}$/";
                // $isMob="/^[1][3456789]\d{9}$/";
                // $isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
                // if(!preg_match($isMob,$map) && !preg_match($isTel,$map))
                // {
                //     xformatOutPutJsonData('error', '1', '手机或电话号码格式不正确.如果是固定电话，必须形如(0315-87876787)!');exit;
                // }
                $data['phone'] = $map;
                break;
            case 7:
                $data['hometown'] = $map;
                break;
            case 8:
                $data['checkintime'] = $map;
                break;
            case 9:
                if(strtotime($map)<strtotime($user['checkintime'])){
                    xformatOutPutJsonData('error', '1', '最晚入住时间不能小于最早入住时间');exit;
                }
                $data['checkoutime'] = $map;
                break;
            case 11:
                $data['age'] = $map;
                break;
            case 10:
            case 12:
                $data['sex'] = $map;
                break;
            case 13:
                $data['weixin'] = $map;
                break;
            case 14:
                $data['has_room'] = $map;
                break;
            case 15:
                $data['is_match'] = $map;
                break;
            case 16:
                //检查文字是否合法
                $msgCheck = A('Home/Chat')->messageCheck($map);
                // xformatOutPutJsonData('test', $msgCheck, $msgCheck['errcode']);
                if($map==''|| $msgCheck['errcode']!=87014){
                    $data['personal'] = $map;
                    break;
                }else{
                    xformatOutPutJsonData('fail', 1, "个性签名内容有违法违规内容");
                }
        }
        //dump(intval($status)==12);
        //dump($data);
        if(intval($status)==12){
            $res = M('user_primary_matching')->where('uid=' . $uid)->save($data);
        }else{
            $res = M('user')->where('id=' . $uid)->save($data);
        }
        //dump(M()->getLastSql());
        if ($res!== FALSE) {
            xformatOutPutJsonData('success', '', M()->getLastSql());
        }else{
            xformatOutPutJsonData('fail', '', M()->getLastSql());
        }
    }
    //上传图片，根据微信的uploadFile接收chooseImage的tempFilePaths.
    public function upload() {
        $Data = I('post.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $file = $_FILES;
        //检查图片是否合法
        $imgCheck = A('Home/Chat')->mediaCheck($file);
        $imgCheck = json_decode(stripslashes($imgCheck));
        $imgCheck = json_decode(json_encode($imgCheck), true);
        if($imgCheck['errcode']==87014){
            xformatOutPutJsonData('fail', 1, "有违法违规内容");
        }else if($imgCheck['errcode']==-1){
            xformatOutPutJsonData('fail', 1, "图片尺寸超过 750px x 1334px");
        }else{
            $config = array(
                'rootPath' => "./Public/Avatar/",
                //'rootPath' => "./Public/MiniCode/",
                'exts' => array('jpg', 'png', 'jpeg', 'bmp'),
                // 'subName' => array('date', 'Ymd'),
                'autoSub' => false,
                'saveName' => $Data['preno'].'_'.$Data['openid'],
                'replace' => true,
            );
            $upload = new \Think\Upload($config);
            $info = $upload->upload($file);
            if(!$info) {// 上传错误提示错误信息
                xformatOutPutJsonData('fail', 1, $upload->getError());
            }else{// 上传成功 获取上传文件信息
                unlink('./Public/Avatar/'.((int)($Data['preno'])-1).'_'.$Data['openid'].'.jpg');
                // unlink('./Public/Avatar/'.((int)($Data['preno'])-1).'_'.$Data['openid'].'.gif');
                unlink('./Public/Avatar/'.((int)($Data['preno'])-1).'_'.$Data['openid'].'.png');
                unlink('./Public/Avatar/'.((int)($Data['preno'])-1).'_'.$Data['openid'].'.jpeg');
                unlink('./Public/Avatar/'.((int)($Data['preno'])-1).'_'.$Data['openid'].'.bmp');
                $fileurl =str_replace('http','https',IMG_PATH). '/Public/Avatar/' . $info['file']['savepath'] . $info['file']['savename'];
                xformatOutPutJsonData('success', $fileurl, $imgCheck);
            }
        }
    }
        public function editAvatar() {
            $Data = I('get.');
            $uid = $Data['id'];
            if (empty($uid)) {
                xformatOutPutJsonData('fail', '', '网络错误1！');
            }
            if ($Data['token'] !== S('user_' . $Data['id'])) {
                xformatOutPutJsonData('fail', '', '网络错误2！');
            }
            //$data['nickname']=$Data['nickname'];
            //检查文字是否合法
            $msgCheck = A('Home/Chat')->messageCheck($Data['nickname']);
            // $msgCheck = json_decode(stripslashes($msgCheck));
            // $msgCheck = json_decode(json_encode($msgCheck), true);
            // xformatOutPutJsonData('test', $msgCheck, $msgCheck['errcode']);
            if($msgCheck['errcode']!=87014){
                $data['name']=base64_encode($Data['nickname']);
                if(!empty($Data['images'])){
                    $data['avatar']=$Data['images'];
                }
                $res = M('user')->where('id=' .intval($uid))->save($data);
                if ($res !== FALSE) {
                    // $result=array('status' =>'success' ,'code' => 1);
                    xformatOutPutJsonData('success', 1, $msgCheck);
                }
            }else{
                xformatOutPutJsonData('fail', 1, "有违法违规内容");
            }
        }

    /*
     * 获取用户全部基本信息
     */

    public function getUserinfoall() {
        $Data = I('get.');
        $uid = $Data['id'];
//        $uid = 363;
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //获取基本信息
        $map['a.id'] = $uid;
        $data = M('user')->alias('a')
                ->field('a.*,b.sex as lookfor ,b.pet as b_pet,b.smoking as b_smoking,b.bedlate as b_bedlate,b.lovers as b_lovers')
                ->join('LEFT JOIN xqwl_user_primary_matching b ON a.id =b.uid')
                ->where($map)
                ->find();
        //dump($data);
        if (empty($data['name'])) {
            xformatOutPutJsonData('fail', '', 'name为空');
        } else {
            $data['nickname'] = base64_decode($data['name']);
        }
        $data['touxiang'] = $data['avatar'];
        if (!empty($data['budget'])) {
            $budget = M('budget')->field('b_name')->where('id=' . $data['budget'])->find();
            $data['yusuan'] = $budget['b_name'];
        }
        if ($data['sex'] == 1) {
            $data['sex'] = 'man';
        } else {
            $data['sex'] = 'women';
        }
        // $data['zuiztime'] = date('Y-m-d',strtotime($data['checkintime']));
        // $data['zuiwtime'] = date('Y-m-d',strtotime($data['checkoutime']));
        $data['zuiztime'] = $data['checkintime'];
        $data['zuiwtime'] = $data['checkoutime'];
        $country = M('user')->alias('a')
                ->field('a.*,b.country as country')
                ->join('LEFT JOIN xqwl_city b ON a.city_no =b.cid')
                ->where("a.id = ".$uid)
                ->find();
        $res['yusuan'] = M('budget')->field('id,b_name')->where("country='".$country['country']."'")->select();
        
        foreach ($res['yusuan'] as $key => $value) {
            if($data['budget']==$value['id']){
                $res['index3'] = $key;
            }
        }
        $res['zutime'] = M('tenant_long')->field('id,name')->select();
        foreach ($res['zutime'] as $key => $value) {
            if($data['tenant_long']==$value['id']){
                $res['index'] = $key;
            }
        }
        if (!empty($data['tenant_long'])) {
            $tenant_long = M('tenant_long')->field('name')->where('id=' . $data['tenant_long'])->find();
            $data['zutime'] = $tenant_long['name'];
        }
        $data['zuzhupos'] = $data['address'];
        if(intval($data['is_match']) == 1){
           $res['info'] = $data;
           xformatOutPutJsonData('success', $res, '');
        }
        if (!empty($data['age'])) {
            $age = M('age')->field('a_name')->where('aid=' . $data['age'])->find();
            $data['nianlingduan'] = $age['a_name'];
        }
        if (!empty($data['school'])) {
            $school = M('school')->field('s_name')->where('sid=' . $data['school'])->find();
            $data['biyeschool'] = $school['s_name'];
        }
        if (!empty($data['work'])) {
            $work = M('work')->field('w_name')->where('id=' . $data['work'])->find();
            $data['zhiye'] = $work['w_name'];
        }
        if (!empty($data['constellation'])) {
            $constellation = M('constellation')->field('c_name')->where('id=' . $data['constellation'])->find();
            $data['xingzuo'] = $constellation['c_name'];
        }
        $data['is_sex']=intval($data['sex'])-1;
        $data['is_lookfor']=intval($data['lookfor'])-1;
        
        if ($data['lookfor'] == 1) {
            $data['lookfor'] = '男室友';
        } else if ($data['lookfor'] == 2) {
            $data['lookfor'] = '女室友';
        } else {
            $data['lookfor'] = '男女室友';
        }
        if ($data['lovers'] == 1) {
            $xiguan[] = '情侣';
        } else {
            $xiguan[] = '单身';
        }
        if ($data['bedlate'] == 1) {
            $xiguan[] = '晚睡';
        } else {
            $xiguan[] = '早睡';
        }
        if ($data['smoking'] == 1) {
            $xiguan[] = '抽烟';
        } else {
            $xiguan[] = '不抽烟';
        }
        if ($data['pet'] == 1) {
            $xiguan[] = '养宠物';
        } else {
            $xiguan[] = '不养宠物';
        }
        
        
        if ($data['b_lovers'] == 1) {
            $shiyouxiguan[] = '允许情侣';
        } else {
            $shiyouxiguan[] = '必须单身';
        }
        if ($data['b_bedlate'] == 1) {
            $shiyouxiguan[] = '允许晚睡';
        } else {
            $shiyouxiguan[] = '必须早睡';
        }
        if ($data['b_smoking'] == 1) {
            $shiyouxiguan[] = '允许抽烟';
        } else {
            $shiyouxiguan[] = '严禁抽烟';
        }
        if ($data['b_pet'] == 1) {
            $shiyouxiguan[] = '允许养宠物';
        } else {
            $shiyouxiguan[] = '不许养宠物';
        }
        $data['address'] = $data['province'];
        $data['jiaxiang'] = $data['hometown'];
        $data['dianhua'] = $data['phone'];
        $data['xiguan'] = implode('、', $xiguan);
        $data['shiyouxiguan'] = implode('、', $shiyouxiguan);
        //获取标签
        $label[0]['tit'] = "个性";
        $label[0]['color'] = "#f4b1f6";
        $label[0]['text'] = [];
        $label[1]['tit'] = "运动";
        $label[1]['color'] = "#4957b8";
        $label[1]['text'] = [];
        $label[2]['tit'] = "娱乐";
        $label[2]['color'] = "#ffb02f";
        $label[2]['text'] = [];
        if (!empty($data['personality'])) {
            $personality = explode(',', $data['personality']);
            foreach ($personality as $key => $value) {
                $biaoqian1 = M('taste')->field('tasname')->where('id=' . $value)->find();
                array_push($label[0]['text'], $biaoqian1['tasname']);
            }
        }
        if (!empty($data['motion'])) {
            $motion = explode(',', $data['motion']);
            foreach ($motion as $key => $value) {
                $biaoqian2 = M('taste')->field('tasname')->where('id=' . $value)->find();
                array_push($label[1]['text'], $biaoqian2['tasname']);
            }
        }
        if (!empty($data['entertainment'])) {
            $entertainment = explode(',', $data['entertainment']);
            foreach ($entertainment as $key => $value) {
                $biaoqian3 = M('taste')->field('tasname')->where('id=' . $value)->find();
                array_push($label[2]['text'], $biaoqian3['tasname']);
            }
        }
        $res['info'] = $data;
        //性别
        $res['index5'] = intval($data['is_sex']);
        $res['index6'] = intval($data['is_lookfor']);
        $res['biaoqian'] = $label;
        //获取修改信息  
        
        $res['nianlingduan'] = M('age')->field('aid,a_name')->select();
        foreach ($res['nianlingduan'] as $key => $value) {
            if($data['age']==$value['a_name']){
                $res['index7'] = $key;
            }
        }
        
        //以是否有学校来判断是否是高级匹配
        if($data['school']){
            //获取我的毕业学校
            $biyexuexiao = M('school')->field('s_name,s_city')->where('sid= '. $data['school'])->find();
            //获取城市
            $res['erji_city'] = M('city')->field('cid,c_name as name')->select();
            //获取学校
            foreach ($res['erji_city'] as $key => $value) {
                $erji_school =  M('school')->field('sid,s_name as name')->where('s_city="' . $value['name'] .'"')->select();
                if($value['name']==$biyexuexiao['s_city']){
                    $res['index_city'] = $key;
                    foreach ($erji_school as $k => $v) {
                        if($v['name'] == $biyexuexiao['s_name']){
                            $res['index_school'] = $k;
                            break;
                        }
                    }
                }
                $res['erji_school'][] = $erji_school;
            }
    
            $res['xingzuo'] = M('constellation')->field('id,c_name')->select();
            foreach ($res['xingzuo'] as $key => $value) {
                if($data['constellation']==$value['id']){
                    $res['index2'] = $key;
                }
            }
            $res['zhiye'] = M('work')->field('id,w_name')->select();
            foreach ($res['zhiye'] as $key => $value) {
                if($data['work']==$value['id']){
                    $res['index4'] = $key;
                }
            }
        }
//        var_dump($res);die;
        if (!empty($data)) {
            xformatOutPutJsonData('success', $res, '');
        }
    }

    /**
     * @desc arraySort php二维数组排序 按照指定的key 对数组进行排序
     * @param array $arr 将要排序的数组
     * @param string $keys 指定排序的key
     * @param string $type 排序类型 asc | desc
     * @return array
     */
    public function arraySort($arr, $keys, $type = 'asc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }

        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);

        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /*
     * 获取时间差 折算为天
     */

    public function gettimeDifference($a, $b) {
        $time = ($a - $b) / 60 / 60 / 24;
        return $time;
    }

    /*
     * 租房时长匹配
     * $score   分数
     * $ulongtime  当前用户租房时长
     * $plongtime  匹配用户租房时长
     */

    public function CalculateRenting($ulongtime, $plongtime) {
        switch ($ulongtime) {
            //当前用户<3个月
            case 1:
                switch ($plongtime) {
                    //匹配用户<3个月
                    case 1:
                        $score = 30;
                        break;
                    //匹配用户3-6个月
                    case 2:
                        $score = 20;
                        break;
                    //匹配用户6-12个月
                    case 3:
                        $score = 20;
                        break;
                    //匹配用户>12个月
                    case 4:
                        $score = 10;
                        break;
                }
                break;
            //当前用户3-6个月
            case 2:
                switch ($plongtime) {
                    //匹配用户<3个月
                    case 1:
                        $score = -10;
                        break;
                    //匹配用户3-6个月
                    case 2:
                        $score = 30;
                        break;
                    //匹配用户6-12个月
                    case 3:
                        $score = 20;
                        break;
                    //匹配用户>12个月
                    case 4:
                        $score = 10;
                        break;
                }
                break;
            //当前用户6-12个月
            case 3:
                switch ($plongtime) {
                    //匹配用户<3个月
                    case 1:
                        $score = -30;
                        break;
                    //匹配用户3-6个月
                    case 2:
                        $score = -10;
                        break;
                    //匹配用户6-12个月
                    case 3:
                        $score = 30;
                        break;
                    //匹配用户>12个月
                    case 4:
                        $score = 20;
                        break;
                }
                break;
            //当前用户>12个月
            case 4:
                switch ($plongtime) {
                    //匹配用户<3个月
                    case 1:
                        $score = -30;
                        break;
                    //匹配用户3-6个月
                    case 2:
                        $score = -30;
                        break;
                    //匹配用户6-12个月
                    case 3:
                        $score = -10;
                        break;
                    //匹配用户>12个月
                    case 4:
                        $score = 30;
                        break;
                }
                break;
        }
        return $score;
    }

    /*
     * 预算匹配
     * $score   分数
     * $ulongtime  当前用户预算值
     * $plongtime  匹配用户预算值
     */

    public function Budget($ubudget, $pbudget) {
        switch ($ubudget) {
            //当前用户<1500
            case 1:
                switch ($pbudget) {
                    //匹配用户<1500
                    case 1:
                        $score = 30;
                        break;
                    //1500<= 匹配用户预算 <2000
                    case 2:
                        $score = -10;
                        break;
                    //2000<= 匹配用户预算 <3000
                    case 3:
                        $score = -30;
                        break;
                    //3000<= 匹配用户预算 <5000
//                    case 4:
//                        $score = 10;
//                        break;
//                    //5000<= 匹配用户预算
//                    case 5:
//                        $score = 10;
//                        break;
                }
                break;
            //1500<= 当前用户 <2000
            case 2:
                switch ($plongtime) {
                    //匹配用户<1500
                    case 1:
                        $score = -20;
                        break;
                    //1500<= 匹配用户预算 <2000
                    case 2:
                        $score = 30;
                        break;
                    //2000<= 匹配用户预算 <3000
                    case 3:
                        $score = -10;
                        break;
                    //3000<= 匹配用户预算 <5000
                    case 4:
                        $score = -30;
                        break;
                    //5000<= 匹配用户预算
//                    case 5:
//                        $score = 10;
//                        break;
                }
                break;
            //2000<= 当前用户 <3000
            case 3:
                switch ($plongtime) {
                    //匹配用户<1500
                    case 1:
                        $score = -30;
                        break;
                    //1500<= 匹配用户预算 <2000
                    case 2:
                        $score = -10;
                        break;
                    //2000<= 匹配用户预算 <3000
                    case 3:
                        $score = 30;
                        break;
                    //3000<= 匹配用户预算 <5000
                    case 4:
                        $score = -10;
                        break;
                    //5000<= 匹配用户预算
                    case 5:
                        $score = -30;
                        break;
                }
                break;
            //3000<= 当前用户 <5000
            case 4:
                switch ($plongtime) {
                    ///匹配用户<1500
//                    case 1:
//                        $score = -30;
//                        break;
                    //1500<= 匹配用户预算 <2000
                    case 2:
                        $score = -30;
                        break;
                    //2000<= 匹配用户预算 <3000
                    case 3:
                        $score = -10;
                        break;
                    //3000<= 匹配用户预算 <5000
                    case 4:
                        $score = 30;
                        break;
                    //5000<= 匹配用户预算
                    case 5:
                        $score = -10;
                        break;
                }
                break;
            //5000<=当前用户
            case 5:
                switch ($plongtime) {
//                    ///匹配用户<1500
//                    case 1:
//                        $score = -30;
//                        break;
//                    //1500<= 匹配用户预算 <2000
//                    case 2:
//                        $score = -30;
//                        break;
                    //2000<= 匹配用户预算 <3000
                    case 3:
                        $score = -30;
                        break;
                    //3000<= 匹配用户预算 <5000
                    case 4:
                        $score = -10;
                        break;
                    //5000<= 匹配用户预算
                    case 5:
                        $score = 30;
                        break;
                }
                break;
        }
        return $score;
    }

    /*
     * json方法
     * $code返回码(1 成功 0失败) $type 类型（成功或者失败 success error） $result 数据信息(array)
     */

    private function get_json($code, $type, $result = []) {
        $data['code'] = $code;
        $data['msg'] = $type;
        $data['value'] = $result;
        echo json_encode($data);
        exit;
    }

    //将token存入缓存  获取token
    public function getTokenCode($uid,$type="roomer") {
        if($type=="landlord"){
            //获取9位 字母数字组合
            $value = $this->getRandomString(9);
            //将token存入缓存  3天
            S('user_landlord' . $uid, $value);
    //        30天
    //        S('user_' . $uid, $value, 2592000);
            return $value;
        }else if($type=="roomer"){
            //获取8位 字母数字组合
            $value = $this->getRandomString(8);
            //将token存入缓存  3天
            S('user_' . $uid, $value);
    //        30天
    //        S('user_' . $uid, $value, 2592000);
            return $value;
        }
    }

    //随机生成字母数字组合
    function getRandomString($len, $chars = null) {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000 * (double) microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }
    
    //获取初级匹配最大金额
    function getMatchSum($is = 1,$PosWeight=200) {
        $pmatch = M('pmatch')->select();
        //地点跟租房时长
        $qsum = bcadd(bcmul($pmatch[0]['place'],$PosWeight), $pmatch[0]['q_enancytime']);
        $sum = bcadd($qsum, $pmatch[0]['enancytime']);
        if ($is == 2) {
            //标签5*n
            $label = M('taste')->count();
            $labelsum = bcmul($label, intval($pmatch[0]['tag']));
            $sum = bcadd($sum, $labelsum);
            //年龄20
            $sum+=intval($pmatch[0]['age']);
            //学校20
            $sum+=intval($pmatch[0]['school']);
            //星座20
            $sum+=intval($pmatch[0]['constellation']);
            //预算30
            $sum+=intval($pmatch[0]['budget']);
            //家乡20
            $sum+=intval($pmatch[0]['address']);
            //职业30
            $sum+=intval($pmatch[0]['work']);
        }
        return $sum;
    }

    //获取匹配值
    function getMatchNum() {
        $pmatch = M('pmatch')->find();
        return $pmatch;
    }

    function getPhone(){
        /**
         * error code 说明.
         * <ul>

        *    <li>-41001: encodingAesKey 非法</li>
        *    <li>-41003: aes 解密失败</li>
        *    <li>-41004: 解密后得到的buffer非法</li>
        *    <li>-41005: base64加密失败</li>
        *    <li>-41016: base64解密失败</li>
        * </ul>
        */
        static $OK = 0;
        static $IllegalAesKey = -41001;
        static $IllegalIv = -41002;
        static $IllegalBuffer = -41003;
        static $DecodeBase64Error = -41004;

        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }

        
        $sessionKey = S($data['openid'] . '_key');
        $appid = 'wx8f2ed65c8aee3563';
        $encryptedData = $data['encryptedData'];
        $iv = $data['iv'];
        $error;
        if (strlen($sessionKey) != 24) {
			$error = $IllegalAesKey;
		}
		$aesKey=base64_decode($sessionKey);

        
		if (strlen($iv) != 24) {
			$error =  $IllegalIv;
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        // xformatOutPutJsonData('test', $result, $data['code']);
        if(!$result){
            //应该是会话密钥 session_key 有效性过期了
            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wx8f2ed65c8aee3563&secret=4f2e7944beac651f1b5d49dd53e36328&js_code=' . $data['code'] . '&grant_type=authorization_code';
            $result = file_get_contents($url);
            $result_arr = json_decode($result, TRUE);
            //xformatOutPutJsonData('success', $result_arr, '');
            $openid = $result_arr['openid'];
            S($openid . '_key', $result_arr['session_key']);
            $sessionKey = S($data['openid'] . '_key');
            $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        }
		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			$error = $IllegalBuffer;
		}
		if( $dataObj->watermark->appid != $this->appid )
		{
			$error = $IllegalBuffer;
		}
        $error =  $OK;
        xformatOutPutJsonData('success', $result, $error);
    }
    //计算两地的距离
    function GetDistance($long1, $lat1, $long2, $lat2) {
        $EARTH_RADIUS = 6378.137;
        $lat1 = $lat1 * M_PI / 180.0;
        $lat2 = $lat2 * M_PI / 180.0;
        $a = $lat1 - $lat2;
        $b = ($long1 - $long2) * M_PI / 180.0;
        $sa2 = sin($a / 2.0);
        $sb2 = sin($b / 2.0);
        $d = 2 * $EARTH_RADIUS * asin(sqrt($sa2 * $sa2 + cos($lat1) * cos($lat2) *  $sb2 * $sb2));
        return $d;
    }
    public function testDis(){
        dump($this->GetDistance(116.351944,39.980378, 116.160264,39.764288));
    }

    //上传房源信息的图片，根据微信的uploadFile接收chooseImage的tempFilePaths
    public function uploadRoomImg() {
        $Data = I('post.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $file = $_FILES;
        //检查图片是否合法
        $imgCheck = A('Home/Chat')->mediaCheck($file);
        $imgCheck = json_decode(stripslashes($imgCheck));
        $imgCheck = json_decode(json_encode($imgCheck), true);
        if($imgCheck['errcode']==87014){
            xformatOutPutJsonData('fail', $Data['index'], "有违法违规内容");
        }else if($imgCheck['errcode']==-1){
            xformatOutPutJsonData('fail', $Data['index'], "图片尺寸超过 750px x 1334px");
        }else{
            $config = array(
                'rootPath' => "./Public/RoomImage/",
                'exts' => array('jpg','png', 'jpeg', 'bmp'),
                'autoSub' => true,
                'subName'  => array('date','Ymd'),
                'saveName' => $uid.'-'.$Data['index'].'-'.time(),
                'replace' => true,
            );
            $upload = new \Think\Upload($config);
            $info = $upload->upload($file);
            if(!$info) {// 上传错误提示错误信息
                xformatOutPutJsonData('fail', 1, $upload->getError());
            }else{// 上传成功 获取上传文件信息
                $fileurl =str_replace('http','https',IMG_PATH). '/Public/RoomImage/'. $info['file']['savepath'] . $info['file']['savename'];
                xformatOutPutJsonData('success', $Data['index'], $fileurl);
            }
        }
    }

    //房源初级匹配
    public function roomPrimaryMatch() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $uid)) {
            xformatOutPutJsonData('fail', 1, '网络错误2！');
        }
        //当前用户基本租房信息
        $userinfo = M('user')->field('shangquan,ditie,checkintime,checkoutime,tenant_long,lovers,is_place')->where('id=' . $uid)->find();
        //获取用户想找的室友性别
        $userpm = M('user_primary_matching')->field('sex')->where('uid=' . $uid)->find();
        $looksex = $userpm['sex'];
        //dump($userinfo);
        //获取我的位置
        if ($userinfo['is_place'] == 1) {
            $myPosition = M('circles')->field('city_name as city,lon,lat')->where('id='. $userinfo['shangquan'])->find();
        }else{
            $myPosition = M('subways')->field('city,lon,lat')->where('id='. $userinfo['ditie'])->find();
        }
        //获取那些被我拉黑的房源
        $heimingdan = M('shoucang')->field('dislikehouse')->where('uid=' . $uid)->find();
        if($heimingdan){
            $shanchumingdan = json_decode($heimingdan['dislikehouse'],true);
        }else{
            $shanchumingdan = '';
        }
        //设置不同城市权重
        $PosWeight = M('city')->field('city_score')->where('c_name="'.$myPosition['city'].'"')->find();
        
        /*设置要选取的房源地点的经纬度界限
        1.同一条经线上，纬度相差1°，其距离相差约111千米。
        2..在同一条纬线上（假设此纬线的纬度为Φ），经度相差1°对应的实际弧长大约为111×cosΦ千米。
        假设只显示方圆10公里以内的房源，那么纬度相差0.09度，经度相差0.1度吧
        */
        $sqlCondition = "lon<".($userinfo['lon']+0.1). "AND lon>".($userinfo['lon']-0.1) 
                        . "AND lat<".($userinfo['lat']+0.09)." AND lat>" .($userinfo['lat']-0.09);
        
        //蛋壳公寓的房源
        $danke = M('danke')->field('id,room_title as title,lon, lat,feature,normal_price,promotion_price,room_id,room_size,room_type,room_floor,room_subway,roommate,room_image_link')->where($sqlCondition)->select();
        foreach ($danke as $key => $value) {
            //删除我拉黑的房源
            $index = array_search($value['room_id'],explode(",",$shanchumingdan['danke']));
            if($index !== FALSE)
                continue;
            $value['brand'] = '蛋壳';
            $value['id'] = 'danke'.$value['id'];
            //室友匹配
            $sexsuit = true;
            $danke[$key]['roommates']=json_decode(strtr(strtr($value['roommate'],": ",':'),"'",'"'));
            // dump($danke[$key]['roommates']);die;
            // $value['roommates'] = json_decode(strtr(strtr($value['roommates'],": ",':'),"'",'"'));
            if($looksex==1){
                //排除有女性室友的房间 
                foreach($danke[$key]['roommates'] as $k=>$v){
                    if($v->Gender=="女"){
                        $sexsuit = false;
                        break;
                    }
                }
            }else if($looksex==2){
                //排除有男性室友的房间
                // $roommates = json_decode(strtr(strtr($value['roommates'],": ",':'),"'",'"'));
                foreach($danke[$key]['roommates'] as $k=>$v){
                    if($v->Gender=="男"){
                        $sexsuit = false;
                        break;
                    }
                }
            }
            if(!$sexsuit)
                continue;
            //排除没有空房间的房源
            $hasEmpty = 0;
            foreach($danke[$key]['roommates'] as $k=>$v){
                // xformatOutPutJsonData('test', $v,$v->RoomState);
                if($v->RoomState=="当前房间"||$v->RoomState=="可出租"){
                    $hasEmpty = $hasEmpty+1;
                }
            }
            if($hasEmpty==0)
                continue;
            unset($value['roommate']);
            $fraction = 0;
            //租住地点匹配,计算两地的距离
            $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
            // $value['distance'] = $distance;
            //10公里以内分数为正，10公里以外分数为负
            $fraction += bcmul(10-$distance, $PosWeight['city_score'])/10;
            if($value['promotion_price']!=0){
                $value['price'] = $value['promotion_price'];
            }else{
                $value['price'] = $value['normal_price'];
            }
            if(floatval($value['price'])>2500)
                continue;
            unset($value['promotion_price']);
            unset($value['normal_price']);
            $value['size'] = $value['room_size'];
            $value['type2'] = explode(",",$value['room_type'])[0];
            $value['floor'] = $value['room_floor'].'层';
            $value['transport'] = $value['room_subway'];
            //面积：0-5平米 +0；5-10平米 +10；10-15平米 +15；15以上平米 +20；
            $fraction += floatval($value['room_size']);
            //总楼层：0-6（包括）：-15，大于6：+15；所在楼层越低越好，最高10分
            $floosInfo = explode('/',$value['room_floor']);
            if(intval($floosInfo[1])>6)
                $fraction += 15;
            else $fraction += 15*(intval($floosInfo[1])-intval($floosInfo[0]))/intval($floosInfo[1]);
            //n室m厅：n/m > 3的 – 10
            $typeInfo = explode('室',$value['type2']);
            if(intval($typeInfo[0])/intval($typeInfo[1])>3)
                $fraction -= 10;
            unset($value['info']);
            unset($value['location']);
            unset($value['room_size']);
            unset($value['room_type']);
            unset($value['room_floor']);
            unset($value['room_subway']);
            $images = json_decode($value['room_image_link'],true);
            $value['image'] = $images[0];
            unset($value['room_image_link']);
            $value['ratio'] = round($fraction/($PosWeight['city_score']/75),2);
            $value['labels'] = explode(",",$value['feature']);
            if(count($value['labels'])<4&&$hasEmpty>1)
                $value['labels'][] = '多间空房';
            if($value['rent_whole']==1){
                $value['labels'][] = '整租';
                // $value['ratio'] = $value['ratio'] + 10;
            }
            else if($value['rent_whole']==0)
                $value['labels'][] = '合租';
            unset($value['feature']);
            $value['room_id'] = 'danke'.$value['room_id'];
            $res[] = $value;
        }
        //我爱我家的的房源
        $woaiwojia = M('woaiwojia')->field('*')->where($sqlCondition)->select();
        foreach ($woaiwojia as $key => $value) {
            //删除我拉黑的房源
            $index = array_search($value['room_id'],explode(",",$shanchumingdan['woaiwojia']));
            // xformatOutPutJsonData($index, $shanchumingdan, explode(",",$shanchumingdan['ziruyu']));
            if($index !== FALSE)
                continue;
            $value['brand'] = '我爱我家';
            $value['id'] = 'woaiwojia'.$value['id'];
            $value['title'] = $value['room_title'];
            unset($value['room_title']);
            unset($value['pay_mode']);
            //室友匹配 我爱我家没有室友

            $fraction = 0;
            //租住地点匹配,计算两地的距离
            $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
            // $value['distance'] = $distance;
            //10公里以内分数为正，10公里以外分数为负
            $fraction += bcmul(10-$distance, $PosWeight['city_score'])/10;
            $value['price'] = $value['normal_price'];
            if(floatval($value['price'])>2500)
                continue;
            unset($value['normal_price']);
            $value['size'] = $value['room_size'];
            $value['type2'] = $value['room_type'];
            $value['floor'] = $value['room_floor'];
            if($value['room_subway']!='')
                $value['transport'] = $value['room_subway'];
            else $value['transport'] = $value['room_business'] ."附近" .$value['room_location'];
            //面积：0-5平米 +0；5-10平米 +10；10-15平米 +15；15以上平米 +20；
            $fraction += floatval($value['size']);
            //总楼层：0-6（包括）：-15，大于6：+15；所在楼层越低越好，最高10分
            $floosInfo = explode('/',$value['floor']);
            if(intval($floosInfo[1])>6)
                $fraction += 15;
            else{
                if($floosInfo[1]=='底')
                    $fraction += 15;
                if($floosInfo[1]=='低')
                    $fraction += 12;
                if($floosInfo[1]=='中')
                    $fraction += 9;
                if($floosInfo[1]=='高')
                    $fraction += 6;
                if($floosInfo[1]=='顶')
                    $fraction += 3;
            }
            //n室m厅：n/m > 3的 – 10
            $typeInfo = explode('室',$value['type2']);
            if(intval($typeInfo[0])/intval($typeInfo[1])>3)
                $fraction -= 10;
            unset($value['lon']);
            unset($value['lat']);
            unset($value['info']);
            unset($value['location']);
            unset($value['room_size']);
            unset($value['room_type']);
            unset($value['room_floor']);
            unset($value['room_subway']);
            unset($value['room_bussiness']);
            unset($value['room_location']);
            if($value['room_image_link']!='[]'){
                $images = json_decode($value['room_image_link'],true);
                $value['image'] = $images[0];
            }else
                $value['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
            unset($value['room_image_link']);
            $value['ratio'] = round($fraction/($PosWeight['city_score']/50),2);
            $value['labels'] = explode(",",$value['feature']);
            if(count($value['labels'])<4&& $value['room_decoration']!='')
                $value['labels'][] = $value['room_decoration'];
            if(count($value['labels'])<4 && $value['room_building_type']!=''&& $value['room_building_type']!='其他')
                $value['labels'][] = $value['room_building_type'];
            if($value['room_rent_type']!=''&& $value['room_rent_type']!='其他')
                $value['labels'][] = $value['room_rent_type'];
            unset($value['feature']);
            unset($value['room_decoration']);
            unset($value['room_building_type']);
            unset($value['room_warming']);
            unset($value['room_rent_type']);
            unset($value['room_looking_type']);
            unset($value['room_url']);
            unset($value['room_dir']);
            unset($value['room_business']);
            unset($value['city']);
            $value['room_id'] = 'woaiwojia'.$value['room_id'];
            $res[] = $value;
        }
        //按照匹配分数排序
        $sort = array_column($res, 'ratio');      
        array_multisort($sort, SORT_DESC, $res);  
        $res = array_slice($res,0,50);
        foreach($res as $key => $value){
            if($value['ratio']>100)
                $res[$key]['ratio'] = 99.99;
        }
        // $res = array_slice($res,0,5);
        if (!empty($res)) {
            xformatOutPutJsonData('success', $res, '');
        } else {
            xformatOutPutJsonData('success', 1, '');
        }
    }
    //房源高级匹配
    public function roomMatch() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $uid)) {
            xformatOutPutJsonData('fail', 1, '网络错误2！');
        }
        $count = $Data['count'];
        // xformatOutPutJsonData(json_decode(str_replace("&quot;","'",$Data['condition']),true), json_decode($Data['condition'],true), $Data);
        // if($Data['condition']!=NULL)
        //     $Data = json_decode(str_replace("&quot;","'",$Data['condition']),true);
        //设置房源各个特征的权重
        $weight = array(
            'distance' => 100,//距离权重
            'subway_line' => 40,//同一条地铁线路加分
            'time' => 0,//入住时间匹配
            'price' => 20,//价格匹配
            'huxing' => 5,//户型权重匹配，公用区域要比较大
            'floor_type' => 2,//电梯房加分
            'floor_count' => 5,//楼梯越高分数越低，但是大于6层的有电梯
        );
        if($Data['request']==1){
            //$Data['request']=1为保存需求，为-1为不保存需求，有这个参数说明是点了筛选界面之后的请求
            $update1['lon'] = $Data['lon'];
            $update1['lat'] = $Data['lat'];
            $update1['address'] = $Data['address'];
            $update1['city_no'] = $Data['city'];
            $update1['ditie'] = $Data['subway'];
            $update1['checkintime'] = $Data['earlydate'];
            $update1['checkoutime'] = $Data['lastdate'];
            $update1['tenant_long'] = $Data['duration'];
            $update1['budget'] = $Data['budget'];
            // if(!M('user')->where('id='.$uid)->save($update1)){
            //     xformatOutPutJsonData('fail', M()->getLastSql(), '服务器更新失败');
            // }
            M('user')->where('id='.$uid)->save($update1);
            $update2['sex'] = $Data['gender'];
            $update2['tradition'] = $Data['tradition'];
            $update2['rent_type'] = $Data['rent_type'];
            $update2['type'] = $Data['type'];
            $res1 = M('user_primary_matching')->where('uid='.$uid)->find();
            if($res1){
                // if(!M('user_primary_matching')->where('uid='.$uid)->save($update2)){
                //     xformatOutPutJsonData('fail', M()->getLastSql(), '服务器更新失败');
                // }
                M('user_primary_matching')->where('uid='.$uid)->save($update2);
            }else{   
                $update2['uid'] = $uid;
                if(!M('user_primary_matching')->add($update2)){
                    xformatOutPutJsonData('fail', M()->getLastSql(), '服务器插入失败');
                }
            }
        }
        if($Data['request']==-1){
            $looksex = $Data['gender'];
            $lookTradition = $Data['tradition']?explode(",",$Data['tradition']):NULL;
            $lookRentType = $Data['rent_type'];
            $lookType = $Data['type'];

            $look_start_date  = $Data['earlydate'];
            $look_end_date  = $Data['lastdate'];
            $look_duration  = $Data['duration'];
            $look_budget  = $Data['budget'];

            //获取我的位置
            $userinfo = M('user')->field('sex')->where('id=' . $uid)->find();
            $userinfo['lon'] = $Data['lon'];
            $userinfo['lat'] = $Data['lat'];
            if(!empty($Data['subway']) && intval($Data['subway'])>0){
                // $myPosition = M('subways')->field('city,lon,lat')->where('id='. $Data['subway'])->find();
                //找出所有跟当前房屋地铁站在一条地铁线路的地铁站
                $subways_line = array();
                $room_subway = M('subways')->field('city,station')->where("id=".$Data['subway'])->find();
                $room_line = M('subways')->field('city,line')->where('city="'.$room_subway['city'].'" AND station="'.$room_subway['station'].'"')->select();
                foreach($room_line as $k=>$v){
                    $subways_result =  M('subways')->field('id')->where('city="'.$v['city'].' "AND line="' .$v['line'].'"')->select();
                    $subways_line = array_merge($subways_line, array_column($subways_result, 'id'));
                }
                if(count($subways_line)==0){
                    array_push($subways_line,-1);
                }
            }else{
                $subways_line[] = -1;
            }
        }else{
            //当前用户基本租房信息
            $userinfo = M('user')->field('sex,lon,lat,ditie,checkintime,checkoutime,tenant_long,budget')->where('id=' . $uid)->find();
            $look_start_date  = $userinfo['checkintime'];
            $look_end_date  = $userinfo['checkoutime'];
            $look_duration  = $userinfo['tenant_long'];
            $look_budget  = $userinfo['budget'];
            //获取用户想找的室友性别 通勤方式 租住类型 房源类型
            $userpm = M('user_primary_matching')->field('sex,tradition,rent_type,type')->where('uid=' . $uid)->find();
            if($userpm){
                $looksex = $userpm['sex'];
                $lookTradition = explode(",",$userpm['tradition']);
                $lookRentType = $userpm['rent_type'];
                $lookType = $userpm['type'];
            }else{
                $lookTradition = NULL;
            }
            //获取我的位置
            if (intval($userinfo['ditie']) == 0) {
                $subways_line[] = 0;
            }else{
                if($lookTradition!=NULL && array_search(3,$lookTradition)!==FALSE){
                    //找出所有跟当前房屋地铁站在一条地铁线路的地铁站
                    $room_subway = M('subways')->field('city,station')->where("id=".$userinfo['ditie'])->find();
                    $room_line = M('subways')->field('city,line')->where('city="'.$room_subway['city'].'" AND station="'.$room_subway['station'].'"')->select();
                    $subways_line = array();
                    foreach($room_line as $k=>$v){
                        $subways_result =  M('subways')->field('id')->where('city="'.$v['city'].' "AND line="' .$v['line'].'"')->select();
                        $subways_line = array_merge($subways_line, array_column($subways_result, 'id'));
                    }
                    if(count($subways_line)==0){
                        array_push($subways_line,-1);
                    }
                }else{
                    $subways_line[] = -1;
                }
            }
        }
        //dump($userinfo);
        /*设置要选取的房源地点的经纬度界限
        1.同一条经线上，纬度相差1°，其距离相差约111千米。
        2..在同一条纬线上（假设此纬线的纬度为Φ），经度相差1°对应的实际弧长大约为111×cosΦ千米。
        假设只显示方圆10公里以内的房源，那么纬度相差0.09度，经度相差0.1度吧
        */
        //确定选取边界,如果没有限制通勤方式，那么设置成10公里以内
        $londiff = 0.1;
        $latdiff = 0.09;
        $maxDis = 10;
        $sqlCondition = "";
        if($lookTradition!=NULL){
            //需要筛选通勤方式
            if(array_search(1,$lookTradition)!==FALSE){
                //选择了步行可达，那么设置成2公里以内
                $londiff = 0.02;
                $latdiff = 0.018;
                $maxDis = 2;
            }else if(array_search(2,$lookTradition)!==FALSE){
                //选择了骑行可达，那么设置成5公里以内
                $londiff = 0.05;
                $latdiff = 0.045;
                $maxDis = 5;
            }
            if (intval($userinfo['ditie']) != 0 && array_search(3,$lookTradition)!==FALSE){
                $sqlCondition = "subway in (".implode(",",$subways_line).") AND ";
            }
        }
        // xformatOutPutJsonData($lookTradition, $look_budget,$look_budget>0);
        $sqlCondition = $sqlCondition. "lon<".($userinfo['lon']+$londiff). "AND lon>".($userinfo['lon']-$londiff). "AND lat<".($userinfo['lat']+$latdiff)." AND lat>" .($userinfo['lat']-$latdiff);
        if($look_budget>0){
            $look_budget = M('budget')->field('low,high')->where("id=".$look_budget)->find();
        }
        //获取那些被我拉黑的房源
        $heimingdan = M('shoucang')->field('dislikehouse')->where('uid=' . $uid)->find();
        if($heimingdan){
            $shanchumingdan = json_decode($heimingdan['dislikehouse'],true);
        }else{

        }
        //设置不同城市权重
        // $PosWeight = M('city')->field('city_score')->where('c_name="'.$myPosition['city'].'"')->find();
        
        //dump($sqlCondition);
        // $selectRooms = M('rooms')->field('id,BRAND,ROOM_NAME,lon,lat,INFO_1,INFO_2,PRICE,ROOMMATES,SUBPAGE_IMAGE_DIR')
        //                 ->where($sqlCondition)->select();
        // foreach ($selectRooms as $key => $value) {
        //     //删除我拉黑的房源
        //     $index = array_search($value['id'],$shanchumingdan);
        //     if($index !== FALSE)
        //         continue;
        //     //室友匹配
        //     $sexsuit = true;
        //     $selectRooms[$key]['roommates']=json_decode(strtr(strtr($value['roommates'],": ",':'),"'",'"'));
        //     // dump($selectRooms[$key]['roommates']);die;
        //     $value['roommates'] = json_decode(strtr(strtr($value['roommates'],": ",':'),"'",'"'));
        //     if($looksex==1){
        //         //排除有女性室友的房间 
        //         foreach($selectRooms[$key]['roommates'] as $k=>$v){
        //             if($v->sex=="girl"){
        //                 $sexsuit = false;
        //                 break;
        //             }
        //         }
        //     }else if($looksex==2){
        //         //排除有男性室友的房间
        //         // $roommates = json_decode(strtr(strtr($value['roommates'],": ",':'),"'",'"'));
        //         foreach($selectRooms[$key]['roommates'] as $k=>$v){
        //             if($v->sex=="boy"){
        //                 $sexsuit = false;
        //                 break;
        //             }
        //         }
        //     }
        //     if(!$sexsuit)
        //         continue;
        //     //排除没有空房间的房源
        //     $hasEmpty = false;
        //     foreach($selectRooms[$key]['roommates'] as $k=>$v){
        //         // xformatOutPutJsonData('test', $v,$v->roomState);
        //         if($v->roomState=="当前房源"){
        //             $hasEmpty = true;
        //             break;
        //         }
        //     }
        //     if(!$hasEmpty)
        //         continue;
        //     $fraction = 0;
        //     //租住地点匹配,计算两地的距离
        //     $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
        //     // $value['distance'] = $distance;
        //     //10公里以内分数为正，10公里以外分数为负
        //     $fraction += bcmul(10-$distance, $PosWeight['city_score'])/10;
        //     //租住预算匹配，预算符合，越低分越高，最高50，预算不符合减最多100
        //     $price = intval($value['price']);
        //     if($price>=$bud_low&&$price<=$bud_high){
        //         $score = 50*(1-($bud_high-$price)/($bud_high-$bud_low));
        //     }else if($price<$bud_low){
        //         $score = ($price-$bud_low)/25;
        //         // continue;
        //     }else{
        //         $score = ($bud_high - $price)/10;
        //         // continue;
        //     }
        //     $fraction+= $score;
        //     //房屋属性加分
        //     $roomproperties = explode(",",$value['info_1']);
        //     //面积：0-5平米 +0；5-10平米 +10；10-15平米 +15；15以上平米 +20；
        //     $size = str_replace("约","",str_replace('平米','',$roomproperties[0]));
        //     $fraction += floatval($size);
        //     //总楼层：0-6（包括）：-15，大于6：+15；所在楼层越低越好，最高10分
        //     $floor = strtr($roomproperties[1],'层','');
        //     $floosInfo = explode('/',$floor);
        //     if(intval($floosInfo[1])>6)
        //         $fraction += 15;
        //     else $fraction += 15*(intval($floosInfo[1])-intval($floosInfo[0]))/intval($floosInfo[1]);
        //     //n室m厅：n/m > 3的 – 10
        //     $type = strtr($roomproperties[2],'厅','');
        //     $typeInfo = explode('室',$type);
        //     if(intval($typeInfo[0])/intval($typeInfo[1])>3)
        //         $fraction -= 10;
        //     //房屋标签：INFO_2字段有几个，分别加+5；
        //     $fraction  += count(explode(",",$value['info_2']))*5;
        //     if($fraction>0){
        //         //这里的分母是城市权重分数/300
        //         // xformatOutPutJsonData('test', $fraction, $PosWeight['city_score']);
        //         // $value['score'] = $fraction;
        //         $value['ratio'] = round($fraction/($PosWeight['city_score']/75),2);
        //         if($value['ratio']>100)
        //             $value['ratio'] = 99.99;
        //         $value['title'] = explode(" ・ ",$value['room_name'])[1];
        //         if($value['brand']=='ziroom')
        //             $value['brand'] = '自如';
        //         unset($value['roommates']);
        //         $value['image'] = str_replace('http','https',IMG_PATH). '/Public/RoomImage/' .(explode(",",$value['subpage_image_dir'])[0]);
        //         unset($value['subpage_image_dir']);
        //         $value['size'] = $size;
        //         $value['type1'] = explode(" ・ ",$value['room_name'])[0];
        //         unset($value['room_name']);
        //         $value['type2'] = $roomproperties[2];
        //         $value['floor'] = $roomproperties[1];
        //         $value['transport'] = $roomproperties[3];
        //         $value['labels'] = explode(",",$value['info_2']);
        //         unset($value['info_2']);
        //         unset($value['info_1']);
        //         unset($value['lon']);
        //         unset($value['lat']);
        //         $res[] = $value;
        //     }
        //     // xformatOutPutJsonData($floor, intval($floosInfo[0]), $floosInfo);
        // }

        // 自如寓的房源展示
        // $ziruyus = M('ziruyu')->field('id,title,location,lon,lat,price,info,subpage_image_dir')->where($sqlCondition)->select();
        // foreach ($ziruyus as $key => $value) {
        //     //删除我拉黑的房源
        //     $index = array_search($value['id'],explode(",",$shanchumingdan['ziruyu']));
        //     if($index !== FALSE)
        //         continue;
        //     $value['id'] = 'ziruyu'.$value['id'];
        //     $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
        //     if($distance>10)
        //         continue;
        //     $fraction = 100;
        //     $fraction -= (10-$distance)*3;
        //     if($value['id']==5)
        //         //六人间
        //         $fraction -= 10;
        //     elseif ($value['id']==7) {
        //         //四人间
        //         $fraction -= 5;
        //     }
        //     unset($value['lon']);
        //     unset($value['lat']);
        //     $roomproperties = explode(",",$value['info']);
        //     //面积：0-5平米 +0；5-10平米 +10；10-15平米 +15；15以上平米 +20；
        //     $size = str_replace("约","",str_replace('平米','',$roomproperties[2]));
        //     $fraction += floatval($size);
        //     $value['size'] = $size;
        //     $value['type2'] = $roomproperties[0];
        //     $value['floor'] = $roomproperties[1];
        //     $value['transport'] = $value['location'];
        //     unset($value['info']);
        //     unset($value['location']);
        //     $images = json_decode($value['subpage_image_dir'],true);
        //     // $value['timg'] = $images;
        //     if($images['卧室']){
        //         $value['image'] = $images['卧室'][0];
        //     }else if($images['睡眠区']){
        //         $value['image'] = $images['睡眠区'][0];
        //     }else if($images['客厅']){
        //         $value['image'] = $images['客厅'][0];
        //     }
        //     // $value['image'] = explode(",",$value['subpage_image_dir'])[0];
        //     unset($value['subpage_image_dir']);
        //     $value['ratio'] = round($fraction,2);
        //     $value['labels'][] = '限时特惠';
        //     $res[] = $value;
        // }
        if($lookType==NULL || ($lookType==3|| $lookType==1)){
            //蛋壳公寓的房源
            $danke = M('danke')->field('id,room_title as title,lon, lat,subway,feature,normal_price,promotion_price,room_id,room_size,room_type,room_floor,room_subway,roommate,room_image_link, rent_whole')->where($sqlCondition)->select();
            // xformatOutPutJsonData('test', $danke, M()->getLastSql());
            foreach ($danke as $key => $value) {
                //删除我拉黑的房源
                $index = array_search($value['room_id'],explode(",",$shanchumingdan['danke']));
                if($index !== FALSE)
                    continue;
                //租住类型匹配
                if($lookRentType==1){
                    //希望整租，排除所有合租的
                    if($value['rent_whole']==0)
                        continue;
                }else if($lookRentType==2){
                    //希望合租，排除所有整租的
                    if($value['rent_whole']==1)
                        continue;
                }
                //室友匹配
                $danke[$key]['roommates']=json_decode(strtr(strtr($value['roommate'],": ",':'),"'",'"'));
                if($lookRentType && $lookRentType!=1 && $looksex && $looksex!=3){
                    $sexsuit = true;
                    // dump($danke[$key]['roommates']);die;
                    // $value['roommates'] = json_decode(strtr(strtr($value['roommates'],": ",':'),"'",'"'));
                    if($looksex==1){
                        //排除有女性室友的房间 
                        foreach($danke[$key]['roommates'] as $k=>$v){
                            if($v->Gender=="女"){
                                $sexsuit = false;
                                break;
                            }
                        }
                    }else if($looksex==2){
                        //排除有男性室友的房间
                        // $roommates = json_decode(strtr(strtr($value['roommates'],": ",':'),"'",'"'));
                        foreach($danke[$key]['roommates'] as $k=>$v){
                            if($v->Gender=="男"){
                                $sexsuit = false;
                                break;
                            }
                        }
                    }
                    if(!$sexsuit)
                        continue;
                }
                //排除没有空房间的房源
                $hasEmpty = 0;
                foreach($danke[$key]['roommates'] as $k=>$v){
                    // xformatOutPutJsonData('test', $v,$v->RoomState);
                    if($v->RoomState=="当前房间"||$v->RoomState=="可出租"){
                        $hasEmpty = $hasEmpty+1;
                        break;
                    }
                }
                // xformatOutPutJsonData('test2', $value, $hasEmpty);
                if($hasEmpty==0)
                    continue;
                unset($value['roommate']);
                //租住地点匹配,计算两地的距离
                $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
                // $value['subway_line'] = 0;
                //排除超过最大公里数的房源
                if($distance>$maxDis){
                    if(array_search($value['subway'],$subways_line)!=FALSE){
                        $fraction = $weight['subway_line']/ceil($distance);
                        // $value['subway_line'] = $weight['subway_line']/ceil($distance);
                    }else continue;
                }else{
                    $fraction = A('Home/Landlord')->getExpValue(0,$weight['distance'],$maxDis,0.1,$distance);
                    if(array_search($value['subway'],$subways_line)!=FALSE){
                        $fraction += $weight['subway_line']/ceil($distance);
                        // $value['subway_line'] = $weight['subway_line']/ceil($distance);
                    }
                }
                if($value['promotion_price']!=0){
                    $value['price'] = $value['promotion_price'];
                }else{
                    $value['price'] = $value['normal_price'];
                }
                //租住预算匹配，预算符合，越低分越高
                $price = intval($value['price']);
                if(!($look_budget>0) || $price<=$look_budget['high']){
                    $fraction += $weight['price'];
                }else{
                    $fraction += $weight['price']/($price / (bcadd($look_budget['low'],$look_budget['high'])/2));
                }
                //n室m厅：n/m 
                $value['type2'] = explode(",",$value['room_type'])[0];
                $shiWeiNum = explode("室",$value['type2']);
                if(intval($shiWeiNum[1])==0)
                    $shiWeiNum = 3;
                else{
                    $shiWeiNum = abs(intval($shiWeiNum[0])/intval($shiWeiNum[1]));
                    if($shiWeiNum>3)
                        $shiWeiNum = 3;
                    else if($shiWeiNum<1)
                        $shiWeiNum = 1;
                }
                $fraction += A('Home/Landlord')->getExpValue(1,$weight['huxing'],3,0.0001,$shiWeiNum);
                //总楼层：0-6（包括）：-15，大于6：+15；所在楼层越低越好，最高10分
                $floosInfo = explode('/',$value['room_floor']);
                if(intval($floosInfo[1])>6){
                    $fraction += $weight['floor_type'];//有电梯
                }
                $floor_diff = intval($floosInfo[0]);
                if($floor_diff>15)
                    $floor_diff = 15;
                else if($floor_diff<1)
                    $floor_diff = 1;
                $fraction += A('Home/Landlord')->getExpValue(1,$weight['floor_count'],15,0.0001,$floor_diff);
                if($fraction<0)
                    continue;
                $value['brand'] = '蛋壳';
                $value['id'] = 'danke'.$value['id'];
                $value['size'] = $value['room_size'];
                $value['floor'] = $value['room_floor'].'层';
                $value['transport'] = $value['room_subway'];
                unset($value['promotion_price']);
                unset($value['normal_price']);
                unset($value['info']);
                unset($value['location']);
                unset($value['room_size']);
                unset($value['room_floor']);
                unset($value['room_subway']);
                $images = json_decode($value['room_image_link'],true);
                $value['image'] = $images[0];
                unset($value['room_image_link']);
                if($lookTradition==NULL || $subways_line[0] ==-1 ||array_search(3,$lookTradition)===FALSE){
                    $value['ratio'] = bcdiv($fraction, $weight['distance']+$weight['price']+$weight['huxing']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                }else{
                    $value['ratio'] = bcdiv($fraction, $weight['distance']+$weight['subway_line']+$weight['price']+$weight['huxing']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                }
                $value['labels'] = explode(",",$value['feature']);
                if($hasEmpty>1)
                    $value['labels'][] = '多间空房';
                // if($value['rent_whole']==1){
                //     $value['labels'][] = '整租';
                //     // $value['ratio'] = $value['ratio'] + 10;
                // }
                // else if($value['rent_whole']==0)
                //     $value['labels'][] = '合租';

                // 去掉合租这个标签
                // $value['labels'][] = explode(",",$value['room_type'])[1]."租";
                unset($value['room_type']);
                unset($value['feature']);
                unset($value['rent_whole']);
                $value['room_id'] = 'danke'.$value['room_id'];
                $res[] = $value;
            }
        }
        if($lookType==NULL || ($lookType==3|| $lookType==1)){
            //我爱我家的的房源
            $woaiwojia = M('woaiwojia')->field('*')->where($sqlCondition)->select();
            // xformatOutPutJsonData('test', $woaiwojia, count($woaiwojia));
            foreach ($woaiwojia as $key => $value) {
                //删除我拉黑的房源
                $index = array_search($value['room_id'],explode(",",$shanchumingdan['woaiwojia']));
                // xformatOutPutJsonData($index, $shanchumingdan, explode(",",$shanchumingdan['ziruyu']));
                if($index !== FALSE)
                    continue;
                //租住类型匹配
                if($lookRentType==1){
                    //希望整租，排除所有合租的
                    if($value['room_rent_type']=="合租")
                        continue;
                }else if($lookRentType==2){
                    //希望合租，排除所有整租的
                    if($value['room_rent_type']=="整租")
                        continue;
                }
                //室友匹配 我爱我家没有室友

                //租住地点匹配,计算两地的距离
                $distance = $this->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
                //排除超过最大公里数的房源
                if($distance>$maxDis){
                    if(array_search($value['subway'],$subways_line)!=FALSE){
                        $fraction = $weight['subway_line']/ceil($distance);
                    }else continue;
                }else{
                    $fraction = A('Home/Landlord')->getExpValue(0,$weight['distance'],$maxDis,0.1,$distance);
                    if(array_search($value['subway'],$subways_line)!=FALSE){
                        $fraction += $weight['subway_line']/ceil($distance);
                    }
                }
                // $value['fraction'][] = $fraction;
                $value['price'] = $value['normal_price'];
                //租住预算匹配，预算符合，越低分越高
                $price = intval($value['price']);
                // $value['fraction'][] = $price;
                // $value['fraction'][] = $look_budget['high'];
                if(!($look_budget>0) || $price<=$look_budget['high']){
                    $fraction += $weight['price'];
                }else{
                    $fraction += $weight['price']/($price / (bcadd($look_budget['low'],$look_budget['high'])/2));
                }
                // $value['fraction'][] = $fraction;
                //n室m厅：n/m 
                $value['type2'] = $value['room_type'];
                $shiWeiNum = explode("室",$value['type2']);
                if(intval($shiWeiNum[1])==0){
                    $shiWeiNum = 3;
                }else{
                    $shiWeiNum = abs(intval($shiWeiNum[0])/intval($shiWeiNum[1]));
                    if($shiWeiNum>3)
                        $shiWeiNum = 3;
                    else if($shiWeiNum<1)
                        $shiWeiNum = 1;
                }
                $fraction += A('Home/Landlord')->getExpValue(1,$weight['huxing'],3,0.0001,$shiWeiNum);
                // $value['fraction'][] = $fraction;
                $value['floor'] = $value['room_floor'];
                //总楼层：0-6（包括）
                $floosInfo = explode('/',$value['floor']);
                if(intval($floosInfo[1])>6){
                    $fraction += $weight['floor_type'];//有电梯
                }
                // $value['fraction'][] = $fraction;
                if($floosInfo[1]=='底')
                    $fraction += $weight['floor_count'];
                if($floosInfo[1]=='低')
                    $fraction += 0.8*$weight['floor_count'];
                if($floosInfo[1]=='中')
                    $fraction += 0.6*$weight['floor_count'];
                if($floosInfo[1]=='高')
                    $fraction += 0.4*$weight['floor_count'];
                if($floosInfo[1]=='顶')
                    $fraction += 0.2*$weight['floor_count'];
                // $value['fraction'][] = $fraction;
                if($fraction<0)
                    continue;
                $value['brand'] = '我爱我家';
                $value['id'] = 'woaiwojia'.$value['id'];
                $value['title'] = $value['room_title'];
                unset($value['room_title']);
                unset($value['pay_mode']);
                unset($value['normal_price']);
                $value['size'] = $value['room_size'];
                if($value['room_subway']!='')
                    $value['transport'] = $value['room_subway'];
                else $value['transport'] = $value['room_business'] ."附近" .$value['room_location'];
                unset($value['lon']);
                unset($value['lat']);
                unset($value['info']);
                unset($value['location']);
                unset($value['room_size']);
                unset($value['room_type']);
                unset($value['room_floor']);
                unset($value['room_subway']);
                unset($value['room_bussiness']);
                unset($value['room_location']);
                if($value['room_image_link']!='[]'){
                    $images = json_decode($value['room_image_link'],true);
                    $value['image'] = $images[0];
                }else
                    $value['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
                unset($value['room_image_link']);
                // $value['ratio'] = bcdiv($fraction, $weight['distance']+$weight['price']+$weight['huxing']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                if($lookTradition==NULL || $subways_line[0] ==-1 ||array_search(3,$lookTradition)===FALSE){
                    $value['ratio'] = bcdiv($fraction, $weight['distance']+$weight['price']+$weight['huxing']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                }else{
                    $value['ratio'] = bcdiv($fraction, $weight['distance']+$weight['subway_line']+$weight['price']+$weight['huxing']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                }
                $value['labels'] = explode(",",$value['feature']);
                if($value['room_decoration']!='')
                    $value['labels'][] = $value['room_decoration'];
                if($value['room_building_type']!=''&& $value['room_building_type']!='其他')
                    $value['labels'][] = $value['room_building_type'];
                // 去掉合租这个标签
                // if($value['room_rent_type']!=''&& $value['room_rent_type']!='其他')
                //     $value['labels'][] = $value['room_rent_type'];
                unset($value['feature']);
                unset($value['room_decoration']);
                unset($value['room_building_type']);
                unset($value['room_warming']);
                unset($value['room_rent_type']);
                unset($value['room_looking_type']);
                unset($value['room_url']);
                unset($value['room_dir']);
                unset($value['room_business']);
                unset($value['city']);
                $value['room_id'] = 'woaiwojia'.$value['room_id'];
                $res[] = $value;
            }
        }
        //房东上传的房源
        // if ($userinfo['is_place'] == 2){
        //     $sqlCondition = $sqlCondition." AND publish =1 AND subway in (".implode(",",$subways_line).")";
        // }
        if($lookType==NULL || ($lookType==3|| $lookType==2)){
            if($look_end_date!=NULL){
                $sqlCondition = $sqlCondition." AND publish =1 AND start_date <='".$look_end_date."'";
            }else{
                $sqlCondition = $sqlCondition." AND publish =1";
            }
            //排除不符合自己性别和室友性别的房源
            if($userinfo['sex']==1 || $looksex==1){
                $sqlCondition = $sqlCondition." AND sex <> 2";
            }else if($userinfo['sex']==2 || $looksex==2){
                $sqlCondition = $sqlCondition." AND sex <> 1";
            }
            $landlordRooms = M('landlord_room')->field('id,xiaoqu as title,yajinfangshi,price,huxing as type2,size, visit_time,floor_type,floor_count as floor,subway,lon,lat,address as transport ,type,images,rent_type,room_type,rent_temper,city,roomer_count')->where($sqlCondition)->select();
            // xformatOutPutJsonData('test', $landlordRooms, M()->getLastSql());
            foreach($landlordRooms as $lk => $lv){
                // if($userinfo['checkoutime']<$lv['start_date']){
                //     //入住时间不匹配
                //     continue;
                // }
                //租住类型匹配,只有国内房源有
                if($lookRentType==1){
                    //希望整租，排除所有合租的
                    if($lv['rent_type']==0)
                        continue;
                    //海外房源，排除所有合租类型的
                    if($lv['rent_type']==-1){
                        $types = explode(",",$lv['room_type']);
                        $index = array_search("3",$types);
                        if($index!==FALSE){
                            continue;
                        }
                    }
                }else if($lookRentType==2){
                    //希望合租，排除所有整租的
                    if($lv['rent_type']==1)
                        continue;
                }
                
                //租住地点匹配,计算两地的距离
                $distance = $this->GetDistance(floatval($lv['lon']),floatval($lv['lat']),floatval($userinfo['lon']),floatval($userinfo['lat']));
                //排除超过最大公里数的房源
                if($distance>$maxDis){
                    if(array_search($lv['subway'],$subways_line)!=FALSE){
                        $fraction = $weight['subway_line']/ceil($distance);
                    }else continue;
                }else{
                    $fraction = A('Home/Landlord')->getExpValue(0,$weight['distance'],$maxDis,0.1,$distance);
                    if(array_search($lv['subway'],$subways_line)!=FALSE){
                        $fraction += $weight['subway_line']/ceil($distance);
                    }
                }
                // $lv['debug'][] = $distance;
                // $lv['debug'][] = $maxDis;
                // $lv['debug'][] = $fraction;
                // if($lk==0)
                //     xformatOutPutJsonData($look_budget,$fraction,$lv);
                //租住预算匹配，预算符合，越低分越高
                $price = intval($lv['price']);
                if(!($look_budget>0) || $price<=$look_budget['high']){
                    $fraction += $weight['price'];
                }else{
                    $fraction += $weight['price']/($price / (bcadd($look_budget['low'],$look_budget['high'])/2));
                }
                // $lv['debug'][] = $fraction;
                if($lv['rent_type']!=-1){
                    //n室m厅：n/m 只有国内的房源有
                    if(intval(explode("厅",$lv['type2'])[1])==0)
                        $hisShiWei = 3;
                    else $hisShiWei = intval(explode("室",$lv['type2'])[0])/intval(explode("厅",$lv['type2'])[1]);
                    if(intval(explode("室",$lv['type2'])[1])==0)
                        $hisShiTing = 3;
                    else $hisShiTing = intval(explode("室",$lv['type2'])[0])/intval(explode("室",$lv['type2'])[1]);
                    if($hisShiWei>3)
                        $hisShiWei = 3;
                    else if($hisShiWei<1)
                        $hisShiWei = 1;
                    $fraction += A('Home/Landlord')->getExpValue(1,$weight['huxing'],3,0.0001,$hisShiWei);
                    if($hisShiTing>3)
                        $hisShiTing = 3;
                    else if($hisShiTing<1)
                        $hisShiTing = 1;
                    $fraction += A('Home/Landlord')->getExpValue(1,$weight['huxing'],3,0.0001,$hisShiTing);
                }else{
                    // $lv['type2'] = intval($lv['rent_temper'])==1?"冷租":"暖租";
                    $lv['type2'] = "可住".$lv['roomer_count']."人";
                }
                // $lv['debug'][] = $fraction;
                if($lv['floor_type']==1){
                    $fraction += $weight['floor_type'];
                    $lv['labels'][] = '电梯房';
                }else{
                    $lv['labels'][] = '楼梯房';
                }
                // $lv['debug'][] = $fraction;
                // if($lk==0)
                //     xformatOutPutJsonData('test',$fraction,$lv);
                $floor_diff = intval($lv['floor']);
                if($floor_diff>15)
                    $floor_diff = 15;
                else if($floor_diff<1)
                    $floor_diff = 1;
                $fraction += A('Home/Landlord')->getExpValue(1,$weight['floor_count'],15,0.0001,$floor_diff);
                if($fraction<0)
                    continue;
                $lv['room_id'] = 'landlord'.$lv['id'];
                //unset($lv['id']);
                if($lv['type']==1){
                    $lv['brand'] ='房东';
                }else if($lv['type']==2){
                    $lv['brand'] ='转租';
                }else{
                    $lv['brand'] ='中介';
                }
                unset($lv['type']);
                $lv['floor'] = $lv['floor'].'层';
                unset($lv['subway']);
                if($lv['images']!=''){
                    $images = explode(";",$lv['images']);
                    foreach($images as $ik=>$iv){
                        if($iv!=""){
                            $lv['image'] = $iv;
                            break;
                        }
                    }
                }else
                    $lv['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
                unset($lv['images']);
                $lv['labels'][] = $lv['yajinfangshi'];
                unset($lv['yajinfangshi']);
                if($lv['visit_time']==1){
                    $lv['labels'][] = '随时看房';
                }else if($lv['visit_time']==2){
                    $lv['labels'][] = '周末看房';
                }else{
                    $lv['labels'][] = '周中看房';
                }
                unset($lv['visit_time']);
                unset($lv['floor_type']);
                unset($lv['start_date']);
                if($lookTradition==NULL  || $subways_line[0] ==-1||array_search(3,$lookTradition)===FALSE){
                    if($lv['rent_type']!=-1){//国内房源
                        $lv['ratio'] = bcdiv($fraction, $weight['distance']+$weight['price']+$weight['huxing']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                    }else{
                        $lv['ratio'] = bcdiv($fraction, $weight['distance']+$weight['price']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                    }
                }else{
                    if($lv['rent_type']!=-1){//国内房源
                        $lv['ratio'] = bcdiv($fraction, $weight['distance']+$weight['subway_line']+$weight['price']+$weight['huxing']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                    }else{
                        $lv['ratio'] = bcdiv($fraction, $weight['distance']+$weight['subway_line']+$weight['price']+$weight['floor_type']+$weight['floor_count'], 4) * 100;
                    }
                }
                if($lv['ratio']>=100){
                    $lv['ratio'] = 99.99;
                }
                $res[] = $lv;
            }
        }
        //按照匹配分数排序
        $sort = array_column($res, 'ratio');      
        array_multisort($sort, SORT_DESC, $res);
        // usort($res,function($first,$second){
        //     return floatval($first->ratio) < floatval($second->ratio);
        // });
        // xformatOutPutJsonData('test', $res, $sort);
        if(!$count)
            $count = 30;//现在设置初始查看50个房源，需要和小程序一起改动
        else if($count > 30)
            $count = 300;//现在设置最多查看300个房源，需要和小程序一起改动
        $res = array_slice($res,0,intval($count));
        // $res = array_slice($res,0,5);
        if (!empty($res)) {
            xformatOutPutJsonData('success', $res, '');
        } else {
            xformatOutPutJsonData('success', 1, '');
        }
    }

    public function getRoomDetail(){
        $Data = I('get.');
        $uid = $Data['id'];
        if($Data['token']!="visit"){//==visit说明只想看看这个房源
            if (empty($uid)) {
                xformatOutPutJsonData('fail', '', '网络错误1！');
            }
            if($Data['token'] == S('user_' . $uid)){

            }else if($Data['token'] == S('user_landlord' . $uid)){
                $uid = '-'.$uid;
            }else{
                xformatOutPutJsonData('fail', 1, '网络错误2！');
            }
        }
        // if(strpos($Data['roomid'], "ziruyu") === 0){
        //     //从ziruyu数据表中读取
        //     $Data['roomid'] = str_replace("ziruyu",'',$Data['roomid']);
        //     $res = M('ziruyu')->field('*')->where('id='.$Data['roomid'])->find();
        //     if($res){     
        //         $res['id'] = 'ziruyu'. $Data['roomid'];
        //         $roomproperties = explode(",",$res['info']);
        //         $res['price'] = str_replace(',','——',$res['price']);
        //         // $res['size'] = str_replace("约","",str_replace('平米','',$roomproperties[2]));
        //         // $res['type2'] = $roomproperties[0];
        //         // $res['floor'] = $roomproperties[1];
        //         $res['labels0'][] = str_replace("约","",$roomproperties[2]);
        //         $res['labels0'][] = $roomproperties[1];
        //         $res['labels0'][] = $roomproperties[0];
        //         $res['transport'] = $res['location'];
        //         unset($res['info']);
        //         unset($res['location']);
        //         $res['labels1'] = explode(",",$res['equipments1']);
        //         unset($res['equipments1']);
        //         $res['labels2'] = explode(",",$res['equipments2']);
        //         unset($res['equipments2']);
        //         $res['imgs'] = json_decode($res['subpage_image_dir']);
        //         unset($res['subpage_image_dir']);
        //         if(!empty($uid)){
        //             //返回当前是否已经被收藏或者拉黑
        //             $res['shoucang'] = 0;
        //             $res['heimingdan'] = 0;   
        //             $result3 = M('shoucang')->field('likehouse,dislikehouse')->where('uid=' . $uid)->find();
        //             if($result3){
        //                 $houses = json_decode($result3['likehouse'],true);
        //                 $myShoucang = explode(",",$houses['ziruyu']);
        //                 $index = array_search($Data['roomid'],$myShoucang);
        //                 if(!($index===FALSE))
        //                     $res['shoucang'] = 1;
        //                 $houses = json_decode($result3['dislikehouse'],true);
        //                 $myHeimingdan = explode(",",$houses['ziruyu']);
        //                 $index = array_search($Data['roomid'],$myHeimingdan);
        //                 if(!($index===FALSE))
        //                     $res['heimingdan'] = 1;    
        //             }
        //         }
        //         unset($res['vrlink']);
        //         xformatOutPutJsonData('success', $res, '');
        //     } else {
        //         xformatOutPutJsonData('fail', 1, 'error roomid');
        //     }
        // }else 
        if(strpos($Data['roomid'], "danke") === 0){
            //从danke数据表中读取
            $Data['roomid'] = str_replace("danke",'',$Data['roomid']);
            $res = M('danke')->field('*')->where('room_id="'.$Data['roomid'].'"')->find();
            if($res){    
                $result['id'] = "danke".$Data['roomid'];
                $result['roomid'] = $Data['roomid'];
                $result['title'] = $res['room_title'];
                $result['brand'] ='蛋壳公寓';
                $result['labels0'][] = $res['room_size'].'平米';
                $result['labels0'][] = $res['room_floor'].'层';                
                $result['labels0'][] = explode(",",$res['room_type'])[1].'租';
                $result['labels0'][] = explode(",",$res['room_type'])[0];
                $result['transport'] = $res['room_subway'];
                $result['labels'] = explode(",",$res['feature']);
                // if($res['rent_whole']==1)
                //     $result['labels'][] = '整租';
                // else
                //     $result['labels'][] = '合租';
                $roommates=json_decode(strtr(strtr($res['roommate'],": ",':'),"'",'"'));
                $hasEmpty = 0;
                foreach($roommates as $k=>$v){
                    // xformatOutPutJsonData('test', $v,$v->RoomState);
                    if($v->RoomState=="当前房间"||$v->RoomState=="可出租"){
                        $hasEmpty = $hasEmpty+1;
                    }
                }
                if($hasEmpty>1)
                    $result['labels'][] = '多间空房';
                if($res['promotion_price']!=0){
                    $result['price'] = $res['promotion_price'];
                    $result['normal_price'] = $res['normal_price'];
                    $result['labels'][] = '限时特惠';
                }else{
                    $result['price'] = $res['normal_price'];
                }
                $result['lon'] = $res['lon'];
                $result['lat'] = $res['lat'];
                $result['roommates'] = json_decode($res['roommate']);
                $result['imgs'] = json_decode($res['room_image_link']);
                if($Data['token']!="visit"){
                    //返回当前是否已经被收藏或者拉黑
                    $result['shoucang'] = 0;
                    $result['heimingdan'] = 0;   
                    $result3 = M('shoucang')->field('likehouse,dislikehouse')->where('uid=' . $uid)->find();
                    if($result3){
                        $houses = json_decode($result3['likehouse'],true);
                        $myShoucang = explode(",",$houses['danke']);
                        $index = array_search($Data['roomid'],$myShoucang);
                        if(!($index===FALSE))
                            $result['shoucang'] = 1;
                        $houses = json_decode($result3['dislikehouse'],true);
                        $myHeimingdan = explode(",",$houses['danke']);
                        $index = array_search($Data['roomid'],$myHeimingdan);
                        if(!($index===FALSE))
                            $result['heimingdan'] = 1;    
                    }
                }
                xformatOutPutJsonData('success', $result, '');
            } else {
                xformatOutPutJsonData('fail', 1, 'error roomid');
            }
        }else if(strpos($Data['roomid'], "woaiwojia") === 0){
            //从woaiwojia数据表中读取
            $Data['roomid'] = str_replace("woaiwojia",'',$Data['roomid']);
            $res = M('woaiwojia')->field('*')->where('room_id='.$Data['roomid'])->find();
            if($res){    
                $result['id'] = "woaiwojia".$Data['roomid'];
                $result['roomid'] = $Data['roomid'];
                $result['title'] = $res['room_title'];
                $result['brand'] ='我爱我家';
                $result['labels0'][] = $res['room_size'].'平米';
                $result['labels0'][] = $res['room_floor'].'层';
                $result['labels0'][] = $res['room_decoration'];
                $result['labels0'][] = $res['room_type'];
                if($res['room_rent_type']!=''&& $res['room_rent_type']!='其他')
                    $result['labels'][] = $res['room_rent_type'];
                if($res['room_subway']!='')
                    $result['transport'] = $res['room_subway'];
                else $result['transport'] = $res['room_business'] ."附近" .$res['room_location'];
                $result['labels'] = explode(",",$res['feature']);
                $result['labels'][] = $res['pay_mode'];
                if($res['room_building_type']!='其他')
                    $result['labels'][] = $res['room_building_type'];
                $result['price'] = $res['normal_price'];
                $result['lon'] = $res['lon'];
                $result['lat'] = $res['lat'];
                if($res['room_image_link']!='[]'){
                    $result['imgs'] = json_decode($res['room_image_link']);
                }else
                    $result['imgs'][] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
                if($Data['token']!="visit"){
                    //返回当前是否已经被收藏或者拉黑
                    $result['shoucang'] = 0;
                    $result['heimingdan'] = 0;   
                    $result3 = M('shoucang')->field('likehouse,dislikehouse')->where('uid=' . $uid)->find();
                    if($result3){
                        $houses = json_decode($result3['likehouse'],true);
                        $myShoucang = explode(",",$houses['woaiwojia']);
                        $index = array_search($Data['roomid'],$myShoucang);
                        if(!($index===FALSE))
                            $result['shoucang'] = 1;
                        $houses = json_decode($result3['dislikehouse'],true);
                        $myHeimingdan = explode(",",$houses['woaiwojia']);
                        $index = array_search($Data['roomid'],$myHeimingdan);
                        if(!($index===FALSE))
                            $result['heimingdan'] = 1;    
                    }
                }
                xformatOutPutJsonData('success', $result, '');
            } else {
                xformatOutPutJsonData('fail', 1, 'error roomid');
            }
        }else if(strpos($Data['roomid'], "landlord") === 0){
            //从房东上传的数据表中读取
            $Data['roomid'] = str_replace("landlord",'',$Data['roomid']);
            $room = M('landlord_room')->field('*')->where('id='.$Data['roomid'])->find();
            if($room){
                // $master = M('landlord')->field('country')->where('id='.$room['master_id'])->find();
                $country = M('city')->field('country')->where('cid='.$room['city'])->find();
                $room['country'] = $country['country'];
                if($room['rent_type']!=-1){//国内房源
                    // $subway = M('subways')->field('line,station')->where('id=' . $room['subway'])->find();
                    // $room['transport'] = '近'.$subway['line'].$subway['station'];
                    if($room['subway']>0){
                        $room['labels'][] = '近地铁';
                    }
                    $room['labels'][] = $room['yajinfangshi'];
                    if($room['visit_time']==1){
                        $room['labels'][] = '随时看房';
                    }else if($room['visit_time']==2){
                        $room['labels'][] = '周末看房';
                    }else if($room['visit_time']==3){
                        $room['labels'][] = '周内看房';
                    }
                    $room['labels'][] = "可住".$room['roomer_count']."人";
                    $room['labels0'][] = $room['size'].'平米';
                    $room['labels0'][] = $room['huxing'];
                    $room['labels0'][] = $room['floor_count'];
                    if($room['floor_type']==1)
                        $room['labels0'][] = '电梯房';
                    else 
                        $room['labels0'][] = '楼梯房';
                }else{
                    $room['labels0'][] = $room['size'].'平米';
                    $room['labels0'][] = "可住".$room['roomer_count']."人";
                    $room['labels0'][] = $room['floor_count'];
                    if($room['register']==1){
                        $room['labels0'][] = "可注册(anmelden)";
                    }else{
                        $room['labels0'][] = "不可注册(anmelden)";
                    }
                    if($room['sex']==1){
                        $room['labels'][] = "招男租客";
                    }else if($room['sex']==2){
                        $room['labels'][] = "招女租客";
                    }else if($room['sex']==3){
                        $room['labels'][] = "不限性别";
                    }
                    
                    if($room['floor_type']==1)
                        $room['labels'][] = '电梯房';
                    else 
                        $room['labels'][] = '楼梯房';
                    
                    $types = explode(",",$room['room_type']);
                    if(!(array_search(1,$types)===FALSE))
                        $room['labels'][] = '住宅';
                    if(!(array_search(2,$types)===FALSE))
                        $room['labels'][] = '单身公寓';
                    if(!(array_search(3,$types)===FALSE))
                        $room['labels'][] = '合租';
                        
                    if($room['rent_temper']==1){
                        $room['labels'][] = "冷租";
                    }else if($room['rent_temper']==2){
                        $room['labels'][] = "暖租";
                    }
                }
                unset($room['roomer_count']);
                $room['id'] = "landlord".$room['id'];
                $room['transport'] = $room['address'];
                if($room['type']==1){
                    $room['brand'] = '房东直租房源';
                }else if($room['type']==2){
                    $room['brand'] = '个人转租房源';
                }else{
                    $room['brand'] = $room['zhongjie'];
                }
                if($room['publish']==0){
                    $room['title'] = $room['xiaoqu'].'[已下架]';
                }else{
                    $room['title'] = $room['xiaoqu'];
                }
                $room['labels'][] = $room['start_date'].'可住';
                $room['price'] = $room['price'];
                $room['lon'] = $room['lon'];
                $room['lat'] = $room['lat'];
                $room['description'] = $room['description'];
                $images = explode(";",$room['images']);
                foreach($images as $k=>$v){
                    if($v!=''){
                        if($k<3){
                            $room['imgs']['卧室'.($k+1)] = $v;
                        }else if($k==3){
                            $room['imgs']['客厅'] = $v;
                        }else if($k==4){
                            $room['imgs']['厨房'] = $v;
                        }else if($k==5){
                            $room['imgs']['卫生间'] = $v;
                        }else{
                            $room['imgs']['其他'.($k-5)] = $v;
                        }
                    }
                };
                if($Data['token']!="visit"){
                    //返回当前是否已经被收藏或者拉黑
                    $room['shoucang'] = 0;
                    $room['heimingdan'] = 0;   
                    $result3 = M('shoucang')->field('likehouse,dislikehouse')->where('uid=' . $uid)->find();
                    // xformatOutPutJsonData('test', $result3, '');
                    if($result3){
                        $houses = json_decode($result3['likehouse'],true);
                        $myShoucang = explode(",",$houses['landlord']);
                        $index = array_search($Data['roomid'],$myShoucang);
                        if(!($index===FALSE))
                            $room['shoucang'] = 1;
                        $houses = json_decode($result3['dislikehouse'],true);
                        $myHeimingdan = explode(",",$houses['landlord']);
                        $index = array_search($Data['roomid'],$myHeimingdan);
                        if(!($index===FALSE))
                            $room['heimingdan'] = 1;    
                    }
                }
                xformatOutPutJsonData('success', $room, '');
            } else {
                xformatOutPutJsonData('fail', 1, 'error roomid');
            }
        }else{
            $room = M('room')->field('*')->where('id='.$Data['roomid'])->find();
            if($room){
                $res['id'] = $room['id'];
                $res['brand'] = '自有房源';
                // $user = M('user')->field('name')->where('id=' . $room['master_id'])->find();
                // $res['title'] = base64_decode($user['name']);
                $res['title'] = $room['xiaoqu'];
                $res['labels0'][] = '合租';
                if($room['floor_type']==1)
                    $res['labels0'][] = '电梯房';
                else 
                    $res['labels0'][] = '楼梯房';
                $res['labels0'][] = $room['floor_count'];
                $res['labels0'][] = $room['huxing'];

                $subway = M('subways')->field('line,station')->where('id=' . $room['subway'])->find();
                $res['transport'] = '近'.$subway['line'].$subway['station'];
                if($room['area']!=''){
                    $area = M('circles')->field('area_name,name')->where('id=' . $room['area'])->find();
                    $res['transport'] = $res['transport'].',位于'.$area['area_name'].$area['name'];
                }
                $res['labels'][] = $room['yajinfangshi'];
                if($room['visit_time']==1){
                    $res['labels'][] = '随时看房';
                }else if($room['visit_time']==2){
                    $res['labels'][] = '周末看房';
                }else if($room['visit_time']==3){
                    $res['labels'][] = '周内看房';
                }
                $res['labels'][] = '最早'.$room['start_date'].'可住';
                $res['price'] = $room['yuezujin'];
                $location = json_decode($room['location'],true);
                $res['lon'] = $location['longitude'];
                $res['lat'] = $location['latitude'];
                $res['description'] = $room['description'];
                $images = explode(";",$room['images']);
                foreach($images as $k=>$v){
                    if($v!=''){
                        if($k<3){
                            $res['imgs']['卧室'.($k+1)] = $v;
                        }else if($k==3){
                            $res['imgs']['客厅'] = $v;
                        }else if($k==4){
                            $res['imgs']['厨房'] = $v;
                        }else if($k==5){
                            $res['imgs']['卫生间'] = $v;
                        }else{
                            $res['imgs']['其他'.($k-5)] = $v;
                        }
                    }
                };
                xformatOutPutJsonData('success', $res, '');
            } else {
                xformatOutPutJsonData('fail', 1, 'error roomid');
            }
        }
    }

    // //加一个删除房源图片的函数

    // //上传自有房源的图片，根据微信的uploadFile接收chooseImage的tempFilePaths.
    // public function uploadMyRoomPic() {
    //     $Data = I('post.');
    //     $uid = $Data['id'];
    //     if (empty($uid)) {
    //         xformatOutPutJsonData('fail', '', '网络错误1！');
    //     }
    //     if ($Data['token'] !== S('user_' . $Data['id'])) {
    //         xformatOutPutJsonData('fail', '', '网络错误2！');
    //     }
    //     $file = $_FILES;
    //     //检查图片是否合法
    //     $imgCheck = A('Home/Chat')->mediaCheck($file);
    //     $imgCheck = json_decode(stripslashes($imgCheck));
    //     $imgCheck = json_decode(json_encode($imgCheck), true);
    //     if($imgCheck['errcode']==0){
    //         $config = array(
    //             'rootPath' => "./Public/RoomImage/".$Data['openid'],
    //             //'rootPath' => "./Public/MiniCode/",
    //             'exts' => array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
    //             // 'subName' => array('date', 'Ymd'),
    //             'autoSub' => true,
    //             'saveName' => $Data['type'].'_'.$Data['preno'],
    //             'replace' => true,
    //         );
    //         $upload = new \Think\Upload($config);
    //         $info = $upload->upload($file);
    //         if(!$info) {// 上传错误提示错误信息
    //             xformatOutPutJsonData('fail', 1, $upload->getError());
    //         }else{// 上传成功 获取上传文件信息
    //             unlink('./Public/Avatar/'.$Data['type'].'_'.((int)($Data['preno'])-1).'.jpg');
    //             unlink('./Public/Avatar/'.$Data['type'].'_'.((int)($Data['preno'])-1).'.gif');
    //             unlink('./Public/Avatar/'.$Data['type'].'_'.((int)($Data['preno'])-1).'.png');
    //             unlink('./Public/Avatar/'.$Data['type'].'_'.((int)($Data['preno'])-1).'.jpeg');
    //             unlink('./Public/Avatar/'.$Data['type'].'_'.((int)($Data['preno'])-1).'.bmp');
    //             $fileurl =str_replace('http','https',IMG_PATH). '/Public/RoomImage/' .$Data['openid']. $info['file']['savepath'] . $info['file']['savename'];
    //             xformatOutPutJsonData('success', $fileurl, "");
    //         }
    //     }else if($imgCheck['errcode']==87014){
    //         xformatOutPutJsonData('fail', 1, "有违法违规内容");
    //     }else{
    //         xformatOutPutJsonData('fail', 1, "调用出现错误".$msgCheck['errcode']);
    //     }
    // }

    //获取用户的城市
    public function getCityAndRoom(){
        $Data = I('get.');
        $uid = $Data['id'];
//        $uid = 782;
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $uid)) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $user = M('user')->field('city_no')->where('id=' . $uid)->find();
        $myPosition = M('city')->field('c_name as city')->where('cid='. $user['city_no'])->find();
        $room = M('room')->field('*')->where('master_id=' . $uid)->find();
        xformatOutPutJsonData('success', $room, $myPosition['city']);
    }

    public function getCondition(){
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $uid)) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $wx_member = M('user')->field('city_no,address,lon,lat,ditie,checkintime,checkoutime, tenant_long,budget')->where('id='.$uid)->find();
        $result['cityid'] = $wx_member['city_no'];
        $result['pos'] = $wx_member['address'];
        $result['lon'] = $wx_member['lon'];
        $result['lat'] = $wx_member['lat'];
        $result['subway'] = $wx_member['ditie'];
        $myPosition = M('city')->field('country, c_name as city')->where('cid='. $wx_member['city_no'])->find();
        $city = $myPosition['city'];
        $result['city'] = $myPosition['city'];
        // if ($wx_member['is_place'] == 1) {
        //     $myPosition = M('circles')->field('city_name as city,area_name,name')->where('id='. $wx_member['shangquan'])->find();
        //     $city = $myPosition['city'];
        //     $result['pos'] = $myPosition['area_name'].$myPosition['name'];
        //     $result['subway'] = 0;
        // }else if ($wx_member['is_place'] == 2) {
        //     $myPosition = M('subways')->field('city,line,station')->where('id='. $wx_member['ditie'])->find();
        //     $city = $myPosition['city'];
        //     $result['pos'] = $myPosition['line'].$myPosition['station'];
        //     $result['subway'] = $wx_member['ditie'];
        // }
        $result['earlydate'] = $wx_member['checkintime'];
        $result['lastdate'] = $wx_member['checkoutime'];
        $result['duration'] = $wx_member['tenant_long'];
        $result['budget'] = $wx_member['budget'];
        $match = M('user_primary_matching')->field('sex,tradition,rent_type,type')->where('uid='.$uid)->find();
        if($match){
            $result['gender'] = $match['sex'];
            $result['tradition'] = $match['tradition'];
            $result['rent_type'] = $match['rent_type'];
            $result['type'] = $match['type'];
        }
        //地铁
        $subway['line'] = M('subways')->field('DISTINCT line as name')->where('city="' . $city .'"')->select();
        foreach ($subway['line'] as $k => $v) {
            $subway['line'][$k]['id'] = $k + 1;
            $subway['station'][] = M('subways')->field('id as infoid,station as name')->where('city="' . $city .'" AND line="' . $v['name'].'"')->select();
        } 
        foreach ($subway['station'] as $key => $value) {
            foreach ($value as $a => $b) {
                $subway['station'][$key][$a]['id'] = $a + 1;
            }
        }
        $result['subways'] = $subway;
        //租住时长
        $result['durations'] =M('tenant_long')->field('id,name')->select();
        //租住预算
        $country = M('user')->alias('a')
                ->field('a.*,b.country as country')
                ->join('LEFT JOIN xqwl_city b ON a.city_no =b.cid')
                ->where("a.id = ".$uid)
                ->find();
        $result['budgets'] = M('budget')->field('id,b_name as name')->where("country='".$country['country']."'")->select();
        
        xformatOutPutJsonData('success', $result, "");
    }

    public function editMyRoom(){
        $Data = I('post.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //检查文字是否合法
        $msgCheck = A('Home/Chat')->messageCheck($Data['floor_count']);
        // xformatOutPutJsonData('test', $msgCheck, $msgCheck['errcode']);
        if($Data['floor_count']==''|| $msgCheck['errcode']!=87014){
            $msgCheck = A('Home/Chat')->messageCheck($Data['description']);
            if($Data['description']==''|| $msgCheck['errcode']!=87014){
                $msgCheck = A('Home/Chat')->messageCheck($Data['xiaoqu']);
                if($msgCheck['errcode']!=87014){
                    $msgCheck = A('Home/Chat')->messageCheck($Data['yuezujin']);
                    if($msgCheck['errcode']!=87014){
                        //是不是应该检查这个房主是否有其他的房子，并删除这些房源资源，或者直接更新
                        $myRoom = M('room')->field('images')->where('master_id=' . $uid)->find();
                        if($myRoom){
                            $oldImages= explode(";",$myRoom['images']);
                            $newImages= explode(";",$Data['images']);
                            $index = 0;
                            foreach ($oldImages as $k => $v) {
                                if($v != ''&& $v!=$newImages[$index]){
                                    $startIndex = strpos($v,"/Public");
                                    //删除旧照片
                                    unlink('.' . substr($v,$startIndex));
                                }
                                $index = $index + 1;
                            }
                            $update = array(
                                'xiaoqu' => $Data['xiaoqu'],
                                'yajinfangshi' => $Data['yajinfangshi'],
                                'yuezujin' => $Data['yuezujin'],
                                'huxing' => $Data['huxing'],
                                'visit_time' => $Data['visit_time'],
                                'start_date' => $Data['start_date'],
                                'floor_type' => $Data['floor_type'],
                                'floor_count' => $Data['floor_count'],
                                'description' => $Data['description'],
                                'city' => $Data['city'],
                                'area' => $Data['area'],
                                'subway' => $Data['subway'],
                                'location' => str_replace("&quot;",'"',$Data['location']),
                                'images' => $Data['images'],
                                'update_time' => date("Y-m-d H:i:s",time()),
                            );
                            $res = M('room')->where('master_id='.$uid)->save($update); 
                            xformatOutPutJsonData('success', $res, 'update');
                        }else{
                            $inserData = array(
                                'xiaoqu' => $Data['xiaoqu'],
                                'yajinfangshi' => $Data['yajinfangshi'],
                                'yuezujin' => $Data['yuezujin'],
                                'huxing' => $Data['huxing'],
                                'visit_time' => $Data['visit_time'],
                                'start_date' => $Data['start_date'],
                                'floor_type' => $Data['floor_type'],
                                'floor_count' => $Data['floor_count'],
                                'description' => $Data['description'],
                                'city' => $Data['city'],
                                'area' => $Data['area'],
                                'subway' => $Data['subway'],
                                'location' => str_replace("&quot;",'"',$Data['location']),
                                'images' => $Data['images'],
                                'create_time' => date("Y-m-d H:i:s",time()),
                                'update_time' => date("Y-m-d H:i:s",time()),
                                'master_id' => $uid,
                            );
                            $roomid = M('room')->add($inserData);
                            xformatOutPutJsonData('success', $roomid, 'insert');
                        }
                        xformatOutPutJsonData('fail', $Data, "未知错误");
                    }else{
                        xformatOutPutJsonData('fail', $Data['yuezujin'], "月租金内容有违法违规内容");
                    }
                }else{
                    xformatOutPutJsonData('fail', $Data['xiaoqu'], "小区名称内容有违法违规内容");
                }
            }else{
                xformatOutPutJsonData('fail', $Data['description'], "房屋概况内容有违法违规内容");
            }
        }else{
            xformatOutPutJsonData('fail', $Data['floor_count'], "所在楼层内容有违法违规内容");
        }
    }
    
}
