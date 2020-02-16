<?php

namespace Common\Model;

use Common\Model\BaseModel;

/**
 * 菜单操作model
 */
class AdminNavModel extends BaseModel {
    protected $_validate = array(
        array('name','require','菜单名不能为空',1),
        array('mca','require','连接不能为空',1),
        array('mca','_checkMcaFormat','连接格式不正确',1,'callback'),
        array('mca','','连接不能重复',1,'unique',1),
        array('mca,id','_checkMcaUn','连接不能重复',1,'callback',2),
    );
    
    /**
     * 编辑时验证连接是否重复
     */
    protected function _checkMcaUn($options){
        $data = $options;
        $map = array(
            'id' => array('neq',$data['id']),
            'mca' => array('eq',$data['mca'])
        );
        $mca = $this->where($map)->find();
        if( $mca ){
            return false;
        }{
            return true;
        }
    }
    /**
     * 删除数据
     * @param	array	$map	where语句数组形式
     * @return	boolean			操作是否成功
     */
    public function deleteData($map) {
        $count = $this
                ->where(array('pid' => $map['id']))
                ->count();
        if ($count != 0) {
            return false;
        }
        $this->where(array($map))->delete();
        return true;
    }

    /**
     * 获取全部菜单
     * @param  string $type tree获取树形结构 level获取层级结构
     * @return array       	结构数据
     */
    public function getTreeData($type = 'tree', $order = '') {
        // 判断是否需要排序
        if (empty($order)) {
            $data = $this->select();
        } else {
            $data = $this->order('order_number is null,' . $order)->select();
        }
        // 获取树形或者结构数据
        if ($type == 'tree') {
            $data = \Org\Nx\Data::tree($data, 'name', 'id', 'pid');
        } elseif ($type = "level") {
            $data = \Org\Nx\Data::channelLevel($data, 0, '&nbsp;', 'id');
            // 显示有权限的菜单
            $auth = new \Think\Auth();
            foreach ($data as $k => $v) {
                if ($auth->check($v['mca'], $_SESSION['admin']['id'])) {
                    foreach ($v['_data'] as $m => $n) {
                        if (!$auth->check($n['mca'], $_SESSION['admin']['id'])) {
                            unset($data[$k]['_data'][$m]);
                        }
                    }
                } else {
                    // 删除无权限的菜单
                    unset($data[$k]);
                }
            }
        }
        // p($data);die;
        return $data;
    }

}
