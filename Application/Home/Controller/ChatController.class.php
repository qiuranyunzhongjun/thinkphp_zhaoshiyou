<?php

namespace Home\Controller;

use Common\Controller\HomeBaseController;

/**
 * 首页Controller
 */

class ChatController extends HomeBaseController {

    /**
     *文本消息的数据持久化
     */
    public function save_message(){
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
        if($data['type']==3)
            $inserData = array(
                'fromid' => $uid,
                'toid' => $data['toid'],
                'type' => $data['type'],
                'content' => base64_encode($data['content']),
                'creat_time' => time(),
            );
        else
            $inserData = array(
                'fromid' => $uid,
                'toid' => $data['toid'],
                'type' => $data['type'],
                'content' => $data['content'],
                'creat_time' => time(),
            );
        $res = M('chat')->add($inserData);
        if ($res == FALSE) {
            xformatOutPutJsonData('fail', '', '网络错误3！');
        }else{
            $inserData['id'] = $res;
            xformatOutPutJsonData('success', $inserData, '');
        }
    }
    /**
     *删除某条id的消息
     */
    public function delete_message(){
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
        if(!empty($data['chatid'])){
            $res = M('chat')->where('id=' . $data['chatid'])->delete();
            xformatOutPutJsonData('success', $res, '');
        }else{
            xformatOutPutJsonData('fail', $data, '0');
        }
    }

    /**
     * 根据用户id返回用户昵称和聊天双方的头像信息
     */
    public function getNameAndHead(){
        $data = I('get.');
        $uid = $data['id'];
        $friendId = $data['friendId'];//聊天对方的的用户id
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if(intval($friendId)>0){
            $result = M('user')->field('name,avatar,phone')->where('id=' . $friendId)->find();
            if(!$result){
                xformatOutPutJsonData('error', 1, '');
            }
            $result['nickname'] = base64_decode($result['name']);
        }else{
            $result = M('landlord')->field('name,avatar,phone')->where('id=' . intval($friendId)*-1)->find();
            if(!$result){
                xformatOutPutJsonData('error', 1, '');
            }
            $result['nickname'] = $result['name'];
        }
        if(intval($uid)>0){
            $result2 = M('user')->field('avatar')->where('id=' . $uid)->find();
            if(!$result2){
                xformatOutPutJsonData('error', 2, '');
            }
        }else{
            $result2 = M('landlord')->field('avatar')->where('id=' . intval($uid)*-1)->find();
            if(!$result2){
                xformatOutPutJsonData('error', 2, '');
            }
        }
        $result['myAvator'] = $result2['avatar'];
        $result['shoucang'] = 0;
        $result['heimingdan'] = 0;   
        $result['exchangePhone'] = 0;
        $result['beilahei'] = 0;
        $result3 = M('shoucang')->field('shoucang,heimingdan')->where('uid=' . $uid)->find();
        if($result3){
            $myShoucang = explode(",",$result3['shoucang']);
            $myHeimingdan = explode(",",$result3['heimingdan']);
            $index = array_search($friendId,$myShoucang);
            if(!($index===FALSE))
                $result['shoucang'] = 1;
            $index = array_search($friendId,$myHeimingdan);
            if(!($index===FALSE))
                $result['heimingdan'] = 1;    
        }
        $map['type'] = 5;
        $map['content'] = 1;
        $exchangePhone = M('chat')->field('id')
                    ->where($map)
                    ->where("fromid=%d and toid=%d or fromid=%d and toid=%d",array($friendId,$uid,$uid,$friendId))
                    ->find();
        if($exchangePhone)
            $result['exchangePhone'] = 1; 
        //查询自己是否被拉黑    
        $heimingdan = M('shoucang')->field('heimingdan')->where('uid=' . $friendId)->find();
        if($heimingdan){
            $index = array_search($uid,explode(",",$heimingdan['heimingdan']));
            if(!($index===FALSE))
                $result['beilahei'] = 1;
        }
        xformatOutPutJsonData('success', $result, '');
    }

    /**
     * 加载聊天记录
     */
    public function loadChat(){
        $data = I('get.');
        $uid = $data['id'];
        $friendId = $data['friendId'];//聊天对方的的用户id
        $chatId = $data['chatId'];//聊天记录的id小于$chatId之前的10条记录，最开始查询时是-1表示没有限制
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if($chatId!=-1)
            $map['id'] = array('LT',$chatId);
        $chatRecord = M('chat')->field('id,fromid,type,content,isread,creat_time')
                    ->where($map)
                    ->where("fromid=%d and toid=%d or fromid=%d and toid=%d",array($friendId,$uid,$uid,$friendId))
                    ->order('id DESC')->limit(10)->select();
        foreach($chatRecord as $k => $v) {
            if($v['type']==3){
                $chatRecord[$k]['content'] = base64_decode($v['content']);
            }
        }
        xformatOutPutJsonData('success', $chatRecord, "");
    }

    /**
     * 根据friendid返回用户消息列表中应该显示的信息
     */
    public function get_message_one(){
        $data = I('get.');
        $uid = $data['id'];
        $friendid = $data['friendId'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if(intval($friendid)>0){
            $friendInfo = M('user')->field('name,avatar')->where('id=' . $friendid)->find();
            if($friendInfo){
                $value['head_url'] = $friendInfo['avatar'];
                $value['username'] = base64_decode($friendInfo['name']);
                xformatOutPutJsonData('success', $value, '');
            }else{
                xformatOutPutJsonData('error', 1, '');
            }
        }else{
            $friendInfo = M('landlord')->field('name,avatar')->where('id=' . intval($friendid)*-1)->find();
            if($friendInfo){
                $value['head_url'] = $friendInfo['avatar'];
                $value['username'] = $friendInfo['name'];
                xformatOutPutJsonData('success', $value, '');
            }else{
                xformatOutPutJsonData('error', 1, '');
            }
        }
    }

    /**
     * 根据fromid来获取当前用户聊天列表
     */
    public function get_message_list(){
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
        //获取消息列表需要展示的用户id
        $messagers = M('shoucang')->field('messager')->where('uid=' . $uid)->find();
        if($messagers){
            $messagerIds = explode(",",$messagers['messager']);
        //var_dump($messagerIds);
            if($messagerIds[0]=='')
                xformatOutPutJsonData('success', [], '');
            foreach (array_reverse($messagerIds)as $value) {
                if($value[0]=='*'){
                    break;
                }else{
                    $chatter['id'] = $value;
                    $key1=array_search("*".$chatter['id'] ,$messagerIds);
                    //xformatOutPutJsonData('test', "*".$chatter['id'], $key1);
                    if(!($key1===FALSE)){
                        $chatter['zhiding'] = 1;
                    }else{
                        $chatter['zhiding'] = 0;
                    }
                    $result[] = $chatter;
                }
            }
            if (!empty($result)) {
                foreach ($result as $key => $value) {
                        if(intval($value['id'])>0){
                            $friendInfo = M('user')->field('name,avatar')->where('id=' . $value['id'])->find();
                            if($friendInfo){
                                $value['head_url'] = $friendInfo['avatar'];
                                $value['username'] = base64_decode($friendInfo['name']);
                                $map['fromid'] = $value['id'];
                                $map['toid'] = $uid;
                                $map['isread'] = 0;
                                $value['countNoread'] = M('chat')->where("fromid=%d and toid=%d and isread =0",array($value['id'],$uid))->count('id');
                                $value['last_message'] = M('chat')->where("fromid=%d and toid=%d or fromid=%d and toid=%d",array($value['id'],$uid,$uid,$value['id']))->order('id DESC')->limit(1)->find();
                                if($value['last_message']['type']==3){
                                    $value['last_message']['content'] = base64_decode($value['last_message']['content']);
                                }
                                $finalResult[] = $value;
                            }
                        }else{
                            $fid = intval($value['id'])*-1;
                            $friendInfo = M('landlord')->field('name,avatar')->where('id=' . $fid)->find();
                            if($friendInfo){
                                $value['head_url'] = $friendInfo['avatar'];
                                $value['username'] = $friendInfo['name'];
                                $map['fromid'] = $value['id'];
                                $map['toid'] = $uid;
                                $map['isread'] = 0;
                                $value['countNoread'] = M('chat')->where("fromid=%d and toid=%d and isread =0",array($value['id'],$uid))->count('id');
                                $value['last_message'] = M('chat')->where("fromid=%d and toid=%d or fromid=%d and toid=%d",array($value['id'],$uid,$uid,$value['id']))->order('id DESC')->limit(1)->find();
                                if($value['last_message']['type']==3){
                                    $value['last_message']['content'] = base64_decode($value['last_message']['content']);
                                }
                                $finalResult[] = $value;
                        }
                    }
                    // else{
                    //     xformatOutPutJsonData('error', 1, $value['id']);
                    // }
                }
                xformatOutPutJsonData('success', $finalResult, '1');
            } else {
                xformatOutPutJsonData('success', [], '0');
            }
        }else{
            xformatOutPutJsonData('success', [], '0');
        }
    }

    /**
     * 根据fromid来获取当前用户未读消息总数
     */
    public function get_message_count(){
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

        //获取发给当前id的未读消息数量
        $messagers = M('chat')->field('id')->where('isread=0 AND toid=' . $uid)->count();
        // xformatOutPutJsonData('success', $messagers, M()->getLastSql());
        xformatOutPutJsonData('success', $messagers, '');
        
    }

    /**
     * 根据fromid和toid更改消息为已读
     */

    public function changeNoRead(){
        $data = I('get.');
        $uid = $data['id'];
        $friendId = $data['friendId'];//聊天对方的的用户id
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $map['fromid'] = $friendId;
        $map['toid'] = $uid;
        $map['isread'] = 0;
        $chatRecord = M('chat')->where($map)->setField('isread',1);
        xformatOutPutJsonData('success', $chatRecord, "");
    }
    
    //修改消息列表
    //sub *12相当于取消12号的置顶sub 12相当于删除12号
    public function updateMessagers() {
        $data = I('get.');
        $uid = $data['id'];
        $method = $data['method'];//增加或者减少
        $friend = $data['friendId'];//要更改的用户id,如果删除的不带*的id,带*的也会同时删除
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $mingdan = M('shoucang')->field('messager')->where('uid=' . $uid)->find();
        //var_dump(M()->getLastSql());
        $res = false;
        if($mingdan){
            $ids = explode(",",$mingdan['messager']);
            $index = array_search($friend,$ids);
            //var_dump($index);
            //var_dump($index===FALSE);
            if($method=='add'){
                if($index===FALSE){
                    if($ids[0] == "")
                        $ids[0] = $friend;
                    else 
                        array_splice($ids,A('Home/Friend')->binaryFindPosition($ids,$friend),0,$friend);
                    $updateData['messager'] = implode(",",$ids);
                    $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
                }
            }else if($method=='sub'){
                if(!($index===FALSE)){
                    array_splice($ids,$index,1);
                    if($friend[0]!='*'){
                        $index = array_search("*" . $friend,$ids);
                        if(!($index===FALSE))
                            array_splice($ids,$index,1);
                    }
                    $updateData['messager'] = implode(",",$ids);
                    $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
                    if($res){
                        //还要把删除id发给我的所有消息设置为已读
                        $map['fromid'] = $friend;
                        $map['toid'] = $uid;
                        $map['isread'] = 0;
                        $chatRecord = M('chat')->where($map)->setField('isread',1);
                        xformatOutPutJsonData('success', $chatRecord, "");
                    }
                }
            }
        }else{
            if($method=='add'){
                $inserData = array(
                    'uid' => $uid,
                    'messager' => $friend,
                );
                $res = M('shoucang')->add($inserData);
            }
        }
        if ($res == FALSE) {
            xformatOutPutJsonData('fail', '', '网络错误3！');
        }
    }    
    //修改消息列表
    //在uid给friedid 发了消息之后
    public function updateMessager() {
        $data = I('get.');
        $uid = $data['id'];
        $friend = $data['friendId'];//要更改的用户id,如果删除的不带*的id,带*的也会同时删除
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($data['token'] == S('user_' . $data['id'])){

        }else if($data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $mingdan = M('shoucang')->field('messager')->where('uid=' . $friend)->find();
        //var_dump(M()->getLastSql());
        if($mingdan){
            $ids = explode(",",$mingdan['messager']);
            $index = array_search($uid,$ids);
            var_dump($index);
            var_dump($index===FALSE);
            if($index===FALSE){
                if($ids[0] == "")
                    $ids[0] = $uid;
                else 
                    array_splice($ids,A('Home/Friend')->binaryFindPosition($ids,$uid),0,$uid);
                $updateData['messager'] = implode(",",$ids);
                $res = M('shoucang')->where('uid=' . $friend)->save($updateData);
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', '', '网络错误3！');
                }
            }
        }else{
            $inserData = array(
                'uid' => $friend,
                'messager' => $uid,
            );
            $res = M('shoucang')->add($inserData);
            if ($res == FALSE) {
                xformatOutPutJsonData('fail', '', '网络错误3！');
            }
        }
        //给别人发过消息之后他应该也要出现在我的消息列表中
        $mingdan = M('shoucang')->field('messager')->where('uid=' . $uid)->find();
        //var_dump(M()->getLastSql());
        if($mingdan){
            $ids = explode(",",$mingdan['messager']);
            $index = array_search($friend,$ids);
            if($index===FALSE){
                if($ids[0] == "")
                    $ids[0] = $friend;
                else 
                    array_splice($ids,A('Home/Friend')->binaryFindPosition($ids,$friend),0,$friend);
                $updateData['messager'] = implode(",",$ids);
                $res = M('shoucang')->where('uid=' . $uid)->save($updateData);
                if ($res == FALSE) {
                    xformatOutPutJsonData('fail', '', '网络错误3！');
                }
            }
            //var_dump(M()->getLastSql());
        }else{
            $inserData = array(
                'uid' => $uid,
                'messager' => $friend,
            );
            $res = M('shoucang')->add($inserData);
            if ($res == FALSE) {
                xformatOutPutJsonData('fail', '', '网络错误3！');
            }
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
    
    /**
	 * 发送post请求,检查图片是否合法
	 * @param string $_FILES
	 * @return 
	 */
	function mediaCheck($FILES)
	{
		$post_data=array('media'=>curl_file_create($FILES['file']['tmp_name'], $FILES['file']['type'], $FILES['file']['name']));
        $access_token = $this->getXcxAccessToken();
        //dump($access_token);
        $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token={$access_token}";
        $ch = curl_init();   //1.初始化
        curl_setopt($ch, CURLOPT_URL, $url); //2.请求地址
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);//6.执行
        curl_close($ch);//8.关闭
		return $tmpInfo;
    }
    
    
    /**
	 * 发送post请求,检查文字是否合法
	 * @param string $checkContent
	 * @return 
	 */
	function messageCheck($checkContent)
	{
        $access_token = $this->getXcxAccessToken();
        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='. $access_token;
        $data = json_encode(array('content'=>$checkContent),JSON_UNESCAPED_UNICODE);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL,$url); // url
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // json数据
        $res = curl_exec($ch); // 返回值
        curl_close($ch);
        $result = json_decode($res,true);

        return $result;
    }

    /**
     * 上传图片，返回图片地址
     */
    public function uploadimg(){
        $Data = I('post.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] == S('user_' . $Data['id'])){

        }else if($Data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //检查图片是否合法
        $imgCheck = $this->mediaCheck($_FILES);
        $imgCheck = json_decode(stripslashes($imgCheck));
        $imgCheck = json_decode(json_encode($imgCheck), true);
        if($imgCheck['errcode']==0){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     5 * 1024 * 1024 ;// 设置附件上传大小：5M 以内
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =      './Public/Upload/image/'; // 设置附件上传根目录
            $upload->autoSub  = true;
            $upload->subName  = array('date','Ymd');
            // 上传单个文件 
            $info   =   $upload->uploadOne($_FILES['file']);
            if(!$info) {// 上传错误提示错误信息
                xformatOutPutJsonData('fail', $upload->getError(), "");
            }else{// 上传成功 获取上传文件信息
                xformatOutPutJsonData('success', str_replace('http','https',IMG_PATH). '/Public/Upload/image/' . $info['savepath'].$info['savename'], "");
            }
        }else if($imgCheck['errcode']==87014){
            xformatOutPutJsonData('fail', 1, "有违法违规内容");
        }else{
            xformatOutPutJsonData('fail', 1, "调用出现错误");
        }
    }

    //保存上传的formids
    public function saveFormIds(){
        $Data = I('post.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] == S('user_' . $Data['id'])){

        }else if($Data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $formids = str_replace("&quot;",'"',$Data['formids']);
        $result1 = M('formids')->field('formids')->where('uid=' . $uid)->find();
        if($result1){
            $updateData['formids'] = json_encode(array_merge(json_decode($result1['formids'],true),json_decode($formids,true)));
            $res = M('formids')->where('uid=' . $uid)->save($updateData);
        }else{
            $inserData = array(
                'uid' => $uid,
                'formids' => $formids
            );
            $res = M('formids')->add($inserData);
        }
        xformatOutPutJsonData('success', $res, "M()->getLastSql()");
    }

    
    //保存邀请人信息,可能会取消这种方法
    public function saveInviteId(){
        $Data = I('get.');
        $uid = $Data['id'];
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] == S('user_' . $Data['id'])){

        }else if($Data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $inviteId = $Data['inviteId'];
        $inviteType = $Data['inviteType'];
        $result1 = M('inviteids')->field('inviteids')->where('uid=' . $inviteId)->find();
        if($result1){
            $inviteIds = json_decode($result1['inviteids'],true);
            $existIds = explode(",",$inviteIds[$inviteType]);
            // xformatOutPutJsonData('test', $existIds, $inviteIds[$inviteType]);
            if($existIds[0]=='')
                $existIds[0] = $uid;
            else 
                array_splice($existIds,A('Home/Friend')->binaryFindPosition($existIds,$uid),0,$uid);
            $inviteIds[$inviteType] = implode(",",$existIds);
            $updateData['inviteids'] = json_encode($inviteIds,true);
            $res = M('inviteids')->where('uid=' . $inviteId)->save($updateData);
            // xformatOutPutJsonData('test', $existIds, $updateData);
            //var_dump(M()->getLastSql());
            if ($res == FALSE) {
                xformatOutPutJsonData('fail', '', '网络错误3！');
            }
        }else{
            $inviteIds = array(
                $inviteType => $uid
            );
            $inserData = array(
                'uid' => $inviteId,
                'inviteids' => json_encode($inviteIds)
            );
            $res = M('inviteids')->add($inserData);
        }
        xformatOutPutJsonData('success', $res," M()->getLastSql()");
    }

     /*
     *小程序模板消息
     *@param uid 用户id
     *$param template_id 模板id
     *@param form_id 表单提交场景下formId(只能用一次)
     *@param emphasis_keyword 消息加密密钥
    */
    public function sendTemplateMessage(){
        $Data = I('post.');
        $uid = $Data['uid'];
        // 检验uid合法性 防止非法越界
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] == S('user_' . $Data['id'])){

        }else if($Data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        $nickname = $Data['nickname'];  // 用户昵称
        $friend_id = $Data['friend_id'];
        $result = M('user')->field('openid')->where('id=' . $friend_id)->find();
        if($result){
            $openid = $result['openid'];
            $result2 = M('formids')->field('formids')->where('uid=' . $friend_id)->find();
            if($result2){
                $formids = json_decode($result2['formids'],true);
                $useIndex = -1;
                if(count($formids)==0){
                    xformatOutPutJsonData('fail', $res, 'no formIds');
                }else{
                    foreach ($formids as $key => $value) {
                        if($value['expire']>(time() * 1000 +60000)){
                            $useIndex = $key;
                            break;
                        }
                        //等新版小程序上线之后把这段注释改掉
                        // if($value['expire'] - 1000 * 60 * 60 * 7 + 1000 * 60 * 60 * 24 * 7 >(time() * 1000 +60000)){
                        //     $useIndex = $key;
                        //     break;
                        // }
                    }
                    if($useIndex>-1){
                        $form_id = $formids[$useIndex]['formId'];
                        $temp_msg = array(
                            'touser' => "{$openid}",
                            'template_id' => "LZoNc_IifXy2q3WhH-iGiEoFzJ34ODNiFacXv_cGxmA",
                            'page' => "/pages/index/index",
                            'form_id' => "{$form_id}",
                            'data' => array(
                                'keyword1' => array(
                                    'value' => "{$nickname}",
                                ),
                                'keyword2' => array(
                                    'value' => $Data['content'],
                                ),
                                'keyword3' => array(
                                    'value' => date('Y-m-d H:i:s', time()),
                                ),
                                'keyword4' => array(
                                    'value' => "有人在小程序里给你发消息啦，快去看看吧！",
                                ),
                            ),
                            //'emphasis_keyword'=> "keyword1.DATA"
                        );
                        //xformatOutPutJsonData('test', $temp_msg, $form_id);
                        $resOfSend = $this->sendXcxTemplateMsg(json_encode($temp_msg));
                        //dump($resofSend);
                        $updateData['formids'] = json_encode(array_slice($formids,$useIndex+1));
                        $res = M('formids')->where('uid=' . $friend_id)->save($updateData);
                        xformatOutPutJsonData('success', $res, $resOfSend);
                    }else{
                        $updateData['formids'] = json_encode([]);
                        $res = M('formids')->where('uid=' . $friend_id)->save($updateData);
                        xformatOutPutJsonData('fail', $res, 'out of time');
                    }
                }
            }
        }
    }

    
    /**
     * 发送小程序模板消息
     * @param $data
     * @return array
    */
   function sendXcxTemplateMsg($data)
    {
        // 具体模板格式参考公众平台申请的template_id
        // if (!$appid || !$appsecret)
        // {
        //     $appid        = 'wx8f2ed65c8aee3563';    //小程序id
        //     $appsecret    = '4f2e7944beac651f1b5d49dd53e36328';    //小程序秘钥 
        // }
        $access_token = $this->getXcxAccessToken();
        //dump($access_token);
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$access_token}";
        return $this->request_post($url, $data);
    }


    /*
     *小程序订阅消息
     *@param uid 用户id
     *$param template_id 模板id
     *@param $friend_id 要发送的对象id
     *@param emphasis_keyword 消息加密密钥
    */
    public function sendDingMessage(){
        $Data = I('post.');
        $uid = $Data['uid'];
        $nickname = $Data['nickname'];  // 用户昵称
        $friend_id = $Data['friend_id'];
        // 检验uid合法性 防止非法越界
        if ($Data['token'] == S('user_' . $uid)){

        }else if($Data['token'] == S('user_landlord' . $uid)) {
            $uid = '-'.$uid;
        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        //先查看本次发送的机会是否已经用了，每天只提醒三次
        $fareRes = M('shoucang')->field('fare')->where('uid=' . $friend_id)->find();
        if($fareRes){
            $fareRes = json_decode($fareRes['fare'],true);
            if($fareRes['lastDingDate']){
                $SendDate = explode("@",$fareRes['lastDingDate']);
                $time = time();
                if($SendDate[0] ==date("Y-m-d",$time)){
                    if(date("H",$time)<12&&$SendDate[1]=="moring"||date("H",$time)<18&&$SendDate[1]=="afternoon"||date("H",$time)<24&&$SendDate[1]=="evening"){
                        xformatOutPutJsonData('success', $fareRes['lastDingDate'], "本时间段已经发送过订阅消息");
                    }
                }
                $fareRes['lastDingDate']=date("Y-m-d",$time) .'@'. (date("H",$time)<12?"moring":date("H",$time)<18?"afternoon":"evening");
                $updateData = array(
                    'fare' => json_encode($fareRes),
                );
                $res = M('shoucang')->where('uid='.$friend_id)->save($updateData);
                $state = "之前存在这个人，并且发送过订阅消息";
            }else{
                $time = time();
                $fareRes['lastDingDate']=date("Y-m-d",$time) .'@'. (date("H",$time)<12?"moring":date("H",$time)<18?"afternoon":"evening");
                $updateData = array(
                    'fare' => json_encode($fareRes),
                );
                $res = M('shoucang')->where('uid='.$friend_id)->save($updateData);
                $state = "之前存在这个人，但是没有发送过订阅消息";
            }
        }else{
            $time = time();
            $sendDingState = date("Y-m-d",$time) .'@'. (date("H",$time)<12?"moring":date("H",$time)<18?"afternoon":"evening");
            $fare = array(
                'lastDingDate' => $fareState
            );
            $insertData = array(
                'uid' => $friend_id,
                'fare' => json_encode($fare),
            );
            $res = M('shoucang')->add($insertData);
            $state = "之前不存在这个人";
        }
        
        if($res){
            //更新发送状态成功，准备发送订阅消息
            if (intval($friend_id)>0){
                $result = M('user')->field('openid')->where('id=' . $friend_id)->find();
                $type = "roomer";
            }else if(intval($friend_id)<0) {
                $result = M('landlord')->field('openid')->where('id=' . intval($friend_id)*(-1))->find();
                $type = "landlord";
            }else{
                xformatOutPutJsonData('fail', '', '网络错误3！');
            }
            if($result){
                $openid = $result['openid'];
                $temp_msg = array(
                    'touser' => "{$openid}",
                    'template_id' => "qswGLcsSElTBsoQf4fzanfqFpV4esNcAbjdxeSwansQ",
                    'page' => "/pages/index/index?type=".$type,
                    'form_id' => "{$form_id}",
                    'data' => array(
                        'name1' => array(
                            'value' => "{$nickname}",
                        ),
                        'date2' => array(
                            'value' => date('Y-m-d H:i:s', time()),
                        ),
                        'thing3' => array(
                            'value' => $Data['content'],
                        ),
                    ),
                    'miniprogram_state' => "formal",//跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
                    'lang' => "zh_CN",//进入小程序查看”的语言类型，支持zh_CN(简体中文)、en_US(英文)、zh_HK(繁体中文)、zh_TW(繁体中文)，默认为zh_CN
                );
                //xformatOutPutJsonData('test', $temp_msg, $form_id);
                $resOfSend = $this->sendDingMsg(json_encode($temp_msg));
                //dump($resofSend);
                xformatOutPutJsonData('success', '', $resOfSend);
            }
        }else{
            xformatOutPutJsonData('fail', $res, $state);
        }
    }

    /**
     * 发送小程序订阅消息
     * @param $data
     * @return array
    */
   function sendDingMsg($data)
   {
       // 具体模板格式参考公众平台申请的template_id
       // if (!$appid || !$appsecret)
       // {
       //     $appid        = 'wx8f2ed65c8aee3563';    //小程序id
       //     $appsecret    = '4f2e7944beac651f1b5d49dd53e36328';    //小程序秘钥 
       // }
       $access_token = $this->getXcxAccessToken();
       //dump($access_token);
       $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$access_token}";
       return $this->request_post($url, $data);
   }

    /**
     * 获取微信接口调用凭证
     * @param string $appid
     * @param string $appsecret
     * @return mixed
    */
    function getXcxAccessToken($appid = 'wx8f2ed65c8aee3563', $appsecret = '4f2e7944beac651f1b5d49dd53e36328')
    {
        // if (!$appid || !$appsecret)
        // {
        //     $appid        = 'wx8f2ed65c8aee3563';    //小程序id
        //     $appsecret    = '4f2e7944beac651f1b5d49dd53e36328';    //小程序秘钥 
        // }
 
        // 缓存获取
        if (S($appid)) {
			$access_token = S($appid);
            // dump($access_token);
            // S($appid,null);
		} else {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
            // $result = file_get_contents($url);
            // $result_arr = json_decode($result, TRUE);
            // $access_token = $result_arr['access_token'];
            $token = $this->request_get($url);
			$token = json_decode(stripslashes($token));
            $arr = json_decode(json_encode($token), true);
            //dump($arr);
            $access_token = $arr['access_token'];
            $expire = $arr['expires_in']-200;
            if($expire>0){
                S($appid, $access_token, $expire);
            }else{
                S($appid,null);
            }
            //dump("产生新的access_token");
        }
        return $access_token;
    }

    /**
	 * 发送post请求
	 * @param string $url
	 * @param string $param
	 * @return bool|mixed
	 */
	function request_post($url = '', $param = '')
	{
		if (empty($url) || empty($param)) {
			return false;
		}
		$postUrl = $url;
		$curlPost = $param;
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
		// curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
		curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$data = curl_exec($ch); //运行curl
		curl_close($ch);
		return $data;
	}
	/**
	 * 发送get请求
	 * @param string $url
	 * @return bool|mixed
	 */
	function request_get($url = '')
	{
		if (empty($url)) {
			return false;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 500);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

    public function getMiniCodeImage(){
        $Data = I('get.');
        $uid = $Data['id'];
        // 检验uid合法性 防止非法越界
        if (empty($uid)) {
            xformatOutPutJsonData('fail', '', '网络错误1！');
        }
        if ($Data['token'] == S('user_' . $Data['id'])){

        }else{
            xformatOutPutJsonData('fail', '', '网络错误2！');
        }
        //后期可以用,扩展
        if($this->get_mini_code($data=$uid,$page='pages/shoucangxiangqing/shoucangxiangqing',$width=280)>2000){
            xformatOutPutJsonData('success', '', '');
        }else{
            xformatOutPutJsonData('fail', '', '');
        }
    }

     /*
     *调用接口B获取小程序码     
     * @param array $data 需要传递的参数,微信限制(最大32个可见字符,键名+键值+连接符['='|'&']<=32)
     * @param string $page 已经发布的小程序存在的页面，根路径前不要填加'/'，不能携带参数（参数请放在scene字段里）
     * @param number $width 二维码的宽度 
     * @param boolean $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
     * @param boolean $is_hyaline 是否需要透明底色， is_hyaline 为true时，生成透明底色的小程序码
     * @return boolean|mixed
    */
    public function get_mini_code($data,$page='pages/shoucangxiangqing/shoucangxiangqing',$width=430,$auto_color=false,$line_color=array('r'=>'0','g'=>'0','b'=>'0'),$is_hyaline=true){
        
        // $appid        = 'wx8f2ed65c8aee3563';    //小程序id
        // $appsecret    = '4f2e7944beac651f1b5d49dd53e36328';    //小程序秘钥 
        $access_token = $this->getXcxAccessToken();
        //dump($access_token);
        $wx_data = array(
            'scene'=>$data,
            'page'=>$page,
            'width'=>$width,
            'auto_color'=>$auto_color,
            'line_color'=>$line_color,
            'is_hyaline'=>$is_hyaline,
        );
        $wx_data = json_encode($wx_data);
        //dump($wx_data);
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
        $file = $this->request_post($url, $wx_data);
        //以下三句话可以让生成的二维码图片显示在网页
        // header('Content-Type: image/png');
        //echo $file; 
        // die;
        // dump($file);
        //$filename = IMG_PATH. '/Public/MiniCode/' .$data. '.png';
        $filename = './Public/MiniCode/' .$data. '.png';
        //dump($filename);
        
        $im = file_put_contents($filename, $file);
        if($im<=2000){
            unlink($filename);
        }
        //dump($im);
        return $im;
    }

}