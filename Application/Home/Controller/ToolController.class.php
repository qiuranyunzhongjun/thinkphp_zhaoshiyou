<?php

namespace Home\Controller;

use Common\Controller\HomeBaseController;

/**
 * 工具Controller
 */
class ToolController extends HomeBaseController {
    public function index(){
        xformatOutPutJsonData('success', time()+60000, '');
    }
    public function changeSchool(){
        $user = M('user')->select();
        $count = 0;
        foreach ($user as $key => $value) {
            //xformatOutPutJsonData('test', $value['school'], $value['is_match']);
            if($value['is_match']==2){
                $jiuditie = M('school1')->field('s_name')->where('sid='.$value['school'])->find();
                $xinditie = M('school')->field('sid')->where('s_name="'.$jiuditie['s_name'].'"')->find();
                $update['school'] = $xinditie['sid'];
                $res = M('user')->where('id='.$value['id'])->save($update);
                if($res)
                $count = $count + 1;
            }
        }
        xformatOutPutJsonData('success', '', $count);
    }
    public function changeSubway(){
    $user = M('user')->select();
    foreach ($user as $key => $value) {
        if($value['is_place']==2){
            $jiuditie = M('metro')->field('title')->where('id='.$value['ditie'])->find();
            if(!empty($jiuditie)){
                $xinditie = M('subways')->field('id')->where('station="'.$jiuditie['title'].'" AND city="北京市"')->find();
                $update['ditie'] = $xinditie['id'];
            }
        }
        if($value['age']<=20)
            $update['age'] = 1;
        else if($value['age']<=25)
            $update['age'] = 2;
        else if($value['age']<=30)
            $update['age'] = 3;
        else if($value['age']<=35)
            $update['age'] = 4;
        else if($value['age']<=40)
            $update['age'] = 5;
        else if($value['age']<=45)
            $update['age'] = 6;
        else if($value['age']<=50)
            $update['age'] = 7;
        else if($value['age']<=60)
            $update['age'] = 8;
        else $update['age'] = 9;
        $res = M('user')->where('id='.$value['id'])->save($update);
        //xformatOutPutJsonData('test', $res, M()->getLastSql());
    }
    }
    public function index1(){
        $user = M('subway')->select();
        $count = 0;
        foreach ($user as $key => $value) {
            $inserData = array(
                'id' => $value['id'],
                'city' => $value['city'],
                'line' => $value['line'],
                'station' => $value['station'],
                'lon' => floatval(explode(',',$value['data'])[0]),
                'lat' => floatval(explode(',',$value['data'])[1]),
            );
            M('subways')->add($inserData);
            $count = $count + 1;
        }
        dump('success'+$count);
    }
    public function index2(){
        $user = M('metro')->where('pid> '. '0')->select();
        $count = 0;
        foreach ($user as $key => $value) {
            $inserData = array(
                'city' => '北京市',
                'line' => $value['pid'],
                'station' => $value['title'],
                'lon' => $value['lon'],
                'lat' => $value['lat'],
            );
            M('subways')->add($inserData);
            $count = $count + 1;
        }
        dump('success'+$count);
    }
    //修改原来的位置信息
    public function natsort(){
        $data['ditie'] = M('subways')->field('DISTINCT line')->where('city="北京市"')->select();
        dump($data['ditie']);
        natsort($data['ditie']);
        dump($data['ditie']);
    }
    
    //根据小区名获取小区的经纬度信息
    function getLocationByName($villageName,$nearStation,$city='北京'){

        //先用附近的地铁站获取行政区
        $url = "https://restapi.amap.com/v3/geocode/geo?address={$nearStation}&ouptput=JSON&city={$city}&key=ae2ae2221d74fe13616baed69a869080";
        $locResult =  A('Home/Chat')->request_get($url);
        $locResult = json_decode(stripslashes($locResult));
        $result = json_decode(json_encode($locResult), true);
        $district = $result['geocodes'][0]['district'];
        $village = $district. $nearStation . $villageName;
        // dump($village);
        $url = "https://restapi.amap.com/v3/geocode/geo?address={$village}&ouptput=JSON&city={$city}&key=ae2ae2221d74fe13616baed69a869080";
        $locResult =  A('Home/Chat')->request_get($url);
        // dump($locResult);
        $locResult = json_decode(stripslashes($locResult));
        $result = json_decode(json_encode($locResult), true);
        $location = $result['geocodes'][0]['location'];
        // dump($location);
        return $location;
    }

    //将数据库中的房源信息的小区名变成经纬度存储
    public function changeVillage2Location() {
        $data = I('get.');
        $to = $data['record']+500;//从第几条记录开始处理500条记录
        $total = M('rooms')->field("id")->count();
        $complet = 0;
        for($page = $data['record']/50; $page<$total/50 && $page<$to/50; $page++ ){
            $rooms = M('ziroom_20190405_result')->field("id,VILLAGE,STATION")->page($page,50)->select();
            foreach ($rooms as $k => $v) {
                $station = $v['station'];
                $village = $v['village'];
                $locResult =  $this->getLocationByName($village,$station);
                $locs = explode(",",$locResult);
                //dump($locs[0]);
                $update['lon'] = floatval($locs[0]);
                $update['lat'] = floatval($locs[1]);
                $res = M('ziroom_20190405_result')->where("id='".$v['id']."'")->save($update);
                $complete += $res;
            }
        }
        xformatOutPutJsonData('success', "本次修改记录数： ".$complete, "下次调用请设置record为：".$to);
    }
    //将数据库中的房源信息的小区名变成经纬度存储之后再处理一下那些有问题的
    public function afetrChangeVillage2Location() {
        $to = 200;//处理200条记录
        $total = M('rooms')->field("id")->where('lon<1 and lat <1')->count();
        $complet = 0;
        for($page = 0; $page<$total/50 && $page<$to/50; $page++ ){
            $rooms = M('ziroom_20190405_result')->field("id,VILLAGE,STATION")->where('lon<1 and lat <1')->page($page,50)->select();
            foreach ($rooms as $k => $v) {
                $station = $v['station'];
                $village = $v['village'];
                $locResult =  $this->getLocationByName($village,$station);
                $locs = explode(",",$locResult);
                //dump($locs[0]);
                $update['lon'] = floatval($locs[0]);
                $update['lat'] = floatval($locs[1]);
                $res = M('ziroom_20190405_result')->where("id='".$v['id']."'")->save($update);
                if($res)
                    $complete += $res;
                // else
                // xformatOutPutJsonData('error', $locResult, $v['id']);
            }
        }
        xformatOutPutJsonData('success', $complete, $total);
    }

    public function testChangeVillage2Location() {
        $room = M('rooms')->field("id,VILLAGE,STATION")->where('lon<1 and lat <1')->find();
        $station = $room['station'];
        $village = $room['village'];
        $locResult =  $this->getLocationByName($village,$station);
        $locs = explode(",",$locResult);
        $update['lon'] = floatval($locs[0]);
        $update['lat'] = floatval($locs[1]);
        $res = M('ziroom_20190405_result')->where("id='".$v['id']."'")->save($update);
        if($res)
            xformatOutPutJsonData('success', $res, $total);
        else
            xformatOutPutJsonData('error', $locs, $room);
    }

    //将正式服务器的数据库迁移到体验版服务器之后，需要更新token
    public function updateToken(){
        $users = M('user')->field('id,userToken')->select();
        $count = 0;
        foreach($users as $key=>$value){
            if($value['usertoken']==null) {
                $userToken = A('Home/Index')->getTokenCode($value['id']);
                $update['userToken'] = $userToken;
                M('user')->where('id='.$value['id'])->save($update);
            }else{
                // xformatOutPutJsonData('test', $value, count($users));
                S('user_' . $value['id'], null);
                S('user_' . $value['id'], $value['usertoken']);
                $count = $count + 1;
            }
        }
        xformatOutPutJsonData('success', '', $count);
    }

    //将服务器的微信头像全部下载到我的服务器
    public function updateAvatar(){
        $wx_member = M('user')->field('id,openid,avatar')->select();
        $count = 0;
        foreach($wx_member as $key=>$value){
            if(strpos($value['avatar'],'https://wx.')===0) {
                $file = file_get_contents($value['avatar']);
                $filename = './Public/Avatar/' .$value['openid']. '.jpg';
                $im = file_put_contents($filename, $file);
                if($im){
                    $update['avatar'] = str_replace('http','https',IMG_PATH) . '/Public/Avatar/'.$value['openid']. '.jpg';
                    M('user')->where('id='.$value['id'])->save($update);
                    $count ++;
                }
            }
        }
        xformatOutPutJsonData('success', '', $count);
    }
    
    //将服务器的微信头像链接全部由http变成https
    public function updateAvatar2(){
        $wx_member = M('user')->field('id,avatar')->select();
        $count = 0;
        foreach($wx_member as $key=>$value){
            $update['avatar'] = str_replace('http','https',$value['avatar']);
            $res = M('user')->where('id='.$value['id'])->save($update);
            if($res)
                $count ++;
        }
        xformatOutPutJsonData('success', '', $count);
    }
    
    //将服务器的微信头像链接格式化
    public function updateAvatar3(){
        $wx_member = M('user')->field('id,openid,avatar')->select();
        $count = 0;
        foreach($wx_member as $key=>$value){
            $ext = explode('.',$value['avatar']);
            // xformatOutPutJsonData($value['avatar'], $ext, $ext[3]);
            $update['avatar'] = 'https://www.ten-mate.com/Public/Avatar/0_'.$value['openid'].'.'.$ext[3];
            $res = M('user')->where('id='.$value['id'])->save($update);
            if($res)
                $count ++;
        }
        xformatOutPutJsonData('success', '', $count);
    }
    //数据库结构变动之后要主动清除TP框架的缓存,清除服务器缓存之后重新生成usertoken
    public function updateUserToken(){
        $wx_member = M('user')->field('id')->select();
        $count = 0;
        foreach($wx_member as $key=>$value){
            $update['userToken'] = A('Home/Index')->getTokenCode($value['id'],"roomer");
            $res = M('user')->where('id='.$value['id'])->save($update);
            if($res)
                $count ++;
        }
        
        $wx_member = M('landlord')->field('id')->select();
        $count1 = 0;
        foreach($wx_member as $key=>$value){
            $update['userToken'] = A('Home/Index')->getTokenCode($value['id'],"landlord");
            $res = M('landlord')->where('id='.$value['id'])->save($update);
            if($res)
                $count1 ++;
        }
        xformatOutPutJsonData('success', $count1, $count);
    }

    //清除服务器中违法违规的图片和文字
    public function contentCheck(){
        $Data = I('get.');
        $wx_member = M('user')->field('id,name,avatar,personal')->where('id>='.$Data['start']." AND id<".($Data['start']+100))->select();
        // $wx_member = M('user')->field('id,name,avatar,personal')->select();
        // $wx_room = M('room')->field('id,xiaoqu,description,images')->select();
        $count = 0;
        foreach($wx_member as $key=>$value){
            $name = base64_decode($value['name']);
            $description = $value['personal'];
            $msgCheck = A('Home/Chat')->messageCheck($name);
            if($msgCheck['errcode']==87014){
                xformatOutPutJsonData('test', $value['id'].'昵称', $name);
            }
            $msgCheck = A('Home/Chat')->messageCheck($description);
            if($msgCheck['errcode']==87014){
                xformatOutPutJsonData('test', $value['id'].'个人介绍', $description);
            }
            if(strpos($value['avatar'],"0_") === 0)
                continue;
            $avatar = str_replace('https://www.ten-mate.com','.',$value['avatar']);
            xformatOutPutJsonData('test', $value['avatar'], $avatar);
            // // $avatar = file_get_contents($value['avatar']);
            // $imgCheck = A('Home/Chat')->mediaCheck($avatar);
            // $imgCheck = json_decode(stripslashes($imgCheck));
            // $imgCheck = json_decode(json_encode($imgCheck), true);
            // if($imgCheck['errcode']==87014){
            //     xformatOutPutJsonData('test', $value['avatar'], "有违法违规内容");
            //     $filename = "./Public/landlordRoomImg/landlord_TMP/".$openid. '.jpg';
            //     file_put_contents($filename, $avatar);
            // }
            // $ext = explode('.',$value['avatar']);
            // // xformatOutPutJsonData($value['avatar'], $ext, $ext[3]);
            // $update['avatar'] = 'https://www.ten-mate.com/Public/Avatar/0_'.$value['openid'].'.'.$ext[3];
            // $res = M('user')->where('id='.$value['id'])->save($update);
            // if($res)
            //     $count ++;
        }
        xformatOutPutJsonData('success', $Data['start']+100, $count);
    }
}