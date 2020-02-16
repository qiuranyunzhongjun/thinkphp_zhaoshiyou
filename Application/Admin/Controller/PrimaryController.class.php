<?php

namespace Admin\Controller;

use Common\Controller\AdminBaseController;

/**
 * 后台初级匹配设置
 */
class PrimaryController extends AdminBaseController {

    /**
     * 生活习惯
     */
    public function habit() {
        $Mod = M('LifeCustoms');
        $data = $Mod->select();
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加生活习惯
     */
    public function add() {
        $data = I('post.');
        unset($data['id']);
        $data['name'] = trim($data['name'], "/");
        $data['uptime'] = date('Y-m-d H:i:s');

        $AuthRule = M('LifeCustoms');
        $result = $AuthRule->add($data);
        if ($result) {
            $this->success('添加成功', U('Admin/Primary/habit'));
        } else {
            $this->error($AuthRule->getError());
        }
    }

    /**
     * 修改生活习惯
     */
    public function edit() {
        $Data = I('post.');
        $map = array(
            'id' => $Data['id']
        );
        $data['name'] = trim($Data['name'], "/");
        $data['uptime'] = date('Y-m-d H:i:s');
        $result = M('LifeCustoms')->where($map)->save($data);
        if ($result !== false) {
            $this->success('修改成功', U('Admin/Primary/habit'));
        } else {
            $this->error('错误');
        }
    }

    /**
     * 删除生活习惯
     */
    public function delete() {
        $id = I('get.id');
        $map = array(
            'id' => $id
        );
        $result = M('LifeCustoms')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Primary/habit'));
        } else {
            $this->error('失败');
        }
    }

    /**
     * 租房时长
     */
    public function long() {
        $Mod = M('TenantLong');
        $data = $Mod->select();
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加租房时长
     */
    public function addlong() {
        $data = I('post.');
        unset($data['id']);
        $data['name'] = trim($data['name'], "/");
        $data['uptime'] = date('Y-m-d H:i:s');
        $AuthRule = M('TenantLong');
        $result = $AuthRule->add($data);
        if ($result) {
            $this->success('添加成功', U('Admin/Primary/long'));
        } else {
            $this->error($AuthRule->getError());
        }
    }

    /**
     * 修改租房时长
     */
    public function editlong() {
        $Data = I('post.');
        $map = array(
            'id' => $Data['id']
        );
        $data['name'] = trim($Data['name'], "/");
        $data['uptime'] = date('Y-m-d H:i:s');
        $result = M('TenantLong')->where($map)->save($data);
        if ($result !== false) {
            $this->success('修改成功', U('Admin/Primary/long'));
        } else {
            $this->error('错误');
        }
    }

    /**
     * 删除租房时长
     */
    public function dellong() {
        $id = I('get.id');
        $map = array(
            'id' => $id
        );
        $result = M('TenantLong')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Primary/long'));
        } else {
            $this->error('失败');
        }
    }

    //    商圈管理///////////////////////////////////////////////////////////////////////////////////////////

    public function trading() {
        $obj = D('Trading');
        $data = $obj->getTreeData('tree', 'id', 'name');
        $this->assign('data', $data);
        $this->display();
    }

    //    添加商圈
    public function add_trading() {
        $data = I('post.');
        $data['uptime'] = date('Y-m-d H:i:s');
        $data['name'] = trim($data['name'], "/");
        $obj = M('Trading');
        if (empty($data['name'])) {
            $this->error('请输入商圈名称');
        } else {
            $result = $obj->add($data);
            if ($result) {
                $this->success('添加成功', U('Admin/Primary/trading'));
            } else {
                $this->error($obj->getError());
            }
        }
    }

    //    修改商圈
    public function edit_trading() {
        $Data = I('post.');
        $map = array(
            'id' => $Data['id']
        );
        $data['name'] = trim($Data['name'], "/");
        $data['uptime'] = date('Y-m-d H:i:s');
        if (empty($data['name'])) {
            $this->error('请输入商圈名称');
        } else {
            $result = M('Trading')->where($map)->save($data);
            if ($result !== false) {
                $this->success('修改成功', U('Admin/Primary/trading'));
            } else {
                $this->error('错误');
            }
        }
    }

    //   删除商圈
    public function del_trading() {
        $id = I('get.id');
        $map['id'] = $id;
        $result = M('Trading')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Primary/trading'));
        } else {
            $this->error('失败');
        }
    }

    /*
     * 地铁管理
     */

    public function metro() {
        $obj = D('Metro');
        $data = $obj->getTreeData('tree', 'id', 'title');
        $this->assign('data', $data);
        $this->display();
    }

    //    添加地铁
    public function add_metro() {
        $data = I('post.');
        if (empty($data['title'])) {
            $this->error('名称不能为空');
        }
        if (intval($data['pid']) !== 0) {
            if (empty($data['lon'])) {
                $this->error('请输入经度');
            }
            if (empty($data['lat'])) {
                $this->error('请输入纬度');
            }
        }
        $obj = M('Metro');
        $result = $obj->add($data);
        if ($result) {
            $this->success('添加成功');
        } else {
            $this->error($obj->getError());
        }
    }

    //    修改
    public function edit_metro() {
        $Data = I('post.');
        $map = array(
            'id' => $Data['id']
        );
        if (intval($Data['pid']) !== 0) {
            if (empty($Data['lon'])) {
                $this->error('请输入经度');
            }
            if (empty($Data['lat'])) {
                $this->error('请输入纬度');
            }
        }
        $result = M('Metro')->where($map)->save($Data);
        if ($result !== false) {
            $this->success('修改成功');
        } else {
            $this->error('错误');
        }
    }

    //   删除
    public function del_metro() {
        $id = I('get.id');
        $map['id'] = $id;
        $result = M('Metro')->where($map)->delete();
        if ($result) {
            $this->success('删除成功', U('Admin/Primary/metro'));
        } else {
            $this->error('失败');
        }
    }

}
