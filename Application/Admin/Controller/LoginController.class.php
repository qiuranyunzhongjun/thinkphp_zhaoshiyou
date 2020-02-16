<?php

namespace Admin\Controller;

use Common\Controller\PublicBaseController;

/**
 * 后台登录Controller
 */
class LoginController extends PublicBaseController {

    /**
     * 首页
     */
    public function index() {
        if (IS_POST) {
            $data = I('post.');
            
            if( trim($data['username']) == '' || trim($data['password']) == '' ){
                $this->error('请输入用户名和密码！');
            }
            
            if ( !check_verify($data['verify'],'adm') ) {
                $this->error('验证码输入错误！');
            }
            
            $user_data = M('Admin')->where(array('username' => $data['username']))->find();
            if ( $user_data['status'] == '0' ) {
                $this->error('用户已被禁用！');
            }

            if ( $user_data['password'] != think_ucenter_md5($data['password']) ) {
                $this->error('用户名或密码错误');
            } else {
                $_SESSION['admin'] = array(
                    'id' => $user_data['id'],
                    'username' => $user_data['username'],
                    'avatar' => $user_data['avatar']
                );
                $this->success('登录成功、前往管理后台', U('Admin/Index/index'));
            }
        } else {
            if( check_login() ){
                $this->redirect(U('Admin/Index/index'));
            }else{
                $this->assign($assign);
                $this->display();
            }            
        }
    }

    /**
     * 退出
     */
    public function logout() {
        session('admin', null);
        $this->success('退出成功、前往登录页面', U('Admin/Login/index'));
    }
    
    public function verify() {
        show_verify('adm');
    }

}
