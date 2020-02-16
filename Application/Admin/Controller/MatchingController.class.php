<?php

namespace Admin\Controller;

use Common\Controller\AdminBaseController;

/**
 * 后台首页控制器
 */
class MatchingController extends AdminBaseController {
    
    public function match(){
        $this->display();
    }
    
    
    
    //修改规则
     public function index() {
         if (IS_POST) {
            $data = I('post.');
            $q_ditian = $data['q_ditian'];
//            $ditian = $data['ditian'];
            $q_enancytime = $data['q_enancytime'];
//            $w_enancytime = $data['w_enancytime'];
            $enancytime = $data['enancytime'];
            $id = $data['id'];
            $map['id'] = $id;
            if ($q_ditian == "") {
                $this->error('请输入准确地点分值！');
            }
//            if ($ditian == "") {
//                $this->error('请输入相似地点分值！');
//            }
            if ($q_enancytime == "") {
                $this->error('请输入准确租住时间分值！');
            }
//            if ($w_enancytime == "") {
//                $this->error('请输入准确租住时间分值！');
//            }
            if ($enancytime == "") {
                $this->error('请输入相似租住时间分值！');
            }
            if ($data['tag'] == "") {
                $this->error('请输入标签分值！');
            }
            if ($data['age'] == "") {
                $this->error('请输入年龄差小于3的加分值！');
            }
            if ($data['age1'] == "") {
                $this->error('请输入年龄差大于等于3小于5的加分值！');
            }
            if ($data['age2'] == "") {
                $this->error('请输入年龄差大于等于5小于8的加分值！');
            }
            if ($data['age3'] == "") {
                $this->error('请输入年龄差大于8的加分值！');
            }
            if ($data['school'] == "") {
                $this->error('请输入学校分值！');
            }
            if ($data['constellation'] == "") {
                $this->error('请输入星座分值！');
            }

            if ($data['address'] == "") {
                $this->error('请输入家乡分值！');
            }
            if ($data['work'] == "") {
                $this->error('请输入职业分值！');
            }

                $result = M('Pmatch')->where($map)->save($data);
                if ($result == true) {
                    $this->success('修改成功！', U('Admin/Matching/index'));
                } else {
                    $this->error('修改失败！', U('Admin/Matching/index'));
                }
        } else {
            $id = 1;
            $obj = M('Pmatch');
            $res = $obj->where(array('id' => $id))->find();
            $this->assign('res',$res);
            $this->display();
        }
    }
}
