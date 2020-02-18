<?php

namespace Home\Controller;

use Common\Controller\HomeBaseController;

/**
 * 房东Controller
 */
class LandlordController extends HomeBaseController {

    public function getLandlordOpenid(){
        $data = I('post.');
        if($data['openid']){
            $map['openid'] = array('eq', $data['openid']);
            $wx_member = M('landlord')->field('id,openid,name,phone,avatar,is_match,phone')->where($map)->find();
            if($wx_member){
                //如果之前没存好图片则重新存一下
                if(!$wx_member['avatar']||$wx_member['avatar']==''){
                    $file = file_get_contents($data['avatar']);
                    $filename = './Public/LandlordAvatar/0_' .$openid. '.jpg';
                    $im = file_put_contents($filename, $file);
                    if($im){
                        $update['avatar'] = str_replace('http','https',IMG_PATH). '/Public/LandlordAvatar/0_'.$openid. '.jpg';
                        M('landlord')->where('id='.$wx_member['id'])->save($update);
                    }
                }
                $wx_member['userToken'] = S('user_landlord' . $wx_member['id']);
                // S("landlord".$openid, $wx_member);
                xformatOutPutJsonData('success', $wx_member, '');
            }else{
                xformatOutPutJsonData('fail', 1, '注册失败，请联系客服');
            }
        }
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wx8f2ed65c8aee3563&secret=4f2e7944beac651f1b5d49dd53e36328&js_code=' . $data['code'] . '&grant_type=authorization_code';
        $result = file_get_contents($url);
        $result_arr = json_decode($result, TRUE);
        $openid = $result_arr['openid'];
        // S($openid . '_landlord', $result_arr['session_key']);
        if ($openid != '') {
            $map = array();
            $map['openid'] = array('eq', $openid);
//            $map['is_login'] = array('eq', 1);
            $wx_member = M('landlord')->field('id,openid,name,avatar,phone,is_match,phone')->where($map)->find();
            if ($wx_member == false) {
                $file = file_get_contents($data['avatar']);
                $filename = './Public/LandlordAvatar/0_' .$openid. '.jpg';
                $im = file_put_contents($filename, $file);
                if($im)
                    $data['avatar'] = str_replace('http','https',IMG_PATH). '/Public/LandlordAvatar/0_'.$openid. '.jpg';
                $inserData = array(
                    'openid' => $openid,
                    //'nickname' => $data['nickname'],
                    'name' => $data['name'],
                    'avatar' => $data['avatar'],
                    'gender' => $data['gender'],
                    'province' => $data['province'],
                    'city' => $data['city'],
//                    'is_login' => 0,
                );
                $userid = M('landlord')->add($inserData);
                
                $inserData['id'] = $userid;
                $userToken = A('Home/Index')->getTokenCode($userid,"landlord");
                $inserData['userToken'] = $userToken;
                $update['userToken'] = $userToken;
                M('landlord')->where('id='.$userid)->save($update);
                xformatOutPutJsonData('success', $inserData, '');
                // S("landlord".$openid, $inserData);
            } else {
                //如果之前没存好图片则重新存一下
                if(!$wx_member['avatar']||$wx_member['avatar']==''){
                    $file = file_get_contents($data['avatar']);
                    $filename = './Public/LandlordAvatar/0_' .$openid. '.jpg';
                    $im = file_put_contents($filename, $file);
                    if($im){
                        $update['avatar'] = str_replace('http','https',IMG_PATH). '/Public/LandlordAvatar/0_'.$openid. '.jpg';
                        M('landlord')->where('id='.$wx_member['id'])->save($update);
                    }
                }
                $wx_member['userToken'] = S('user_landlord' . $wx_member['id']);
                // S("landlord".$openid, $wx_member);
                xformatOutPutJsonData('success', $wx_member, 'test20190330');
            }
        }
        xformatOutPutJsonData('fail', '', '网络错误123！');
    }
    
    /*
     * 设置房东基本信息
     */
    public function setLandlordInfo(){
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $map['name'] = $Data['name'];
        $map['work'] = $Data['work'];
        $map['birth'] = $Data['birth'];
        $map['IDcard'] = $Data['idCard'];
        $map['phone'] = $Data['phone'];
        $map['is_match'] = $Data['is_match'];
        $res = M('landlord')->where('id=' . $uid)->save($map);
        if ($res !== FALSE) {
            xformatOutPutJsonData('success', $res, '');
        }
        else{
            xformatOutPutJsonData('fail', $res, '');
        }
    }
        
    /*
     * 获取房东基本信息
     */
    public function getLandlordInfo(){
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //获取职业
        $work = M('work')->field('id,w_name')->select();
        //获取房东信息
        $landlord = M('landlord')->field('name,birth,phone,work,IDcard')->where('id=' . $uid)->find();
        xformatOutPutJsonData('success', $work, $landlord);
    }
    //获取房东的房屋信息
    public function getRoomInfo(){
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $uid)) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $room = M('landlord_room')->field('*')->where('id=' . $Data['roomid'])->find();
        if($room){
            //地铁
            $subway['line'] = M('subways')->field('DISTINCT line as name')->where('city="' . $room['city'] .'"')->select();
            foreach ($subway['line'] as $k => $v) {
                $subway['line'][$k]['id'] = $k + 1;
                $subway['station'][] = M('subways')->field('id as infoid,station as name')->where('city="' . $room['city'] .'" AND line="' . $v['name'].'"')->select();
            } 
            foreach ($subway['station'] as $key => $value) {
                foreach ($value as $a => $b) {
                    $subway['station'][$key][$a]['id'] = $a + 1;
                }
            }
        }
        xformatOutPutJsonData('success', $room, $subway);
    }
    //更改房源的上下架状态
    public function updateRoomStatus(){
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $uid)) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $update = array(
            'publish' => $Data['status']
        );
        // xformatOutPutJsonData('test', $update, $roomid);
        $res = M('landlord_room')->where('id='.$Data['roomid'])->save($update); 
        if($res){
            xformatOutPutJsonData('success', $res, '');
        }else{
            xformatOutPutJsonData('fail', "", '更改失败');
        }
    }
    //根据城市名获取地铁列表
    public function getSubwayByCity(){
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $uid)) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $subway['line'] = M('subways')->field('DISTINCT line as name')->where('city="' . $Data['city'] .'"')->select();
        foreach ($subway['line'] as $k => $v) {
            $subway['line'][$k]['id'] = $k + 1;
            $subway['station'][] = M('subways')->field('id as infoid,station as name')->where('city="' . $Data['city'] .'" AND line="' . $v['name'].'"')->select();
        } 
        foreach ($subway['station'] as $key => $value) {
            foreach ($value as $a => $b) {
                $subway['station'][$key][$a]['id'] = $a + 1;
            }
        }
        xformatOutPutJsonData('success', $subway, '');
    }
    //上传房源信息的图片，根据微信的uploadFile接收chooseImage的tempFilePaths
    public function uploadRoomImg() {
        $Data = I('post.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $Data['id'])) {
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
            if($Data['roomid']==0){
                $config = array(
                    'rootPath' => "./Public/landlordRoomImg/landlord_TMP/",
                    'exts' => array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
                    'autoSub' => true,
                    'saveName' => $uid.'-'.$Data['index'].'-'.time(),
                    'replace' => true,
                );
                $upload = new \Think\Upload($config);
                $info = $upload->upload($file);
                if(!$info) {// 上传错误提示错误信息
                    xformatOutPutJsonData('fail', -1, $upload->getError());
                }else{// 上传成功 获取上传文件信息
                    $fileurl =str_replace('http','https',IMG_PATH). '/Public/landlordRoomImg/landlord_TMP/'. $info['file']['savepath'] . $info['file']['savename'];
                    xformatOutPutJsonData('success', $Data['index'], $fileurl);
                }
            }else{
                $config = array(
                    'rootPath' => "./Public/landlordRoomImg/landlord_TMP/",
                    'exts' => array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
                    'autoSub' => true,
                    'saveName' => $uid.'-'.$Data['roomid'].'-'.$Data['index'].'-'.time(),
                    'replace' => true,
                );
                $upload = new \Think\Upload($config);
                $info = $upload->upload($file);
                if(!$info) {// 上传错误提示错误信息
                    xformatOutPutJsonData('fail', -1, $upload->getError());
                }else{// 上传成功 获取上传文件信息
                    $fileurl =str_replace('http','https',IMG_PATH). '/Public/landlordRoomImg/landlord_TMP/'. $info['file']['savepath'] . $info['file']['savename'];
                    xformatOutPutJsonData('success', $Data['index'], $fileurl);
                }
            }
        }
    }
    /*
     * 编辑我的房源信息,如果带有roomid信息说明是更改
     */
    public function editMyRoom(){
        $Data = I('post.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $Data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //检查文字是否合法
        $msgCheck = A('Home/Chat')->messageCheck($Data['description']);
        if($Data['description']==''|| $msgCheck['errcode']!=87014){
            $msgCheck = A('Home/Chat')->messageCheck($Data['xiaoqu']);
            if($msgCheck['errcode']!=87014){
                $msgCheck = A('Home/Chat')->messageCheck($Data['zhongjie']);
                if($Data['zhongjie']==''|| $msgCheck['errcode']!=87014){
                    if($Data['roomid']==0){
                        //插入新的房源
                        $inserData = array(
                            'xiaoqu' => $Data['xiaoqu'],
                            'size' => $Data['size'],
                            'yajinfangshi' => $Data['yajinfangshi'],
                            'price' => $Data['yuezujin'],
                            'huxing' => $Data['huxing'],
                            'visit_time' => $Data['visit_time'],
                            'start_date' => $Data['start_date'],
                            'floor_type' => $Data['floor_type'],
                            'floor_count' => $Data['floor_count'],
                            'description' => $Data['description'],
                            'city' => $Data['city'],
                            'subway' => $Data['subway'],
                            'type' => $Data['type'],
                            'zhongjie' => $Data['zhongjie'],
                            'lon' => $Data['lon'],
                            'lat' => $Data['lat'],
                            'address' => $Data['address'],
                            'create_time' => date("Y-m-d H:i:s",time()),
                            'update_time' => date("Y-m-d H:i:s",time()),
                            'master_id' => $uid,
                        );
                        $roomid = M('landlord_room')->add($inserData);
                        $update = array(
                            'is_match' => 2
                        );
                        $res = M('landlord')->where('id='.$uid)->save($update); 
                        //然后将房源图片移动过来
                        $oldImages= explode(";",$Data['images']);
                        foreach ($oldImages as $k => $v) {
                            if($v != ''){
                                $startIndex = strpos($v,"/Public/landlordRoomImg/".$uid.'/');
                                if($startIndex===FALSE){
                                    //文件路径不规范，把照片移动过来
                                    $index = explode("-",basename($v))[1];
                                    $ext = explode(".",basename($v))[1];
                                    // xformatOutPutJsonData(basename($v), $index, $ext);
                                    // $olddir = str_replace('http://','data/wwwroot/',IMG_PATH). substr($v,strpos($v,"/Public"));
                                    // $newdir =str_replace('http://','data/wwwroot/',IMG_PATH).'/Public/landlordRoomImg/'.$uid.'/'.$roomid.'/'.$index.'.'.$ext;
                                    $olddir =$_SERVER['DOCUMENT_ROOT']. substr($v,strpos($v,"/Public"));
                                    $path = $_SERVER['DOCUMENT_ROOT'].'/Public/landlordRoomImg/'.$uid.'/'.$roomid;
                                    $newdir =$path.'/'.$index.'-.'.$ext;
                                    // xformatOutPutJsonData('test', $v, $olddir.'改成'.$newdir);
                                    if (is_dir($path)){  
                                        // echo "对不起！目录 " . $path . " 已经存在！";
                                    }else{
                                        //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
                                        $res=mkdir(iconv("UTF-8", "GBK", $path),0777,true); 
                                        if ($res){
                                            // echo "目录 $path 创建成功";
                                        }else{
                                            xformatOutPutJsonData('fail', "", "目录".$path."创建失败");
                                        }
                                    }
                                    if(!rename($olddir,$newdir)){
                                        // xformatOutPutJsonData('fail', "旧名字".$olddir, '新名字'.$newdir);
                                        xformatOutPutJsonData('fail', "旧名字", '新名字');
                                    }else{
                                        $oldImages[$k] = str_replace('http','https',IMG_PATH). '/Public/landlordRoomImg/'.$uid.'/'.$roomid.'/'.$index.'-.'.$ext;
                                    }
                                    // $startIndex = strpos($v,"/Public");
                                    // // 删除旧照片
                                    // unlink('.' . substr($v,$startIndex));
                                }
                            }
                        }
                        $update = array(
                            'images' => implode(";",$oldImages)
                        );
                        // xformatOutPutJsonData('test', $update, $roomid);
                        $res = M('landlord_room')->where('id='.$roomid)->save($update); 
                        if($res){
                            //删除这个房子原来的图片资源
                            foreach ($Data['images'] as $k => $v) {
                                if($v != ''){
                                    $startIndex = strpos($v,"/Public");
                                    //删除旧照片
                                    unlink('.' . substr($v,$startIndex));
                                }
                            }
                        }
                        xformatOutPutJsonData('success', $res, '房屋id'.$roomid);
                    }else{
                        //将房源图片移动过来
                        $oldImages= explode(";",$Data['images']);
                        $index = 0;
                        foreach ($oldImages as $k => $v) {
                            if($v != ''){
                                $startIndex = strpos($v,"/Public/landlordRoomImg/".$uid.'/');
                                if($startIndex===FALSE){
                                    //文件路径不规范，把照片移动过来
                                    // $index = explode("-",basename($v))[2];
                                    $ext = explode(".",basename($v))[1];
                                    // xformatOutPutJsonData(basename($v), $index, $ext);
                                    // $olddir = str_replace('http://','data/wwwroot/',IMG_PATH). substr($v,strpos($v,"/Public"));
                                    // $newdir =str_replace('http://','data/wwwroot/',IMG_PATH).'/Public/landlordRoomImg/'.$uid.'/'.$roomid.'/'.$index.'.'.$ext;
                                    $olddir =$_SERVER['DOCUMENT_ROOT']. substr($v,strpos($v,"/Public"));
                                    $path = $_SERVER['DOCUMENT_ROOT'].'/Public/landlordRoomImg/'.$uid.'/'.$Data['roomid'];
                                    $time_ext = time();
                                    $newdir =$path.'/'.$index.'-'.$time_ext.".".$ext;
                                    // xformatOutPutJsonData('test', $v, $olddir.'改成'.$newdir);
                                    if (is_dir($path)){  
                                        // echo "对不起！目录 " . $path . " 已经存在！";
                                    }else{
                                        //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
                                        $res=mkdir(iconv("UTF-8", "GBK", $path),0777,true); 
                                        if ($res){
                                            // echo "目录 $path 创建成功";
                                        }else{
                                            xformatOutPutJsonData('fail', "", "目录".$path."创建失败");
                                        }
                                    }
                                    if(!rename($olddir,$newdir)){
                                        xformatOutPutJsonData('fail'.$index, "旧名字".$olddir, '新名字'.$newdir);
                                        // xformatOutPutJsonData('fail', "旧名字", '新名字');
                                    }else{
                                        $oldImages[$k] = str_replace('http','https',IMG_PATH). '/Public/landlordRoomImg/'.$uid.'/'.$Data['roomid'].'/'.$index.'-'.$time_ext.".".$ext;
                                    }
                                    //$startIndex = strpos($v,"/Public");
                                    //删除旧照片
                                    //unlink('.' . substr($v,$startIndex));
                                }
                            }
                            $index = $index + 1;
                        }
                        $roomid = $Data['roomid'];
                        //找到这个房子原来的图片资源
                        $myRoom = M('landlord_room')->field('images')->where('id=' . $roomid)->find();
                        if($myRoom){
                            $olds= explode(";",$myRoom['images']);
                        }
                        //更新已有房源
                        $update = array(
                            'xiaoqu' => $Data['xiaoqu'],
                            'size' => $Data['size'],
                            'yajinfangshi' => $Data['yajinfangshi'],
                            'price' => $Data['yuezujin'],
                            'huxing' => $Data['huxing'],
                            'visit_time' => $Data['visit_time'],
                            'start_date' => $Data['start_date'],
                            'floor_type' => $Data['floor_type'],
                            'floor_count' => $Data['floor_count'],
                            'description' => $Data['description'],
                            'city' => $Data['city'],
                            'subway' => $Data['subway'],
                            'type' => $Data['type'],
                            'zhongjie' => $Data['zhongjie'],
                            'lon' => $Data['lon'],
                            'lat' => $Data['lat'],
                            'address' => $Data['address'],
                            'images'=>implode(";",$oldImages),
                            'update_time' => date("Y-m-d H:i:s",time()),
                        );
                        $res = M('landlord_room')->where('id='.$roomid)->save($update); 
                        $index = 0;
                        if($res){
                            //删除这个房子原来的图片资源
                            foreach ($olds as $k => $v) {
                                if($v != ''&&($v!=$oldImages[$index]||$oldImages[$index]=="")){
                                    $startIndex = strpos($v,"/Public");
                                    //删除旧照片
                                    unlink('.' . substr($v,$startIndex));
                                }
                                $index = $index + 1;
                            }
                        }
                        xformatOutPutJsonData('success', $res, '房屋id'.$roomid);
                        // xformatOutPutJsonData('success', $olds, $oldImages);
                    }
                }else{
                    xformatOutPutJsonData('fail', $Data['zhongjie'], "中介内容有违法违规内容");
                }
            }else{
                xformatOutPutJsonData('fail', $Data['xiaoqu'], "小区名称内容有违法违规内容");
            }
        }else{
            xformatOutPutJsonData('fail', $Data['description'], "房屋概况内容有违法违规内容");
        }
    }
    //获取指数函数数值,要求 函数递减，(x1, y1) 是整个函数的最高点
    /**
     *Interpolate y with function y = e ^ (ax + b)
     *@x1, y2: the first known point
     *@x2, y2: the second known point
     *@x: the x-value of the point to be interpolated
     *@return: the interpolation of x
     */
    function getExpValue($x1, $y1, $x2, $y2, $x) {
        $epsilon = 0.00000000001;  # used to avoid zero-division
        $a = ($y2 - $y1) / (($x1 - $x2)**2 + $epsilon);
        $b = -2 * $x1 * $a;
        $c = $a * ($x1**2) + $y1;
        # compute the interpolation value and return it
        return $a * ($x**2) + $b * $x + $c;
    }
    //房客匹配
    public function roomerMatch() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $uid)) {
            xformatOutPutJsonData('fail', 2, '网络错误2！');
        }
        //设置房源各个特征的权重
        $weight = array(
            'distance' => 100,//距离权重
            'subway_line' => 40,//同一条地铁线路加分
            'time' => 0,//入住时间匹配
            'price' => 10,//预算匹配时的加分
            'shoucang' => 20,//用户收藏了我的房源的加分
        );
        //当前用户房源信息
        $roominfo = M('landlord_room')->field('id,price,start_date,subway,lon,lat')->where('master_id=' . $uid)->select();
        //获取那些被我拉黑的室友
        $heimingdan = M('shoucang')->field('heimingdan')->where('uid= -' . $uid)->find();
        $shanchumingdan = explode(",",$heimingdan['heimingdan']);
        foreach ($roominfo as $k => $room) {
            /*设置要选取的房源地点的经纬度界限
            1.同一条经线上，纬度相差1°，其距离相差约111千米。
            2..在同一条纬线上（假设此纬线的纬度为Φ），经度相差1°对应的实际弧长大约为111×cosΦ千米。
            假设只显示方圆10公里以内的室友，那么先找出纬度相差0.09度，经度相差0.1度的地铁站和商圈
            */
            $sqlCondition = "lon<".($room['lon']+0.1). "AND lon>".($room['lon']-0.1) . "AND lat<".($room['lat']+0.09)." AND lat>" .($room['lat']-0.09);
            $subways_result =  M('subways')->field('id')->where($sqlCondition)->select();
            // xformatOutPutJsonData('test2', $subways_result, M()->getLastSql());
            $subway = array_column($subways_result, 'id');//取出对象数组的属性
            //找出所有跟当前房屋地铁站在一条地铁线路的地铁站
            $room_subway = M('subways')->field('city,station')->where("id=".$room['subway'])->find();
            $room_line = M('subways')->field('city,line')->where('city="'.$room_subway['city'].'" AND station="'.$room_subway['station'].'"')->select();
            $subways_line = array();
            foreach($room_line as $k=>$v){
                $subways_result =  M('subways')->field('id')->where('city="'.$v['city'].' "AND line="' .$v['line'].'"')->select();
                $subways_line = array_merge($subways_line, array_column($subways_result, 'id'));
            }
            $subway = array_merge($subway,$subways_line);
            $circles_result =  M('circles')->field('id')->where($sqlCondition)->select();
            $circle = array_column($circles_result, 'id');
            $map1['ditie'] = array('in',$subway);
            $map2['shangquan'] = array('in',$circle);
            //排除空情况
            if(count($circle)==0){
                array_push($circle,0);
            }
            if(count($subway)==0){
                array_push($subway,0);
            }
            // $mymatchuser = M('user')->field("id,name,avatar,sex,shangquan,ditie,budget,school,work,is_place,has_room")->where("( is_place=1 AND shangquan in (" .implode(",",$circle) .") OR is_place=2 AND ditie in (".implode(",",$subway).")) AND is_match=2 AND checkoutime>='".$room['start_date']."'")->select();
            //这里先不限制入住日期了，以后用户多了要开始限制
            $mymatchuser = M('user')->field("id,name,avatar,sex,shangquan,ditie,budget,school,work,is_place,has_room")->where("( is_place=1 AND shangquan in (" .implode(",",$circle) .") OR is_place=2 AND ditie in (".implode(",",$subway).")) AND is_match=2 ")->select();
            //先匹配宋立军
            // $mymatchuser = M('user')->field("id,name,avatar,sex,shangquan,ditie,school,work,is_place,has_room")->where("id=1336")->select();
            // xformatOutPutJsonData('test1', $mymatchuser, $shanchumingdan);
            foreach ($mymatchuser as $key => $value) {
                //删除我拉黑的房客
                $index = array_search($value['id'],$shanchumingdan);
                // xformatOutPutJsonData('test3', $index, $index !== FALSE);
                if($index !== FALSE)
                    continue;
                //获取该租客的位置
                if (intval($value['is_place']) == 1) {
                    $hisPosition = M('circles')->field('city_name as city,area_name,name,lon,lat')->where('id='. $value['shangquan'])->find();
                    $value['zuzhupos'] = $hisPosition['area_name'] . $hisPosition['name'];
                }else{
                    $hisPosition = M('subways')->field('city,line,station,lon,lat')->where('id='. $value['ditie'])->find();
                    $value['zuzhupos'] = $hisPosition['line'] . $hisPosition['station'];
                }
                $distance = A('Home/Index')->GetDistance(floatval($room['lon']),floatval($room['lat']),floatval($hisPosition['lon']),floatval($hisPosition['lat']));
                // $value['fraction'][] = $distance;
                //15公里以内分数为正，15公里以外分数为负，如果是一条线上的再加分
                if($distance>20){
                    //这里先不限制最大距离了，超过20公里就算0分，以后用户多了要开始限制
                    // $fraction = 0;
                    // $value['fraction'][] = 0;
                    continue;
                }else {
                    if($distance>15)
                        $distance = 15;
                    // $fraction = bcmul(10-$distance, $weight['position']);
                    //10公里分数为0，0公里分数为满分，如果是一条线上的再加分
                    $fraction = $this->getExpValue(0,$weight['distance'],15,0.1,$distance);
                    // $value['fraction'][] = $this->getExpValue(0,$weight['distance'],15,0.1,$distance);
                }
                // xformatOutPutJsonData('test3', $fraction, $distance);
                if (intval($value['is_place']) == 2){
                    $index = array_search($value['ditie'],$subways_line);
                    if($index !== FALSE){
                        $fraction += $weight['subway_line']/ceil($distance);
                        // $value['fraction'][] = $weight['subway_line']/ceil($distance);
                    }
                }
                switch($value['budget']){
                    case 1:
                        $bud_low = 0;
                        $bud_high = 1500;
                        break;
                    case 2:
                        $bud_low = 1501;
                        $bud_high = 2000;
                        break;
                    case 3:
                        $bud_low = 2001;
                        $bud_high = 3000;
                        break;
                    case 4:
                        $bud_low = 3001;
                        $bud_high = 5000;
                        break;
                    case 5:
                        $bud_low = 5001;
                        $bud_high = 10000;
                        break;
                    case 6:
                        $bud_low = 10001;
                        $bud_high = 15000;
                        break;
                }
                if($room['price']>=$bud_low && $room['price']<=$bud_high){
                    $fraction += $weight['price'];
                    // $value['fraction'][] = $weight['price'];
                }
                //判断该用户是否收藏了我的这个房源
                $houses = M('shoucang')->field('likehouse')->where('uid=' . $value['id'])->find();
                if($houses){
                    $houses = json_decode($houses['likehouse'],true);
                    $ids = explode(",",$houses['landlord']);
                    $index = array_search($room['id'],$ids);
                    if(!($index===FALSE)){
                        $fraction += $weight['shoucang'];
                        $value['labels'][] = '已收藏';
                        // $value['fraction'][] = $weight['shoucang'];
                    }    
                }
                $value['pipeidu'] = bcdiv($fraction, $weight['distance']+$weight['price'], 4) * 100;
                // xformatOutPutJsonData('test2', $value, $distance);
                if($value['pipeidu']>=100){
                    $value['pipeidu'] = 99.99;
                }
                //这里限制最低匹配度大于0
                if($value['pipeidu']>0){
                    if($value['has_room']==1)
                        $value['labels'][] = '有房';
                    else
                        $value['labels'][] = '无房';
                    // $value['labels'][] = '已收藏';
                    // unset($value['checkintime']);
                    unset($value['school']);
                    unset($value['work']);
                    unset($value['has_room']);
                    if (empty($value['avatar'])) {
                        if ($value['sex'] == 1) {
                            $value['Tximg'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnan1.png';
                        } else {
                            $value['Tximg'] = str_replace('http','https',IMG_PATH) . '/Public/avatar/touxiangnv1.png';
                        }
                    } 
                    else {
                        $value['Tximg'] = $value['avatar'];
                    }
                    unset($value['avatar']);
                    if (empty($value['name'])) {
                        xformatOutPutJsonData('fail', '', 'name为空');
                    } else {
                        $value['nickName'] = base64_decode($value['name']);
                    }
                    unset($value['name']);
                    if ($value['sex'] == 1) {
                        $value['sex'] = 'man';
                    } else {
                        $value['sex'] = 'women';
                    }
                    
                    unset($value['is_place']);
                    unset($value['shangquan']);
                    unset($value['ditie']);
                    $res[] = $value;
                }
            }
        }
        $data = A('Home/Index')->arraySort($res, 'pipeidu', 'desc');
        $res = array_values($data);
        $temp_id = array();
        foreach ($res as $k => $v) {
            if (in_array($v['id'], $temp_id)) {//搜索$v[$key]是否在$temp_id数组中存在，若存在返回true
                // unset($res[$k]);
            } else {
                $result[] = $v;
                $temp_id[] = $v['id'];
            }
        }
        // xformatOutPutJsonData('test', $result, $data);
        if(!$Data['count'])
            $Data['count'] = 30;//现在设置初始查看30个室友，需要和小程序一起改动
        else if($Data['count'] > 30)
            $Data['count'] = 200;//现在设置最多查看200个室友，需要和小程序一起改动
        if (!empty($result)) {
            $tmp_arr = array();
            foreach ($result as $k => $v) {
                if (in_array($v['id'], $tmp_arr)) {
                    unset($result[$k]);
                } else {
                    $tmp_arr[] = $v[$key];
                }
            }
            xformatOutPutJsonData('success', array_slice($result,0,intval($Data['count'])), count($result));
        } else {
            xformatOutPutJsonData('success', 1, '没有合适的租客');
        }
    }
    
    /**
     * 多维数组去重
     * @param $arr
     * @param $key
     * @return array
     */
    function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        return $arr;
    }
    //房源高级匹配
    public function roomMatch() {
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $uid)) {
            xformatOutPutJsonData('fail', 2, '网络错误2！');
        }
        //设置房源各个特征的权重
        $weight = array(
            'distance' => 100,//距离权重
            'size' => 10,//设置成负数吧
            'floor_count' => 3,//设置成负数吧
            'huxing' => 2,//m室n卫 m/n相等时加分
        );
        //当前用户房源信息
        $roominfo = M('landlord_room')->field('id,xiaoqu as title,yajinfangshi,price,huxing as type2,size, visit_time,start_date,floor_type,floor_count as floor,subway,lon,lat,address,type,images,description,publish')->where('master_id=' . $uid)->select();
        //获取那些被我拉黑的房源
        $heimingdan = M('shoucang')->field('dislikehouse')->where('uid= -' . $uid)->find();
        if($heimingdan){
            $shanchumingdan = json_decode($heimingdan['dislikehouse'],true);
        }else{
            // $shanchumingdan ='';
        }
        foreach ($roominfo as $k => $room) {
            //计算与每个房源相似的房源展示
            
            /*设置要选取的房源地点的经纬度界限
            1.同一条经线上，纬度相差1°，其距离相差约111千米。
            2..在同一条纬线上（假设此纬线的纬度为Φ），经度相差1°对应的实际弧长大约为111×cosΦ千米。
            假设只显示方圆10公里以内的房源，那么纬度相差0.09度，经度相差0.1度吧
            */
            $sqlCondition = "lon<".($room['lon']+0.1). "AND lon>".($room['lon']-0.1) . "AND lat<".($room['lat']+0.09)." AND lat>" .($room['lat']-0.09);
            // xformatOutPutJsonData('test', $room['type2'], $room);
            //我的房间是几室几卫
            $myShiWei = intval(explode("室",$room['type2'])[0])/intval(explode("厅",$room['type2'])[1]);
            //我的房间是几室几厅
            $myShiTing = intval(explode("室",$room['type2'])[0])/intval(explode("室",$room['type2'])[1]);
            //蛋壳公寓的房源
            $danke = M('danke')->field('id,room_title as title,lon, lat,room_subway as transport,feature,normal_price,promotion_price,room_id,room_size as size,room_type,room_floor,room_image_link, rent_whole')->where($sqlCondition)->limit(10)->select();
            foreach ($danke as $key => $value) {
                //删除我拉黑的房源
                $index = array_search($value['room_id'],explode(",",$shanchumingdan['danke']));
                // xformatOutPutJsonData("test", $index !== FALSE, $index);
                if($index !== FALSE)
                    continue;
                $value['brand'] = '蛋壳';
                $value['id'] = 'danke'.$value['id'];
                $fraction = 0;
                //租住地点匹配,计算两地的距离
                $distance = A('Home/Index')->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($room['lon']),floatval($room['lat']));
                //10公里以内分数为正
                if($distance>12)
                    continue;
                else if($distance>10)
                    $distance = 10;
                $fraction = $this->getExpValue(0,$weight['distance'],10,0.1,$distance);
                // $value['fraction'][] = $this->getExpValue(0,$weight['distance'],10,0.1,$distance);
                unset($value['lon']);
                unset($value['lat']);
                //面积：相差的面积越大，分数越低；面积相差小于50才加分
                $sizeDiff = abs(floatval($value['size'])-$room['size']);
                if($sizeDiff<=50){
                    $fraction += $this->getExpValue(0,$weight['size'],50,0.01,$sizeDiff);
                    // $value['fraction'][] = $this->getExpValue(0,$weight['size'],50,0.01,$sizeDiff);
                }
                //总楼层：相差得越大，分数越低；
                $floosInfo = explode('/',$value['room_floor']);
                $floor_count_diff = abs(intval($floosInfo[1])-$room['floor']);
                //楼层相差小于20才加分
                if($floor_count_diff<=20){
                    $fraction += $this->getExpValue(0,$weight['floor_count'],20,0.01,$floor_count_diff);
                    // $value['fraction'][] = $this->getExpValue(0,$weight['floor_count'],20,0.01,$floor_count_diff);
                }
                $value['type2'] = explode(",",$value['room_type'])[0];
                //n室m厅：n/m > 相等的加分
                $shiWeiNum = explode("室",$value['type2']);
                if(abs(intval($shiWeiNum[0])/intval($shiWeiNum[1] - $myShiWei))<0.1){
                    $fraction += $weight['huxing'];
                    // $value['fraction'][] = $weight['huxing'];
                }
                $value['floor'] = $value['room_floor'].'层';
                unset($value['location']);
                unset($value['room_type']);
                unset($value['room_floor']);
                if($value['promotion_price']!=0){
                    $value['price'] = $value['promotion_price'];
                }else{
                    $value['price'] = $value['normal_price'];
                }
                unset($value['promotion_price']);
                unset($value['normal_price']);
                $images = json_decode($value['room_image_link'],true);
                $value['image'] = $images[0];
                unset($value['room_image_link']);
                $value['ratio'] = bcdiv($fraction, $weight['distance']+$weight['size']+$weight['floor_count']+$weight['huxing'], 4) * 100;
                $value['labels'] = explode(",",$value['feature']);
                if($value['rent_whole']==1){
                    $value['labels'][] = '整租';
                }
                else if($value['rent_whole']==0)
                    $value['labels'][] = '合租';
                unset($value['feature']);
                unset($value['rent_whole']);
                $value['room_id'] = 'danke'.$value['room_id'];
                $res[] = $value;
            }
            //我爱我家的的房源
            $woaiwojia = M('woaiwojia')->field('id,room_title as title,room_id,normal_price as price, room_size as size,room_type as type2,room_floor as floor,lon,lat,room_subway,room_subway,room_business,room_location,room_image_link,feature,room_decoration,room_building_type,room_rent_type')->where($sqlCondition)->limit(10)->select();
            // xformatOutPutJsonData('test', $woaiwojia, count($woaiwojia));
            foreach ($woaiwojia as $key => $value) {
                //删除我拉黑的房源
                $index = array_search($value['room_id'],explode(",",$shanchumingdan['woaiwojia']));
                // xformatOutPutJsonData($index, $shanchumingdan, explode(",",$shanchumingdan['ziruyu']));
                if($index !== FALSE)
                    continue;
                $value['brand'] = '我爱我家';
                $value['id'] = 'woaiwojia'.$value['id'];
                //室友匹配 我爱我家没有室友

                $distance = A('Home/Index')->GetDistance(floatval($value['lon']),floatval($value['lat']),floatval($room['lon']),floatval($room['lat']));
                //租住地点匹配,计算两地的距离,10公里以内分数为正
                if($distance>12)
                    continue;
                else if($distance>10)
                    $distance = 10;
                $fraction = $this->getExpValue(0,$weight['distance'],10,0.1,$distance);
                // $value['fraction'][] = $this->getExpValue(0,$weight['distance'],10,0.1,$distance);
                unset($value['lon']);
                unset($value['lat']);
                //面积：相差的面积越大，分数越低；面积相差小于50才加分
                $sizeDiff = abs(floatval($value['size'])-$room['size']);
                if($sizeDiff<=50){
                    $fraction += $this->getExpValue(0,$weight['size'],50,0.01,$sizeDiff);
                    // $value['fraction'][] = $this->getExpValue(0,$weight['size'],50,0.01,$sizeDiff);
                }
                //总楼层：相差得越大，分数越低；
                $floosInfo = explode('/',$value['floor']);
                $floor_count_diff = abs(intval($floosInfo[1])-$room['floor']);
                //楼层相差小于20才加分
                if($floor_count_diff<=20){
                    $fraction += $this->getExpValue(0,$weight['floor_count'],20,0.01,$floor_count_diff);
                    // $value['fraction'][] = $this->getExpValue(0,$weight['floor_count'],20,0.01,$floor_count_diff);
                }
                //n室m厅：n/m > 相等的加分
                $shiTingNum = explode("室",$value['type2']);
                if(abs(intval($shiTingNum[0])/intval($shiTingNum[1] - $myShiTing))<0.2){
                    $fraction += $weight['huxing'];
                    // $value['fraction'][] = $weight['huxing'];
                }
                if($value['room_subway']!='')
                    $value['transport'] = $value['room_subway'];
                else $value['transport'] = $value['room_business'] ."附近" .$value['room_location'];
                unset($value['room_subway']);
                unset($value['room_business']);
                unset($value['room_location']);
                
                if($value['room_image_link']!='[]'){
                    $images = json_decode($value['room_image_link'],true);
                    $value['image'] = $images[0];
                }else
                    $value['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
                unset($value['room_image_link']);
                //$value['ratio'] = round($fraction/($PosWeight['city_score']/50),2);//还没想好怎么计算匹配度
                $value['ratio'] = bcdiv($fraction, $weight['distance']+$weight['size']+$weight['floor_count']+$weight['huxing'], 4) * 100;
                $value['labels'] = explode(",",$value['feature']);
                if($value['room_decoration']!='')
                    $value['labels'][] = $value['room_decoration'];
                if($value['room_building_type']!=''&& $value['room_building_type']!='其他')
                    $value['labels'][] = $value['room_building_type'];
                if($value['room_rent_type']!=''&& $value['room_rent_type']!='其他')
                    $value['labels'][] = $value['room_rent_type'];
                unset($value['feature']);
                unset($value['room_decoration']);
                unset($value['room_building_type']);
                unset($value['room_rent_type']);
                $value['room_id'] = 'woaiwojia'.$value['room_id'];
                $res[] = $value;
            }

            //其他房东上传的房源
            $landlordRooms = M('landlord_room')->field('id,xiaoqu as title,yajinfangshi,price,huxing as type2,size, visit_time,start_date,floor_type,floor_count as floor,subway,lon,lat,type,images,publish')->where($sqlCondition."AND publish=1 AND master_id !=".$uid)->select();
            foreach($landlordRooms as $lk => $lv){
                $lv['room_id'] = 'landlord'.$lv['id'];
                $distance = A('Home/Index')->GetDistance(floatval( $lv['lon']),floatval( $lv['lat']),floatval($room['lon']),floatval($room['lat']));
                //租住地点匹配,计算两地的距离,10公里以内分数为正
                if($distance>12)
                    continue;
                else if($distance>10)
                    $distance = 10;
                $fraction = $this->getExpValue(0,$weight['distance'],10,0.1,$distance);
                // $lv['fraction'][] = $this->getExpValue(0,$weight['distance'],10,0.1,$distance);
                unset($lv['lon']);
                unset($lv['lat']);
                //面积：相差的面积越大，分数越低；面积相差小于50才加分
                $sizeDiff = abs(floatval($lv['size'])-$room['size']);
                if($sizeDiff<=50){
                    $fraction += $this->getExpValue(0,$weight['size'],50,0.01,$sizeDiff);
                    // $lv['fraction'][] = $this->getExpValue(0,$weight['size'],50,0.01,$sizeDiff);
                }
                //总楼层：相差得越大，分数越低；
                $floor_count_diff = abs(intval($lv['floor'])-$room['floor']);
                //楼层相差小于20才加分
                if($floor_count_diff<=20){
                    $fraction += $this->getExpValue(0,$weight['floor_count'],20,0.01,$floor_count_diff);
                    // $lv['fraction'][] = $this->getExpValue(0,$weight['floor_count'],20,0.01,$floor_count_diff);
                }
                //n室m厅：n/m > 相等的加分
                // xformatOutPutJsonData('test', $myShiWei, explode("室",$lv['type2']));
                $hisShiWei = intval(explode("室",$lv['type2'])[0])/intval(explode("厅",$lv['type2'])[1]);
                $hisShiTing = intval(explode("室",$lv['type2'])[0])/intval(explode("室",$lv['type2'])[1]);
                if(abs($hisShiWei-$myShiWei)<0.2){
                    $fraction += $weight['huxing'];
                    // $lv['fraction'][] = $weight['huxing'];
                }
                if(abs($hisShiTing-$myShiTing)<0.2){
                    $fraction += $weight['huxing'];
                    // $lv['fraction'][] = $weight['huxing'];
                }
                $lv['ratio'] = bcdiv($fraction, $weight['distance']+$weight['size']+$weight['floor_count']+$weight['huxing'], 4) * 100;
                if($lv['ratio']>=100){
                    $lv['ratio'] = 99.99;
                }
                if($lv['ratio']>0){
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
                    $transport = M('subways')->field('line,station')->where('id='.$lv['subway'])->find();
                    if($transport){
                        $lv['transport'] = $transport['line'].$transport['station'].'附近';
                    }else{
                        $lv['transport'] = '房东未按规定上传附近交通信息';
                    }
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
                        $lv['labels'][] = '周中';
                    }
                    unset($lv['visit_time']);
                    if($lv['floor_type']==1){
                        $lv['labels'][] = '电梯房';
                    }else{
                        $lv['labels'][] = '楼梯房';
                    }
                    unset($lv['floor_type']);
                    unset($lv['start_date']);
                    $res[] = $lv;
                }
            }
            //处理本房源的信息
            $roominfo[$k]['room_id'] = 'landlord'.$roominfo[$k]['id'];
            unset($roominfo[$k]['lon']);
            unset($roominfo[$k]['lat']);

            //unset($roominfo[$k]['id']);
            if($roominfo[$k]['type']==1){
                $roominfo[$k]['brand'] ='房东';
            }else if($roominfo[$k]['type']==2){
                $roominfo[$k]['brand'] ='转租';
            }else{
                $roominfo[$k]['brand'] ='中介';
            }
            if($roominfo[$k]['publish']==0){
                $roominfo[$k]['title'] = $roominfo[$k]['title'].'[已下架]';
            }
            unset($roominfo[$k]['publish']);
            unset($roominfo[$k]['type']);
            $roominfo[$k]['floor'] = $roominfo[$k]['floor'].'层';
            $transport = M('subways')->field('line,station')->where('id='.$roominfo[$k]['subway'])->find();
            if($transport){
                $roominfo[$k]['transport'] = $transport['line'].$transport['station'].'附近';
            }else{
                $roominfo[$k]['transport'] = '房东未按规定上传附近交通信息';
            }
            unset($roominfo[$k]['subway']);
            if($roominfo[$k]['images']!=''){
                $images = explode(";",$roominfo[$k]['images']);
                foreach($images as $ik=>$iv){
                    if($iv!=""){
                        $roominfo[$k]['image'] = $iv;
                        break;
                    }
                }
            }else
                $roominfo[$k]['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
            // unset($roominfo[$k]['images']);
            $roominfo[$k]['labels'][] = $roominfo[$k]['yajinfangshi'];
            unset($roominfo[$k]['yajinfangshi']);
            if($roominfo[$k]['visit_time']==1){
                $roominfo[$k]['labels'][] = '随时看房';
            }else if($roominfo[$k]['visit_time']==2){
                $roominfo[$k]['labels'][] = '周末看房';
            }else{
                $roominfo[$k]['labels'][] = '周中';
            }
            unset($roominfo[$k]['visit_time']);
            if($roominfo[$k]['floor_type']==1){
                $roominfo[$k]['labels'][] = '电梯房';
            }else{
                $roominfo[$k]['labels'][] = '楼梯房';
            }
            unset($roominfo[$k]['floor_type']);
            // unset($roominfo[$k]['start_date']);
            $roominfo[$k]['ratio'] = 100;
        }
        

        //按照匹配分数排序
        $sort = array_column($res, 'ratio');      
        array_multisort($sort, SORT_DESC, $res);
        $temp_roomid = array();
        foreach ($res as $k => $v) {
            if (in_array($v['room_id'], $temp_roomid)) {//搜索$v[$key]是否在$temp_roomid数组中存在，若存在返回true
                // unset($res[$k]);
            } else {
                $result[] = $v;
                $temp_roomid[] = $v['room_id'];
            }
        }
        // xformatOutPutJsonData('test', $res, $result);
        if(!$Data['count'])
            $Data['count'] = 30;//现在设置初始查看50个房源，需要和小程序一起改动
        else if($Data['count'] > 30)
            $Data['count'] = 300;//现在设置最多查看300个房源，需要和小程序一起改动
        $result = array_slice($result,0,intval($Data['count']));
        // $res = array_slice($res,0,5);
        if (empty($result)) {
            $result = $roominfo;
        } 
        xformatOutPutJsonData('success', $result, $roominfo);
    }
    
    /*
     * 获取房东信息用于我的页面
     */
    public function getMyinfo() {
        $Data = I('get.');
        $uid = $Data['id'];//当前用户id
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] !== S('user_landlord' . $uid)) {
            xformatOutPutJsonData('fail', 2, '网络错误2！');
        }
        $landlord = M('landlord')->field('id,avatar as touxiang,name,phone')->where('id= '.$uid)->find();
        if($landlord){
            xformatOutPutJsonData('success', $landlord, "");
        }
    }
    /*
     * 获取房东详细信息用于展示
     */

    public function getUserinfo() {
        $Data = I('get.');
        $uid = $Data['id'];//当前用户id
        if (empty($Data['pipei'])) {
            xformatOutPutJsonData('fail', '', '网络错误4！');
        }else if(!$Data['pipei']||$Data['pipei']<0){
            //通过二维码扫描进来的没有匹配度，以后看需要加啥
            //$res['type'] = 'minicode';
        }else{
            if (empty($uid)) {
                xformatOutPutJsonData('fail', '', '网络错误1！');
            }
            if ($Data['token'] !== S('user_' . $uid) && $Data['token'] !== S('user_landlord' . $uid)) {
                xformatOutPutJsonData('fail', '', '网络错误2！');
            }
        }
        if (empty($Data['pid'])) {
            xformatOutPutJsonData('fail', '', '网络错误3！');
        }
        $landlord = M('landlord')->field('id,gender as sex,avatar as touxiang,name,province,city,work,birth')->where('id= '.$Data['pid'])->find();
        if($landlord){
            if($landlord['sex']=='1'){
                $landlord['sex'] = 'man';
            }else if($landlord['sex']=='2'){
                $landlord['sex'] = 'woman';
            }
            $work = M('work')->field('w_name')->where('id=' . $landlord['work'])->find();
            $landlord['work'] = $work['w_name'];

            if($Data['token'] === S('user_landlord' . $uid)){//房东用户发起的访问
                $uid = -1 * intval($uid);
            }
            //返回当前是否已经被收藏或者拉黑
            $landlord['shoucang'] = 0;
            $landlord['heimingdan'] = 0;   
            $result3 = M('shoucang')->field('shoucang,heimingdan')->where('uid=' . $uid)->find();
            if($result3){
                $myShoucang = explode(",",$result3['shoucang']);
                $myHeimingdan = explode(",",$result3['heimingdan']);
                $index = array_search('-'.$Data['pid'],$myShoucang);
                if(!($index===FALSE))
                    $landlord['shoucang'] = 1;
                $index = array_search('-'.$Data['pid'],$myHeimingdan);
                if(!($index===FALSE))
                    $landlord['heimingdan'] = 1;    
            }

            //当前用户房源信息
            $roominfo = M('landlord_room')->field('id,xiaoqu as title,yajinfangshi,price,huxing as type2,size, visit_time,floor_type,floor_count as floor,subway,type,images,publish')->where('master_id=' . $landlord['id'])->select();
            foreach ($roominfo as $k => $room) {
                //处理本房源的信息
                $room['room_id'] = 'landlord'.$room['id'];
                //unset($room['id']);
                if($room['type']==1){
                    $room['brand'] ='房东';
                }else if($room['type']==2){
                    $room['brand'] ='转租';
                }else{
                    $room['brand'] ='中介';
                }
                if($room['publish']==0){
                    $room['title'] =$room['title'].'已下架';
                }
                unset($room['publish']);
                unset($room['type']);
                $room['floor'] = $room['floor'].'层';
                $transport = M('subways')->field('line,station')->where('id='.$room['subway'])->find();
                if($transport){
                    $room['transport'] = $transport['line'].$transport['station'].'附近';
                }else{
                    $room['transport'] = '房东未按规定上传附近交通信息';
                }
                unset($room['subway']);
                if($room['images']!=''){
                    $images = explode(";",$room['images']);
                    $room['image'] = $images[0];
                }else
                    $room['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
                unset($room['images']);
                $room['labels'][] = $room['yajinfangshi'];
                unset($room['yajinfangshi']);
                if($room['visit_time']==1){
                    $room['labels'][] = '随时看房';
                }else if($room['visit_time']==2){
                    $room['labels'][] = '周末看房';
                }else{
                    $room['labels'][] = '周中';
                }
                unset($room['visit_time']);
                if($room['floor_type']==1){
                    $room['labels'][] = '电梯房';
                }else{
                    $room['labels'][] = '楼梯房';
                }
                unset($room['floor_type']);
                unset($room['start_date']);
                $res[] = $room;
            }
            xformatOutPutJsonData('success', $landlord, $res);
        }else{
            xformatOutPutJsonData('fail', M()->getLastSql(), '无效的房东id');
        }
    }
    public function getMiniCodeImage(){
        $Data = I('get.');
        $uid = $Data['id'];
        // 检验uid合法性 防止非法越界
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if($Data['token'] == S('user_landlord' . $uid)) {
            $uid = $uid.'-';
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if($Data['roomid']){
            $uid = $uid . $Data['roomid'];
            if(A('Home/Chat')->get_mini_code($data=$uid,$page='pages/roomDetail/roomDetail',$width=280)>2000){
                xformatOutPutJsonData('success', '', '');
            }else{
                xformatOutPutJsonData('fail', '', '');
            }
        }else{
            if(A('Home/Chat')->get_mini_code($data=$uid,$page='pages/landlordxiangqing/landlordxiangqing',$width=280)>2000){
                xformatOutPutJsonData('success', '', '');
            }else{
                xformatOutPutJsonData('fail', '', '');
            }
        }
    }
}
