<?php

namespace Admin\Controller;

use Common\Controller\AdminBaseController;

/**
 * 后台首页控制器
 */
class AdvancedController extends AdminBaseController {

//    院校管理
//    列表
    public function schoolList() {
        $obj = M('School');
        $data = $obj->select();
        $this->assign('data', $data);
        $this->display();
    }

//    添加学院
    public function add_school() {
        $data = I('post.');
        $data['addtime'] = date('Y-m-d H:i:s');
        $obj = M('School');
        if (empty($data['s_name'])) {
            $this->error('请输入学院名称');
        } else {
            $result = $obj->add($data);
            if ($result) {
                $this->success('添加成功', U('Admin/Advanced/schoolList'));
            } else {
                $this->error($obj->getError());
            }
        }
    }

    //    修改学院
    public function edit_school() {
        $Data = I('post.');
        $map = array(
            'sid' => $Data['sid']
        );
        $data['s_name'] = trim($Data['s_name'], "/");
        $data['addtime'] = date('Y-m-d H:i:s');
        if (empty($data['s_name'])) {
            $this->error('请输入学院名称');
        } else {
            $result = M('School')->where($map)->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Admin/Advanced/schoolList'));
            } else {
                $this->error('错误');
            }
        }
    }

//   删除学院
    public function delschool() {
        $sid = I('get.sid');
        $map = array(
            'sid' => $sid
        );
        $result = M('School')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Advanced/schoolList'));
        } else {
            $this->error('失败');
        }
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////
//    职业管理列表
    public function work() {
        $obj = M('Work');
        $data = $obj->select();
        $this->assign('data', $data);
        $this->display();
    }

    //    添加职业
    public function add_work() {
        $data = I('post.');
        $data['addtime'] = date('Y-m-d H:i:s');
        $obj = M('Work');
        if (empty($data['w_name'])) {
            $this->error('请输入职业名称');
        } else {
            $result = $obj->add($data);
            if ($result) {
                $this->success('添加成功', U('Admin/Advanced/work'));
            } else {
                $this->error($obj->getError());
            }
        }
    }

    //    修改职业
    public function edit_work() {
        $Data = I('post.');
        $map = array(
            'id' => $Data['id']
        );
        $data['w_name'] = trim($Data['w_name'], "/");
        $data['addtime'] = date('Y-m-d H:i:s');
        if (empty($data['w_name'])) {
            $this->error('请输入职业名称');
        } else {
            $result = M('Work')->where($map)->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Admin/Advanced/work'));
            } else {
                $this->error('错误');
            }
        }
    }

    //   删除职业
    public function delwork() {
        $id = I('get.id');
        $map = array(
            'id' => $id
        );
        $result = M('Work')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Advanced/work'));
        } else {
            $this->error('失败');
        }
    }
////////////////////////////////////////////////////////////////////////////////////////////////
//   星座管理列表
    public function constellation() {
        $obj = M('Constellation');
        $data = $obj->select();
        $this->assign('data', $data);
        $this->display();
    }

    //    添加星座
    public function add_constellation() {
        $data = I('post.');
        $data['addtime'] = date('Y-m-d H:i:s');
        $obj = M('Constellation');
        if (empty($data['c_name'])) {
            $this->error('请输入星座名称');
        } else {
            $result = $obj->add($data);
            if ($result) {
                $this->success('添加成功', U('Admin/Advanced/constellation'));
            } else {
                $this->error($obj->getError());
            }
        }
    }

    //    修改星座
    public function edit_constellation() {
        $Data = I('post.');
        $map = array(
            'id' => $Data['id']
        );
        $data['c_name'] = trim($Data['c_name'], "/");
        $data['addtime'] = date('Y-m-d H:i:s');
        if (empty($data['c_name'])) {
            $this->error('请输入星座名称');
        } else {
            $result = M('Constellation')->where($map)->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Admin/Advanced/constellation'));
            } else {
                $this->error('错误');
            }
        }
    }

    //   删除职业
    public function delconstellation() {
        $id = I('get.id');
        $map = array(
            'id' => $id
        );
        $result = M('Constellation')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Advanced/constellation'));
        } else {
            $this->error('失败');
        }
    }
///////////////////////////////////////////////////////////////////////////////////////////////////
//预算管理列表
    public function budget() {
        $obj = M('Budget');
        $data = $obj->select();
        $this->assign('data', $data);
        $this->display();
    }

    //    添加星座
    public function add_budget() {
        $data = I('post.');
        $data['addtime'] = date('Y-m-d H:i:s');
        $obj = M('Budget');
        if (empty($data['b_name'])) {
            $this->error('请输入预算金额');
        } else {
            $result = $obj->add($data);
            if ($result) {
                $this->success('添加成功', U('Admin/Advanced/budget'));
            } else {
                $this->error($obj->getError());
            }
        }
    }

    //    修改星座
    public function edit_budget() {
        $Data = I('post.');
        $map = array(
            'id' => $Data['id']
        );
        $data['b_name'] = trim($Data['b_name'], "/");
        $data['addtime'] = date('Y-m-d H:i:s');
        if (empty($data['b_name'])) {
            $this->error('请输入星座名称');
        } else {
            $result = M('Budget')->where($map)->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Admin/Advanced/budget'));
            } else {
                $this->error('错误');
            }
        }
    }

    //   删除职业
    public function delbudget() {
        $id = I('get.id');
        $map['id'] =  $id;
        $result = M('Budget')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Advanced/budget'));
        } else {
            $this->error('失败');
        }
    }
    
    
    //兴趣爱好管理//////////////////////////////////////////////////////////////////////
    
//    兴趣爱好首页
    public function hobbies(){
        $obj = M('Taste');
        $data = $obj->where(array('pid' => 1))->select();
        $this->assign('data', $data);
        $this->display();
    }
    //    个性管理
    public function character(){
        $obj = M('Taste');
        $data = $obj->where(array('pid' => 1))->select();
        $this->assign('data', $data);
        $this->display();
    }
//    运动管理
    public function sport(){
        $obj = M('Taste');
        $data = $obj->where(array('pid' => 2))->select();
        $this->assign('data', $data);
        $this->display();
    }
//    娱乐管理
    public function disport(){
        $obj = M('Taste');
        $data = $obj->where(array('pid' => 3))->select();
        $this->assign('data', $data);
        $this->display();
    }
    
//    添加兴趣爱好
    public function add(){
        $data = I('post.');
        $data['uptime'] = date('Y-m-d H:i:s');
        $link=$data['link'];
        $obj = M('Taste');
        $pid = $data['pid'];
        if (empty($data['tasname'])) {
            $this->error('请输入兴趣爱好名称！');
        } 
            $result = $obj->add($data);
            if ($result) {
                $this->success('添加成功', U('Admin/Advanced/'.$link,array('pid'=>$pid)));
            } else {
                $this->error($obj->getError());
            }
    }
//    修改兴趣爱好
    public function edit(){
        $Data = I('post.');
        $where['id'] = $Data['id'];
        $link=$Data['link'];
        $data['tasname'] = trim($Data['tasname'], "/");
        $data['uptime'] = date('Y-m-d H:i:s');
        if (empty($data['tasname'])) {
            $this->error('请输入兴趣爱好名称');
        } else {
            $result = M('Taste')->where($where)->save($data);
            if ($result !== false) {
                $this->success('修改成功',U('Admin/Advanced/'.$link));
            } else {
                $this->error('错误');
            }
        }
    }
    
        //   删除兴趣爱好
    public function del() {
        $id = I('get.id');
        $data = I('get.');
        $map['id'] =  $id;
        $link=$data['link'];
        $result = M('Taste')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Advanced/'.$link));
        } else {
            $this->error('失败');
        }
    }
    

    


}
