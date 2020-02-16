<?php

namespace Admin\Controller;

use Common\Controller\AdminBaseController;

/**
 * 后台首页控制器
 */
class UserController extends AdminBaseController {

    /**
     * 用户列表
     */
    public function index() {


        $count = M('User')->count();
        $Page = new \Org\Nx\Page($count, 20);
        $show = $Page->show();
        $data = M('User')
                ->order('id DESC')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        
        foreach ($data as $k => $v) {
//            用户名（如果是base64加密则显示name(用户名)否则显示nickname（昵称））
            if($this->is_base64($v['name'])){
                $data[$k]['base64'] = base64_decode($v['name']);
            }else{
                $data[$k]['base64'] = $v['nickname'];
            }

//            租房时长
            $lid['id'] = $v['tenant_long'];
            $long = M('TenantLong')->where($lid)->select();
            $data[$k]['tenant_long'] = $long[0]['name'];
//            商圈
            $data[$k]['shangquan'] = $this->getQuan($v['shangquan']);
            //  预算
            $bid['id'] = $v['budget'];
            $budget = M('Budget')->where($bid)->select();
            $data[$k]['budget'] = $budget[0]['b_name'];
//            职业
            $work['id'] = $v['work'];
            $workdata = M('Work')->where($work)->select();
            $data[$k]['work'] = $workdata[0]['w_name'];
        }

        $this->assign('data', $data);
        $this->assign('show', $show);
        $this->display();
    }

//    获取商圈
    public function getQuan($id) {
        $tra = M('Trading');
        $shangquan = $tra->where(array('id' => $id))->select();
        $pid = $shangquan[0]['pid'];
        $name = $shangquan[0]['name'];
        $sname = "";
        $shangquan1 = $tra->where(array('id' => $pid))->select();
        $name1 = $shangquan1[0]['name'];
        $sname .= $name1 . "-";
        $sname .= $name;
        return $sname;
    }

//    添加用户
    public function add_user() {
        if (IS_POST) {
            $data = I('post.');

            if (empty($data['nickname'])) {
                $this->error('请输入昵称！');
            }
            //保存头像路径
            $data['avatar'] = $data['upload'];
            if (empty($data['avatar'])) {
                $this->error('请上传头像！');
            }
            if (empty($data['sex'])) {
                $this->error('请选择性别！');
            }
            if (empty($data['province'])) {
                $this->error('请输入省份！');
            }
            if (empty($data['city'])) {
                $this->error('请输入城市！');
            }
            if ($data['shangquan'] == 0 && $data['ditie']!=0) {
                $data['is_place']=2;
            }else if($data['ditie'] == 0 && $data['shangquan']!=0){
                $data['is_place']=1;
            }else if($data['ditie'] == 0 && $data['shangquan']==0){
                
                $this->error('请选择租住地点！');
            }
//            时间戳转化为毫秒数
            $starttime = strtotime($data['checkintime']);
            $endtime = strtotime($data['checkoutime']);
            $time = time() - 86400;
            if ($starttime > $endtime) {
                $this->error('最早入住时间不能大于最晚入住时间！');
            }
            if ($time > $starttime) {
                $this->error('时间以过！');
            }
            if (empty($data['checkintime'])) {
                $this->error('请选择最早入住时间！');
            }
            if (empty($data['checkoutime'])) {
                $this->error('请选择最晚入住时间！');
            }
            if ($data['tenant_long'] == 0) {
                $this->error('请选择租房时长！');
            }
            
//            用户高级匹配规则
            if ($data['is_senior'] == 1) {
                if (empty($data['age'])) {
                $this->error('请输入年龄！');
                }
                if ($data['school'] =='0') {
                    $this->error('请选择学校！');
                }
                if ($data['constellation'] =='0') {
                    $this->error('请选择星座！');
                }
                if ($data['budget']=='0') {
                    $this->error('请选择预算金额！');
                }
                $data['phone'] = (int) $data['phone'];
                $isMob="/^1[34578]{1}\d{9}$/";
                $isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
                if(!preg_match($isMob,$data['phone']) && !preg_match($isTel,$data['phone']))
                {
                    $this->error('手机或电话号码格式不正确！');
                }

                if (empty($data['weixin'])) {
                    $this->error('请输入微信号！');
                }
                if ($data['work']=='0') {
                    $this->error('请选择职业！');
                }
                if (empty($data['personality'])) {
                    $this->error('请选择个性！');
                }
                if (empty($data['motion'])) {
                    $this->error('请选择喜欢的运动！');
                }
                if (empty($data['entertainment'])) {
                    $this->error('请选择娱乐活动！');
                }
            }

            //huikang 添加用户类型 是否为高级匹配用户
            if (empty($data['is_senior'])) {
                $this->error('请选择用户类型！');
            }
            if ($data['is_senior'] == 1) {
                $data['is_match'] = 2;
            }else{
                $data['is_match'] = 1;
            }
//            个性运动娱乐数组转化字符串处理
            $per = implode(',',$data['personality']);
            $mot = implode(',',$data['motion']);
            $ent = implode(',',$data['entertainment']);
            $data['personality'] = $per;
            $data['motion'] = $mot;
            $data['entertainment'] = $ent;
            
            unset($data['file']);
            unset($data['upload']);
            unset($data['shangquans']);
            unset($data['dities']);
            //后台导入用户，与后台添加用户，类型（2）
            $data['is_true'] = 2;
            $arr = M('User')->add($data);
            
//            当前用户希望室友的习惯
            $datas['sex'] = $data['sexs'];
            $datas['pet'] = $data['pets'];
            $datas['smoking'] = $data['smokings'];
            $datas['bedlate'] = $data['bedlates'];
            $datas['lovers'] = $data['loverss'];
            $datas['uid'] = $arr;
            $match = M('UserPrimaryMatching')->add($datas);
            
            if ($arr && $match) {
                $this->success('添加成功！', U('Admin/User/index'));
            } else {
                $this->error('添加失败！', U('Admin/User/add_user'));
            }
        } else {
            //            生活习惯
//            $Customs = M('LifeCustoms');
//            $customsdata = $Customs->select();
//             租房时长
            $Long = M('TenantLong');
            $longdata = $Long->select();
//            院校
            $school = M('School');
            $schooldata = $school->select();
//            商圈
            $trading = M('Trading');
            $tradingdata = $trading->where(array('pid' => 0))->select();
//            地铁
            $metro = M('Metro');
            $metrodata = $metro->where(array('pid' => 0))->select();

//            星座
            $constellation = M('Constellation');
            $constellationdata = $constellation->select();
            $this->assign('constellationdata', $constellationdata);
//            预算
            $budget = M('Budget');
            $budgetdata = $budget->select();
            $this->assign('budgetdata', $budgetdata);
//            个性
            $taste = M('Taste');
            $tastedata = $taste->where(array('pid' => 1))->select();
            $this->assign('tastedata', $tastedata);
//            运动
            $portdata = $taste->where(array('pid' => 2))->select();
            $this->assign('portdata', $portdata);
//        娱乐
            $disportdata = $taste->where(array('pid' => 3))->select();
            $this->assign('disportdata', $disportdata);

            //            职业
            $work = M('Work');
            $workdata = $work->select();
            $this->assign('workdata', $workdata);


            $this->assign('schooldata', $schooldata);
            $this->assign('tradingdata', $tradingdata);
            $this->assign('metrodata', $metrodata);
            $this->assign('longdata', $longdata);
            $this->assign('customsdata', $customsdata);
            $this->display();
        }
    }

    //TP3.2.3上传图片
    public function baseImg() {
        $file = $_FILES;
        $config = array(
            'rootPath' => "./Public/",
            'savePath' => "/Upload/",
            'exts' => array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
            'subName' => date('Ymd'),
            'autoSub' => true,
        );
        $uplode = new \Think\Upload($config);
        $info = $uplode->upload($file);
        if (!$info) {
            $this->error($uplode->getError());
        }
        $data['msg'] = '/Public' . $info['file']['savepath'] . $info['file']['savename'];
        $data['code'] = '1';
        echo json_encode($data);
    }

    public function edit_user() {
        if (IS_POST) {
            $data = I('post.');
            $where['id'] = $data['id'];
            if (empty($data['nickname'])) {
                $this->error('请输入昵称！');
            }
            $data['avatar'] = $data['upload'];
            if (empty($data['avatar'])) {
                $this->error('请上传头像！');
            }
            if (empty($data['sex'])) {
                $this->error('请选择性别！');
            }
            if (empty($data['province'])) {
                $this->error('请输入省份！');
            }
            if (empty($data['city'])) {
                $this->error('请输入城市！');
            }
            if ($data['shangquan'] == 0 && $data['ditie']!=0) {
                $data['is_place']=2;
                $data['shangquan']=0;
            }else if($data['ditie'] == 0 && $data['shangquan']!=0){
                $data['is_place']=1;
                $data['ditie']=0;
            }else if($data['ditie'] == 0 && $data['shangquan']==0){
                $this->error('请选择租住地点！');
            }
            if (empty($data['checkintime'])) {
                $this->error('请选择最早入住时间！');
            }
            if (empty($data['checkoutime'])) {
                $this->error('请选择最晚入住时间！');
            }
//            时间戳转化为毫秒
            $starttime = strtotime($data['checkintime']);
            $endtime = strtotime($data['checkoutime']);
            $time = time() - 86400;
            if ($starttime > $endtime) {
                $this->error('最早入住时间不能大于最晚入住时间！');
            }
            if ($time > $starttime) {
                $this->error('时间以过！');
            }

            if (empty($data['tenant_long'])) {
                $this->error('请选择租房时长！');
            }
            

//            用户高级匹配规则
            if ($data['is_senior'] == 1) {
                if (empty($data['age'])) {
                $this->error('请输入年龄！');
                 }
                if ($data['school'] =='0') {
                    $this->error('请选择学校！');
                }
                if ($data['constellation'] =='0') {
                    $this->error('请选择星座！');
                }
                if ($data['budget']=='0') {
                    $this->error('请选择预算金额！');
                }
                $data['phone'] = (int) $data['phone'];
                $isMob="/^1[34578]{1}\d{9}$/";
                $isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
                if(!preg_match($isMob,$data['phone']) && !preg_match($isTel,$data['phone']))
                {
                    $this->error('手机或电话号码格式不正确！');
//                    xformatOutPutJsonData('error', '1', '手机或电话号码格式不正确.如果是固定电话，必须形如(0315-87876787)!');
                }
//                if (!preg_match("/^1[34578]{1}\d{9}$/", $data['phone'])) {
//                    $this->error('请输入正确的手机号！');
//                }

                if (empty($data['weixin'])) {
                    $this->error('请输入微信号！');
                }
                if ($data['work']=='0') {
                    $this->error('请选择职业！');
                }
                if (empty($data['personality'])) {
                    $this->error('请选择个性！');
                }
                if (empty($data['motion'])) {
                    $this->error('请选择喜欢的运动！');
                }
                if (empty($data['entertainment'])) {
                    $this->error('请选择娱乐活动！');
                }
            }
            //huikang 添加用户类型 是否为高级匹配用户
            if (empty($data['is_senior'])) {
                $this->error('请选择用户类型！');
            }
            if ($data['is_senior'] == 1) {
                $data['is_match'] = 2;
            }else{
                $data['is_match'] = 1;
            }
//            个性运动娱乐数组转化字符串处理
            $per = implode(',',$data['personality']);
            $mot = implode(',',$data['motion']);
            $ent = implode(',',$data['entertainment']);
            $data['personality'] = $per;
            $data['motion'] = $mot;
            $data['entertainment'] = $ent;
//            个性，运动，娱乐处理end

            unset($data['file']);
            unset($data['upload']);
            unset($data['shangquans']);
            unset($data['dities']);
            //后台导入用户，与后台添加用户，类型（2）
            $data['is_true'] = 2;
            $arr = M('User')->where($where)->save($data);
            //            当前用户希望室友的习惯
            $datas['sex'] = $data['sexs'];
            $datas['pet'] = $data['pets'];
            $datas['smoking'] = $data['smokings'];
            $datas['bedlate'] = $data['bedlates'];
            $datas['lovers'] = $data['loverss'];
            $mid = $data['id'];
            $match = M('UserPrimaryMatching')->where(array('uid' => $mid))->save($datas);
            
            if ($arr !== false && $match !== FALSE) {
                $this->success('修改成功！', U('Admin/User/index'));
            } else {
                $this->error('修改失败！', U('Admin/User/edit_user', array('id' => $where['id'])));
            }
        } else {
            //            生活习惯
//            $Customs = M('LifeCustoms');
//            $customsdata = $Customs->select();
//             租房时长
            $Long = M('TenantLong');
            $longdata = $Long->select();
//            院校
            $school = M('School');
            $schooldata = $school->select();

//            星座
            $constellation = M('Constellation');
            $constellationdata = $constellation->select();
            $this->assign('constellationdata', $constellationdata);
//            预算
            $budget = M('Budget');
            $budgetdata = $budget->select();
            $this->assign('budgetdata', $budgetdata);
//            商圈
            $trading = M('Trading');
            $tradingdata = $trading->where(array('pid' => 0))->select();
//            地铁
            $metro = M('Metro');
            $metrodata = $metro->where(array('pid' => 0))->select();

//            个性
            $taste = M('Taste');
            $tastedata = $taste->where(array('pid' => 1))->select();
            $this->assign('tastedata', $tastedata);
//            运动
            $portdata = $taste->where(array('pid' => 2))->select();
            $this->assign('portdata', $portdata);
//        娱乐
            $disportdata = $taste->where(array('pid' => 3))->select();
            $this->assign('disportdata', $disportdata);
//            职业
            $work = M('Work');
            $workdata = $work->select();
            $this->assign('workdata', $workdata);

            $this->assign('schooldata', $schooldata);
            $this->assign('tradingdata', $tradingdata);
            $this->assign('metrodata', $metrodata);
            $this->assign('longdata', $longdata);
            $this->assign('customsdata', $customsdata);

            $id = I('get.id');
            $obj = M('User');
            $res = $obj->where(array('id' => $id))->find();

//            希望
            $matching = M('UserPrimaryMatching')->where(array('uid' => $id))->select();
            $this->assign('matching', $matching);
            //            所选商圈
            $tradingdatas = $trading->where(array('id' => $res['shangquan']))->select();
            $this->assign('tradingdatas', $tradingdatas);
            $tradingdatass = $trading->where(array('pid' => $tradingdatas[0][pid]))->select();
            $this->assign('tradingdatass', $tradingdatass);

            $metrodatas = $metro->where(array('id' => $res['ditie']))->select();
            $this->assign('metrodatas', $metrodatas);
            $metrodatass = $metro->where(array('pid' => $metrodatas[0][pid]))->select();
            $this->assign('metrodatass', $metrodatass);
//            var_dump($res);die;
            $this->assign('res', $res);
            $this->display();
        }
    }

    public function del_user() {
        $id = I('get.id');
        $res = M('User')->where(array('id' => $id))->delete();
         M('UserPrimaryMatching')->where(array('uid' => $id))->delete();
        if ($res) {
            $this->success('删除成功', U('Admin/User/index'));
        } else {
            $this->error('删除失败');
        }
    }

//    一键更新微信用户信息   根据openid获取用户昵称跟头像更新数据库
    public function update() {
        //虎丘全部用户数据
        $user = M('user')->field('id,openid')->select();
        foreach ($user as $key => $value) {
            if (!empty($value['openid'])) {
                //获取全部真实用户
                $info[] = $value;
            }
        }
        //获取access_token
        $WeChat = new \Org\Api\WeChat();
        $data = $WeChat->getAccessToken();
        foreach ($info as $k => $v) {
            $user = $WeChat->getUserInfo($data['access_token'], $v['openid']);
            $map['avatar'] = $data['avatar'];
            $map['name'] = base64_encode($data['nickName']);
            $map['nickname'] = $data['nickName'];
            M('user')->where('id=' . $v['id'])->save($map);
        }
//        session('wx_user', $user);
    }

//读取excel表格数据保存到数据库
    public function push() {
        if (IS_POST) {
            $files = $_FILES;
            $config = array(
                'rootPath' => "./Public/",
                'savePath' => "/Push/",
                'exts' => array('xls', 'xlsx'),
                'subName' => date('Ymd'),
                'autoSub' => true,
            );
            $uplode = new \Think\Upload($config);
            $info = $uplode->upload($files);
            if (!$info) {
                $this->error($uplode->getError());
            }

//             读取excel表格
//文件路径
            $file_path = "./Public" . $info['push']['savepath'] . $info['push']['savename'];
            $data = import_excel($file_path);
//            格林威志时间转化为时间戳
            import("Common.Org.PHPExcel.Shared.Date");
            $shared = new \PHPExcel_Shared_Date();
//            import("Common.Org.PHPExcel.Shared.Date");
//            $shared = new \PHPExcel_Shared_Date();
            //导入csv文件
//            $handle = fopen($file_path, 'r');
//            while ($data = fgetcsv($handle)) {
//                $ditie['insite'] = $data[0];
//                $ditie['outsite'] = $data[1];
//                $ditie['in_long'] = $data[2];
//                $ditie['in_lat'] = $data[3];
//                $ditie['out_long'] = $data[4];
//                $ditie['out_lat'] = $data[5];
//                $ditie['distance'] = $data[6];
//                $ditie['score'] = $data[7];
//                $res = M('MetroScore')->add($ditie);
//            }
            
            foreach ($data as $k => $v) {
//                $k>1第几行开始录入数据($k从1开始)
                if ($k > 1) {
                    $record['nickname'] = $v[0];
                    $record['sex'] = $v[1];
                    $record['shangquan'] = $v[2];
                    $record['ditie'] = $v[3];
                    $record['checkintime'] = date('Y/m/d H:i:s', $shared->ExcelToPHP($v[4]) - 28800);
                    $record['checkoutime'] = date('Y/m/d H:i:s', $shared->ExcelToPHP($v[5]) - 28800);
                    $record['tenant_long'] = $v[6];
                    $record['age'] = $v[7];
                    $record['school'] = $v[8];
                    $record['work'] = $v[9];
                    $record['constellation'] = $v[10];
                    $record['budget'] = $v[11];
                    $record['phone'] = $v[12];
                    $record['hometown'] = $v[13];
                    $record['weixin'] = $v[14];
                    $record['personality'] = $v[15];
                    $record['motion'] = $v[16];
                    $record['entertainment'] = $v[17];
                    $record['pet'] = $v[18];
                    $record['smoking'] = $v[19];
                    $record['bedlate'] = $v[20];
                    $record['lovers'] = $v[21];
                    $record['province'] = $v[22];
                    $record['is_match'] = $v[24];
              
                    //后台导入用户，与后台添加用户，类型（2）
                    $record['is_true'] = 2;
                    $res = M('User')->add($record);
                    $map['uid'] = $res;
                    $map['sex'] = $v[23];
                    $result = M('UserPrimaryMatching')->add($map);
                }
            }
            if ($res !== false && $result !== FALSE) {
                $this->success('数据导入成功');
            } else {
                 $this->error('数据导入失败');
            }
        } else {
            $this->display();
        }
    }

//    判断是否是base64
    function is_base64($str) {
//这里多了个纯字母和纯数字的正则判断
        if (@preg_match('/^[0-9]*$/', $str) || @preg_match('/^[a-zA-Z]*$/', $str)) {
            return false;
        } elseif ($this->isutf8(base64_decode($str)) && base64_decode($str) != '') {
            return true;
        }
        return false;
    }

//判断否为UTF-8编码
    function isutf8($str) {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247)) {
                    return false;
                } elseif ($c > 239) {
                    $bytes = 4;
                } elseif ($c > 223) {
                    $bytes = 3;
                } elseif ($c > 191) {
                    $bytes = 2;
                } else {
                    return false;
                }
                if (($i + $bytes) > $len) {
                    return false;
                }
                while ($bytes > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
                    $bytes--;
                }
            }
        }
        return true;
    }

    public function getsq() {
        $id = I('get.id');
        $trading = M('Trading');
        if($id != 0 ){
            $data = $trading->where(array('pid' => $id))->select();
        }else{
            $data = 0;
        }
        $this->ajaxReturn($data);
    }

    public function getditie() {
        $id = I('get.id');
        $trading = M('Metro');
        if($id != 0 ){
             $data = $trading->where(array('pid' => $id))->select();
        }else{
            $data = 0;
        }
       
        $this->ajaxReturn($data);
    }

}
