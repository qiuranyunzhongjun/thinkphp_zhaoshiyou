<?php

namespace Home\Controller;

use Common\Controller\HomeBaseController;

/**
 * 首页Controller
 */
class FriendController extends HomeBaseController {
    /*
     * 登录
     */
    //更新shoucang表的用户信息
    public function updateFriends() {
        $data = I('get.');
        $uid = $data['id'];
        $method = $data['method'];//增加或者减少
        $type = $data['type'];//黑名单或者收藏
        $friend = $data['friendId'];//要更改的用户id
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($type=='invite' && $data['token'] !== S('user_' . $data['friendId'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        // if ($type!='invite' && $data['token'] !== S('user_' . $data['id'])) {
        //     xformatOutPutJsonData('fail', '', '网络错误2！');
        // }
        if($type!='invite'){
            if ($data['token'] == S('user_' . $data['id'])){

            }else if($Data['token'] !== S('user_landlord' . $uid)) {
                $uid = '-'.$uid;
            }else{
                xformatOutPutJsonData('fail', '', '网络错误2！');
            }
        }
        $mingdan = M('shoucang')->field($type)->where('uid=' . $uid)->find();
        if($mingdan){
            $ids = explode(",",$mingdan[$type]);
            // xformatOutPutJsonData('test', '', $ids);
            //$index = A('Home/Friend')->binarySearch($ids,$friend);
            $index = array_search($friend,$ids);
            if(!($index===FALSE)){
                if($method=='sub'){
                    array_splice($ids,$index,1);
                }
            }else{
                if($method=='add'){
                    if(!$index){
                        if($ids[0] == "")
                            $ids[0] = $friend;
                        else 
                            array_splice($ids,A('Home/Friend')->binaryFindPosition($ids,$friend),0,$friend);
                    }
                }
            }
            $updateData[$type] = implode(",",$ids);
            $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
            //var_dump(M()->getLastSql());
            if ($res == FALSE) {
                xformatOutPutJsonData('fail', $index, '网络错误3！');
            }

        }else{
            if($method=='add'){
                $inserData = array(
                    'uid' => $uid,
                    $type => $friend,
                );
                $res = M('shoucang')->add($inserData);
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', '', '网络错误3！');
                }
            }
        }
        
    }

    //获取黑名单成员
    public function getHeimingdan() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $heimingdan = M('shoucang')->field('heimingdan')->where('uid=' . $uid)->find();
        if($heimingdan){
            //先获取租户
            $map['id']  = array('in',explode(",",$heimingdan['heimingdan']));
            $heimingdanUsers = M('user')->field('id,sex,avatar,name,shangquan,ditie,is_place,constellation,work,age')->where($map)->select();
            foreach ($heimingdanUsers as $k => $v) {
                if (empty($v['name'])) {
                    xformatOutPutJsonData('fail', '', 'name为空');
                } else {
                    $v['nickname'] = base64_decode($v['name']);
                }

                if (intval($v['is_place']) == 1) {
                    $xing = M('circles')->field('city_name,area_name,name')->where('id=' . $v['shangquan'])->find();
                    $v['zuzhupos'] = $xing['city_name'].$xing['area_name'] . $xing['name'];
                } else {
                    $xing = M('subways')->field('city,line,station')->where('id=' . $v['ditie'])->find();
                    $v['zuzhupos'] = $xing['city'].$xing['line'] . $xing['station'];
                }
                $result[] = $v;
            }
            //获取房东
            foreach(explode(",",$heimingdan['heimingdan']) as $lk => $landlordId){
                $landid = intval($landlordId);
                if($landid<0){
                    $landid = $landid * -1;
                    $landlord = M('landlord')->field('gender as sex,avatar,name as nickname,province,city,work')->where('id= '.$landid)->find();
                    $landlord['id'] = $landlordId;
                    $landlord['zuzhupos'] = $landlord['province'].$landlord['city'];
                    unset($landlord['province']);
                    unset($landlord['city']);
                    $landlord['constellation'] ='个人出租';
                    $result[] = $landlord;
                }
            }
            xformatOutPutJsonData('success', $result, '');
        }else{
            xformatOutPutJsonData('error', 1, '');
        }
    }

    
    //获取我的收藏名单
    public function getShoucang() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $shoucang = M('shoucang')->field('shoucang')->where('uid=' . $uid)->find();
        if($shoucang){
            $map['id']  = array('in',explode(",",$shoucang['shoucang']));
            $shoucangUsers = M('user')->field('id,sex,avatar,name,shangquan,ditie,is_place,constellation,work,age')->where($map)->select();
            foreach ($shoucangUsers as $k => $v) {
                if (empty($v['name'])) {
                    xformatOutPutJsonData('fail', '', 'name为空');
                } else {
                    $v['nickname'] = base64_decode($v['name']);
                }
                
                if (intval($v['is_place']) == 1) {
                    $xing = M('circles')->field('city_name,area_name,name')->where('id=' . $v['shangquan'])->find();
                    $v['zuzhupos'] = $xing['city_name'].$xing['area_name'] . $xing['name'];
                } else {
                    $xing = M('subways')->field('city,line,station')->where('id=' . $v['ditie'])->find();
                    $v['zuzhupos'] = $xing['city'].$xing['line'] . $xing['station'];
                }
                $result[] = $v;
            }
            //获取房东
            foreach(explode(",",$shoucang['shoucang']) as $lk => $landlordId){
                $landid = intval($landlordId);
                if($landid<0){
                    $landid = $landid * -1;
                    $landlord = M('landlord')->field('gender as sex,avatar,name as nickname,province,city,work')->where('id= '.$landid)->find();
                    $landlord['id'] = $landlordId;
                    $landlord['zuzhupos'] = $landlord['province'].$landlord['city'];
                    unset($landlord['province']);
                    unset($landlord['city']);
                    $landlord['constellation'] ='个人出租';
                    $result[] = $landlord;
                }
            }
            xformatOutPutJsonData('success', $result, '');
        }else{
            xformatOutPutJsonData('error', 1, '');
        }
    }
    
    
    //保存邀请人信息
    public function saveInviteId(){
        $data = I('get.');
        $uid = $data['id'];
        $method = $data['method'];//增加或者减少
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $inviteId = $data['inviteId'];
        $inviteType = $data['inviteType'];
        $mingdan = M('shoucang')->field('inviteids')->where('uid=' . $inviteId)->find();
        if($mingdan){
            if($mingdan['inviteids']){
                $invite = json_decode($mingdan['inviteids'],true);
                $ids = explode(",",$invite[$inviteType]);
                // xformatOutPutJsonData('test', $existIds, $inviteIds[$inviteType]);
                $index = array_search($uid,$ids);
                if(!($index===FALSE)){
                    if($method=='sub'){
                        array_splice($ids,$index,1);
                    }
                }else{
                    if($method=='add'){
                        if(!$index){
                            if($ids[0] == "")
                                $ids[0] = $uid;
                            else 
                                array_splice($ids,A('Home/Friend')->binaryFindPosition($ids,$uid),0,$uid);
                        }
                    }
                }
                $invite[$inviteType] = implode(",",$ids);
                $updateData['inviteids'] = json_encode($invite,true);
                $res = M('shoucang')->where('uid=' . $inviteId)->save($updateData);
                //var_dump(M()->getLastSql());
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', '', '网络错误3！');
                }
            }else{
                if($method=='add'){
                    $invite = array(
                        $inviteType => $uid
                    );
                    $updateData['inviteids'] = json_encode($invite,true);
                    $res = M('shoucang')->where('uid=' . $inviteId)->save($updateData);
                    // xformatOutPutJsonData('test', $existIds, $updateData);
                    //var_dump(M()->getLastSql());
                    if ($res == FALSE) {
                        xformatOutPutJsonData('fail', '', '网络错误3！');
                    }
                }
            }

        }else{
            if($method=='add'){
                $invite = array(
                    $inviteType => $uid
                );
                $inserData = array(
                    'uid' => $inviteId,
                    'inviteids' => json_encode($invite),
                );
                $res = M('shoucang')->add($inserData);
                if (!$res) {
                    xformatOutPutJsonData('fail', '', '网络错误3！');
                }
            }
        }
        xformatOutPutJsonData('success', $res, M()->getLastSql());
    }
    //更新houses表的房源信息
    public function updateRooms() {
        $data = I('get.');
        $uid = $data['id'];
        $method = $data['method'];//增加或者减少
        $type = $data['type'];//黑名单或者收藏
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if(strpos($data['friendId'], "danke") === 0){
            //从danke数据表中读取
            $brand = 'danke';//房源的品牌
            $room = str_replace("danke",'',$data['friendId']);//要更改的id
        }else if(strpos($data['friendId'], "ziruyu") === 0){
            //从ziruyu数据表中读取
            $brand = 'ziruyu';//房源的品牌
            $room = str_replace("ziruyu",'',$data['friendId']);//要更改的id
        }else if(strpos($data['friendId'], "woaiwojia") === 0){
            //从woaiwojia数据表中读取
            $brand = 'woaiwojia';//房源的品牌
            $room = str_replace("woaiwojia",'',$data['friendId']);//要更改的id
        }else if(strpos($data['friendId'], "landlord") === 0){
            //从房东上传的房源数据表中读取
            $brand = 'landlord';//房源的品牌
            $room = str_replace("landlord",'',$data['friendId']);//要更改的id
        }
        $mingdan = M('shoucang')->field($type)->where('uid=' . $uid)->find();
        // xformatOutPutJsonData('test', $mingdan, '网络错误1！');
        if($mingdan){
            if($mingdan[$type]){
                $houses = json_decode($mingdan[$type],true);
                $ids = explode(",",$houses[$brand]);
                // xformatOutPutJsonData('test', $existIds, $inviteIds[$inviteType]);
                $index = array_search($room,$ids);
                if(!($index===FALSE)){
                    if($method=='sub'){
                        array_splice($ids,$index,1);
                    }
                }else{
                    if($method=='add'){
                        if(!$index){
                            if($ids[0] == "")
                                $ids[0] = $room;
                            else 
                                array_splice($ids,A('Home/Friend')->binaryFindPosition($ids,$room),0,$room);
                        }
                    }
                }
                $houses[$brand] = implode(",",$ids);
                $updateData[$type] = json_encode($houses,true);
                $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
                //var_dump();
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', M()->getLastSql(), '网络错误3！');
                }
            }else{
                if($method=='add'){
                    $houses = array(
                        $brand => $room
                    );
                    $updateData[$type] = json_encode($houses,true);
                    // $updateData['likehouse'] = '12138';
                    $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
                    // xformatOutPutJsonData($res, M()->getLastSql(), $updateData);
                    // var_dump(M()->getLastSql());
                    if ($res == FALSE) {
                        xformatOutPutJsonData('fail', M()->getLastSql(), '网络错误3！');
                    }
                }
            }

        }else{
            if($method=='add'){
                $houses = array(
                    $brand => $room
                );
                $inserData = array(
                    'uid' => $uid,
                    $type => json_encode($houses),
                );
                $res = M('shoucang')->add($inserData);
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', M()->getLastSql(), '网络错误3！');
                }
            }
        }
        xformatOutPutJsonData('success', M()->getLastSql(), '操作成功');
    }
    
    //增加一次拨号记录
    public function addPhoneCount() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if(strpos($data['roomId'], "danke") === 0){
            $brand = 'danke';//房源的品牌
        }else if(strpos($data['roomId'], "ziruyu") === 0){
            //从ziruyu数据表中读取
            $brand = 'ziruyu';//房源的品牌
        }else if(strpos($data['roomId'], "woaiwojia") === 0){
            //从woaiwojia数据表中读取
            $brand = 'woaiwojia';//房源的品牌
        }
        $phonecount = M('shoucang')->field('phonecount')->where('uid=' . $uid)->find();
        // xformatOutPutJsonData('test', $phonecount, '网络错误1！');
        if($phonecount){
            if($phonecount['phonecount']){
                $houses = json_decode($phonecount['phonecount'],true);
                $count =bcadd($houses[$brand],1);
                $houses[$brand] = $count;
                $updateData['phonecount'] = json_encode($houses,true);
                $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
                //var_dump();
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', M()->getLastSql(), '网络错误3！');
                }
            }else{
                $houses = array(
                    $brand => 1
                );
                $updateData['phonecount'] = json_encode($houses,true);
                $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', M()->getLastSql(), '网络错误3！');
                }
            }

        }else{
            $houses = array(
                $brand => 1
            );
            $inserData = array(
                'uid' => $uid,
                $type => json_encode($houses),
            );
            $res = M('shoucang')->add($inserData);
            if ($res == FALSE) {
                xformatOutPutJsonData('fail', M()->getLastSql(), '网络错误3！');
            }
        }
        xformatOutPutJsonData('success', M()->getLastSql(), '操作成功');
    }
    //获取我的房源列表
    public function getHouseList() {
        $data = I('get.');
        $uid = $data['id'];
        $type = $data['type'];//黑名单或者收藏
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $houses = M('shoucang')->field($type)->where('uid=' . $uid)->find();
        if($houses){
            $houses = json_decode($houses[$type],true);
            $brand = array_keys($houses);
            foreach ($brand as $k => $v) {
                if($houses[$v]=='')
                    continue;
                $ids = explode(",",$houses[$v]);
                // xformatOutPutJsonData('test1', $v, $brand); 
                if($v=='danke'){//蛋壳公寓的房源
                    $map_danke['room_id']  = array('in',$ids);
                    $danke = M('danke')->field('id,room_title as title,feature,normal_price,promotion_price,room_id,room_size,room_type,room_floor,room_subway,roommate,room_image_link')->where($map_danke)->select();
                    foreach ($danke as $key => $value) {
                        $value['brand'] = '蛋壳';
                        $value['id'] = 'danke'.$value['id'];
                        if($value['promotion_price']!=0){
                            $value['price'] = $value['promotion_price'];
                        }else{
                            $value['price'] = $value['normal_price'];
                        }
                        unset($value['promotion_price']);
                        unset($value['normal_price']);
                        $value['size'] = $value['room_size'];
                        $value['type2'] = explode(",",$value['room_type'])[0];
                        $value['floor'] = $value['room_floor'].'层';
                        $value['transport'] = $value['room_subway'];
                        $typeInfo = explode('室',$value['type2']);
                        unset($value['info']);
                        unset($value['location']);
                        unset($value['room_size']);
                        unset($value['room_type']);
                        unset($value['room_floor']);
                        unset($value['room_subway']);
                        $images = json_decode($value['room_image_link'],true);
                        $value['image'] = $images[0];
                        unset($value['room_image_link']);
                        $value['labels'] = explode(",",$value['feature']);
                        unset($value['feature']);
                        $value['room_id'] = 'danke'.$value['room_id'];
                        $res[] = $value;
                    }
                }else if($v=='woaiwojia'){//我爱我家的房源
                    $map_woaiwojia['room_id']  = array('in',$ids);
                    $woaiwojia = M('woaiwojia')->field('*')->where($map_woaiwojia)->select();
                    // xformatOutPutJsonData('test1', $woaiwojia, M()->getLastSql());
                    foreach ($woaiwojia as $key => $value) {
                        $value['brand'] = '我爱我家';
                        $value['id'] = 'woaiwojia'.$value['id'];
                        $value['title'] = $value['room_title'];
                        unset($value['room_title']);
                        $value['price'] = $value['normal_price'];
                        unset($value['normal_price']);
                        $value['size'] = $value['room_size'];
                        $value['type2'] = $value['room_type'];
                        $value['floor'] = $value['room_floor'];
                        if($value['room_subway']!='')
                            $value['transport'] = $value['room_subway'];
                        else $value['transport'] = $value['room_business'] ."附近" .$value['room_location'];
                        if($value['room_image_link']!='[]'){
                            $images = json_decode($value['room_image_link'],true);
                            $value['image'] = $images[0];
                        }else
                            $value['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
                        $value['labels'] = explode(",",$value['feature']);
                        if(count($value['labels'])<4)
                            $value['labels'][] = $value['room_decoration'];
                        if(count($value['labels'])<4 && $value['room_building_type']!='')
                            $value['labels'][] = $value['room_building_type'];
                        unset($value['feature']);
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
                        unset($value['room_image_link']);
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
                }else if($v=='ziruyu'){
                    //自如寓的房源展示
                    $map_ziruyu['room_id']  = array('in',$ids);
                    $ziruyus = M('ziruyu')->field('id,title,location,price,info,subpage_image_dir')->where($map_ziruyu)->select();
                    foreach ($ziruyus as $key => $value) {
                        $value['id'] = 'ziruyu'.$value['id'];
                        $roomproperties = explode(",",$value['info']);
                        $size = str_replace("约","",str_replace('平米','',$roomproperties[2]));
                        // $fraction += floatval($size);
                        $value['size'] = $size;
                        $value['type2'] = $roomproperties[0];
                        $value['floor'] = $roomproperties[1];
                        $value['transport'] = $value['location'];
                        unset($value['info']);
                        unset($value['location']);
                        $images = json_decode($value['subpage_image_dir'],true);
                        // $value['timg'] = $images;
                        if($images['卧室']){
                            $value['image'] = $images['卧室'][0];
                        }else if($images['睡眠区']){
                            $value['image'] = $images['睡眠区'][0];
                        }else if($images['客厅']){
                            $value['image'] = $images['客厅'][0];
                        }
                        // $value['image'] = explode(",",$value['subpage_image_dir'])[0];
                        unset($value['subpage_image_dir']);
                        $value['labels'][] = '限时特惠';
                        $res[] = $value;
                    }
                }else if($v=='landlord'){
                    //房东上传的房源展示
                    $map_landlord['id']  = array('in',$ids);
                    $landlordroom = M('landlord_room')->field('id,xiaoqu as title,yajinfangshi,price,huxing as type2,size, visit_time,floor_type,floor_count as floor,subway,type,images')->where($map_landlord)->select();
                    // xformatOutPutJsonData('test2', $map, $landlordroom); 
                    foreach ($landlordroom as $key => $value) {
                        $value['id'] = 'landlord'.$value['id'];
                        $value['room_id'] = $value['id'];
                        if($value['type']==1){
                            $value['brand'] ='房东';
                        }else if($value['type']==2){
                            $value['brand'] ='转租';
                        }else{
                            $value['brand'] ='中介';
                        }
                        unset($value['type']);
                        $value['floor'] = $value['floor'].'层';
                        $transport = M('subways')->field('line,station')->where('id='.$value['subway'])->find();
                        if($transport){
                            $value['transport'] = $transport['line'].$transport['station'].'附近';
                        }else{
                            $value['transport'] = '房东未按规定上传附近交通信息';
                        }
                        unset($value['subway']);
                        if($value['images']!=''){
                            $images = explode(";",$value['images']);
                            $value['image'] = $images[0];
                        }else
                            $value['image'] = str_replace('http','https',IMG_PATH). '/Public/image/houseDetail404.jpg';
                        unset($value['images']);
                        $value['labels'][] = $value['yajinfangshi'];
                        unset($value['yajinfangshi']);
                        if($value['visit_time']==1){
                            $value['labels'][] = '随时看房';
                        }else if($value['visit_time']==2){
                            $value['labels'][] = '周末看房';
                        }else{
                            $value['labels'][] = '周中';
                        }
                        unset($value['visit_time']);
                        if($value['floor_type']==1){
                            $value['labels'][] = '电梯房';
                        }else{
                            $value['labels'][] = '楼梯房';
                        }
                        unset($value['floor_type']);
                        $res[] = $value;
                    }
                }
            }
            xformatOutPutJsonData('success', $res, '');
        }else{
            xformatOutPutJsonData('error', 1, '');
        }
    }
    //给定一个数组和一个元素，返回该数组中第一个比目标元素大的元素的位置，如果都比目标元素小，返回最大元素下标加1
    public function binaryFindPosition($array,$target){
        if($array[0] == "")
            return 0;
        $left = 0;
        $right = sizeof($array)-1;
        while($left<=$right){
            if ($array[$right] < $target)
                return $right + 1;
            else if ($array[$left] > $target)
                return $left;
            $middle = (int)(($left + $right) / 2);
            if ($target > $array[$middle]) {
                $left = $middle + 1;
            }else{
                $right = $middle - 1;
            }
        }
    }

    //二分查找获得数组内某个元素的索引，不存在返回-1
    public function binarySearch($array,$target){
        if($array[0] == "")
            return -1;
        $left = 0;
        $right = sizeof($array)-1;
        while($left<=$right){
            $middle = (int)(($left + $right) / 2);
            if ($target == $array[$middle]) 
                return $middle;
            else if($target > $array[$middle]){
                $left = $middle+1;
            }else{
                $right = $middle-1;
            }
        }
        return -1;
    }

    /*
    * 这个应该只有租客可以预约吧
    */
    public function roomAppointment() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if($data['brand']=='danke'){
            $room = M('danke')->where('room_id="' . $data['roomid'] .'"')->find();
            if($room){
                // xformatOutPutJsonData('test', $room, $data);
                if($room['promotion_price']!=0){
                    $price = $room['promotion_price'];
                }else{
                    $price = $room['normal_price'];
                }
                $title = explode(" ",$room['room_title']);
                $user = M('user')->where('id=' . $uid)->find();
                if($user['sex']==1)
                    $sex = 1;
                else $sex = 0;
                $result = $this->dankeAppointment($data['name'],$sex,$data['phone'],$data['description'],$data['time'],$room['city'],$title[0],$title[1],intval($data['roomid']),$price);
            }else{
                xformatOutPutJsonData('fail', 1, '此房屋已经不在数据库');
            }
        }

        $appointment = M('appointment')->where('status=0 AND uid=' . $uid)->find();
            if ($appointment) {
                // $update['roomid'] = $data['roomid'];
                $update['name'] = $data['name'];
                // $update['brand'] = $data['brand'];
                $update['phone'] = $data['phone'];
                $update['time'] = $data['time'];
                $update['description'] = $data['description'];
                $update['update_time'] = date("Y-m-d H:i:s",time());
                $res = M('appointment')->where('status=0 AND uid='.$uid)->save($update);
                xformatOutPutJsonData('success', $res, $result);
            } else {
                $insert['uid'] = $data['id'];
                $insert['brand'] = $data['brand'];
                $insert['roomid'] = $data['roomid'];
                $insert['name'] = $data['name'];
                $insert['phone'] = $data['phone'];
                $insert['time'] = $data['time'];
                $insert['description'] = $data['description'];
                $insert['create_time'] = date("Y-m-d H:i:s",time());
                $insert['update_time'] = $insert['create_time'];
                $res = M('appointment')->add($insert);
                xformatOutPutJsonData('success', $res, $result);
            }

        xformatOutPutJsonData('fail', $res, '未知错误');
    }

    //这是可以运行的版本
    // public function DankeAppointment(){
    //     $timestamp = time();
    //     $source_id = '6217b4e16cdc';
    //     $token = '01172f91fe0aa3423d1169a071e4cc79';
    //     $sign = sha1($source_id.$timestamp.$token);
    //     // xformatOutPutJsonData('test',$timestamp, $sign);
    //     $room_data = array(
    //         source_id=>$source_id,
    //         sign=>$sign,
    //         timestamp=>strval($timestamp),
    //         type=>找室友预约,
    //         created_at=>date("Y-m-d H:i:s",$timestamp),
    //         name=>'测试数据,不用理会3407',
    //         gender=>1,
    //         mobile=>'13240303407',
    //         appointment_note=>'测试',//在备注这个字段里面加上城市、商圈、小区的名字、房间编号（65537-A）
    //         appointment_time=>date("Y-m-d H:i:s",$timestamp),
    //         target_city=>'北京市',
    //         target_block_name=>'欢乐谷景区',
    //         target_xiaoqu_name=>'垡头翠城馨园',
    //         target_room_code=>65537,
    //         price=>'2000-3000'
    //     );
    //     // $room_data = json_encode($room_data);
        
	// 	$postUrl = "https://www.danke.com/callback/pangu/external-passenger/single-add";
	// 	$curlPost =  $room_data;
	// 	$ch = curl_init(); //初始化curl
	// 	curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
	// 	curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	// 	curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
	// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	// 	$result = curl_exec($ch); //运行curl
	// 	curl_close($ch);
        
    //     // xformatOutPutJsonData('test','',$result );
    //     xformatOutPutJsonData('test',$room_data,$result );
    // }

    public function dankeAppointment($name,$gender,$phone,$note,$time,$city,$block,$xiaoqu,$room_id,$price){
        $timestamp = time();
        $source_id = '6217b4e16cdc';
        $token = '01172f91fe0aa3423d1169a071e4cc79';
        $sign = sha1($source_id.$timestamp.$token);
        // xformatOutPutJsonData('test',$timestamp, $sign);
        $room_data = array(
            source_id=>$source_id,
            sign=>$sign,
            timestamp=>strval($timestamp),
            type=>找室友预约,
            created_at=>date("Y-m-d H:i:s",$timestamp),
            name=>$name,
            gender=>$gender,
            mobile=>$phone,
            appointment_note=>$note,//在备注这个字段里面加上城市、商圈、小区的名字、房间编号（65537-A）
            appointment_time=>$time,
            target_city=>$city,
            target_block_name=>$block,
            target_xiaoqu_name=>$xiaoqu,
            target_room_code=>$room_id,
            price=>$price
        );
        // $room_data = json_encode($room_data);
        // xformatOutPutJsonData('test',$room_data,'');
		$postUrl = "https://www.danke.com/callback/pangu/external-passenger/single-add";
		$curlPost =  $room_data;
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$result = curl_exec($ch); //运行curl
		curl_close($ch);
        
        // xformatOutPutJsonData('test','',$result );
        // xformatOutPutJsonData('test1',$room_data,$result );
        return $result;
    }
    //更新福利状态，待完善
    public function updateFareState() {
        $data = I('get.');
        $uid = $data['id'];
        $name = $data['name'];//福利名字
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $fareRes = M('shoucang')->field('fare')->where('uid=' . $uid)->find();
        if($fareRes){
            $fareRes = json_decode($fareRes['fare'],true);
            // $brand = array_keys($fare);
            // foreach ($brand as $k => $v) {
            //     if($v==$name){
            //         xformatOutPutJsonData('success', $fare[$v], '');
            //     }
            // }
            if($fareRes[$name]){
                xformatOutPutJsonData('success', $fareRes[$name], '原来就有这个福利');
            }
            else {
                $fareState = array(
                    'state' => '1',
                    'time' => date("Y-m-d H:i:s",time())
                );
                $fareRes[$name]=$fareState;
                $updateData = array(
                    'fare' => json_encode($fareRes),
                );
                $res = M('shoucang')->where('uid='.$uid)->save($updateData);
                xformatOutPutJsonData('success', $res, '原来就有这个人');
            }
        }else{
            $fareState = array(
                'state' => '1',
                'time' => date("Y-m-d H:i:s",time())
            );
            $fare = array(
                $name => $fareState
            );
            $insertData = array(
                'uid' => $uid,
                'fare' => json_encode($fare),
            );
            $res = M('shoucang')->add($insertData);
            xformatOutPutJsonData('success', $res, '从无到有');
        }
    }
    //获取福利状态
    public function getFareState() {
        $data = I('get.');
        $uid = $data['id'];
        $name = $data['name'];//福利名字
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $fare = M('shoucang')->field('fare')->where('uid=' . $uid)->find();
        if($fare){
            $fare = json_decode($fare['fare'],true);
            // $brand = array_keys($fare);
            // foreach ($brand as $k => $v) {
            //     if($v==$name){
            //         xformatOutPutJsonData('success', $fare[$v], '');
            //     }
            // }
            if($fare[$name]){
                xformatOutPutJsonData('success', $fare[$name], '');
            }
            else xformatOutPutJsonData('error', 2, '');
        }else{
            xformatOutPutJsonData('error', 1, '');
        }
    }
    
    //获取某个人的自由房源编号
    public function getHouseByUser() {
        $data = I('get.');
        $uid = $data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] !== S('user_' . $data['id'])) {
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $house = M('room')->field('id')->where('master_id=' . $data['friendid'])->find();
        if($house){
            xformatOutPutJsonData('success', $house['id'], '');
        }else{
            xformatOutPutJsonData('error', 1, 'no room');
        }
    }
}
