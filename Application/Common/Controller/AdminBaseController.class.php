<?php

namespace Common\Controller;

use Common\Controller\BaseController;

/**
 * admin 基类控制器
 */
class AdminBaseController extends BaseController {

    /**
     * 初始化方法
     */
    public function _initialize() {
        parent::_initialize();

        if ( defined('UID') ) {
            return;
        }
        define('UID', get_uid());
        if (!UID) {// 还没登录 跳转到登录页面
            $this->redirect('Login/index');
        }

        $auth = new \Think\Auth();
        $rule_name = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
        $result = $auth->check($rule_name, $_SESSION['admin']['id']);
        if (!$result) {
            $this->error('您没有权限访问');
        }
        // 分配菜单数据
        $nav_data = D('AdminNav')->getTreeData('level', 'order_number,id');
        $assign = array(
            'nav_data' => $nav_data
        );
        $this->assign($assign);
    }

}
