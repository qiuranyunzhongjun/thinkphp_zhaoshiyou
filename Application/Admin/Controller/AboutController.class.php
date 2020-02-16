<?php

namespace Admin\Controller;

use Common\Controller\AdminBaseController;

/**
 * 后台首页控制器
 */
class AboutController extends AdminBaseController {

    /**
     * 内容
     */
    
     public function aboutme() {
      $obj = M('Substance');
        $tid = $obj->where(array('type' => 0))->find();
        if (IS_POST) {
            $data = I('post.');
            $title = $data['title'];
            $content = $data['content'];
            $id = $data['subid'];
            if (empty($title)) {
                $this->error('请输入标题！');
            } else if (empty($content)) {
                $this->error('请输入内容！');
            } else {
                if (empty($tid)) {
                    $data['type'] = 0;
                    $arr = $obj->add($data);
                } else {
                    $arr = $obj->where(array('sub_id' => $id))->save($data);
                     if ($arr!==FALSE) {
                        $this->success('修改成功！', U('Admin/About/aboutme'));
                    } else {
                        $this->error('修改失败！', U('Admin/About/aboutme'));
                    }
                }
            }
        } else {
            $res = $obj->where(array('type' => 0))->find();
            $assign['data'] = $res;
            $this->assign($assign);
            $this->display();
        }
    }

    
    public function xieyi() {
        $obj = M('Substance');
        $tid = $obj->where(array('type' => 1))->find();
        if (IS_POST) {
            $data = I('post.');
            $title = $data['title'];
            $content = $data['content'];
            $id = $data['subid'];
            if (empty($title)) {
                $this->error('请输入标题！');
            } else if (empty($content)) {
                $this->error('请输入内容！');
            } else {
                if (empty($tid)) {
                    $data['type'] = 1;
                    $arr = $obj->add($data);
                } else {
                    $arr = $obj->where(array('sub_id' => $id))->save($data);
                     if ($arr!==FALSE) {
                        $this->success('修改成功！', U('Admin/About/xieyi'));
                    } else {
                        $this->error('修改失败！', U('Admin/About/xieyi'));
                    }
                }
            }
        } else {
            $res = $obj->where(array('type' => 1))->find();
            $assign['data'] = $res;
            $this->assign($assign);
            $this->display();
        }
    }

   
//    添加用户
    public function add_user() {
        $data = I('post.');
        if (empty($data)) {
            $this->display();
        } else {
            $name = $data['name'];
            $phone = $data['phone'];
            if (empty($name)) {
                $this->error('请输入姓名！');
            } else if (empty($phone)) {
                $this->error('请输入手机号！');
            } else if (!preg_match("/^1[34578]{1}\d{9}$/", $phone)) {
                $this->error('请输入正确的手机号！');
            } else {
                $arr = M('User')->add($data);
                if ($arr) {
                    $this->success('添加成功！', U('Admin/User/index'));
                } else {
                    $this->error('添加失败！', U('Admin/User/add_user'));
                }
            }
        }
    }

    public function edit_user() {
        if (IS_POST) {
            $data = I('post.');
            $name = $data['name'];
            $phone = $data['phone'];
            $id = $data['id'];
            $map['id'] = $id;
            if (empty($name)) {
                $this->error('请输入姓名！');
            } else if (empty($phone)) {
                $this->error('请输入手机号！');
            } else if (!preg_match("/^1[34578]{1}\d{9}$/", $phone)) {
                $this->error('请输入正确的手机号！');
            } else {
                $result = M('User')->where($map)->save($data);
                if ($result) {
                    $this->success('修改成功！', U('Admin/User/index'));
                } else {
                    $this->error('修改失败！', U('Admin/User/index', array('id' => $id)));
                }
            }
        } else {
            $id = I('get.id');
            $obj = M('User');
            $res = $obj->where(array('id' => $id))->find();
            $assign = array(
                'res' => $res
            );

            $this->assign($assign);
            $this->display();
        }
    }

}
