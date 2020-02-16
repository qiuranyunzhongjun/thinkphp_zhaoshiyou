<?php

namespace Common\Model;

use Common\Model\BaseModel;

/**
 * 权限规则model
 */
class AuthRuleModel extends BaseModel {
    
    protected $_validate = array(
        array('title','require','权限名不能为空',1),
        array('name','require','权限不能为空',1),
        array('name','_checkMcaFormat','权限格式不正确',1,'callback'),
        array('name','','权限不能重复',1,'unique',1),
        array('name,id','_checkNameUn','权限不能重复',1,'callback',2),
    );
    
    /**
     * 编辑时验证权限是否重复
     */
    protected function _checkNameUn($options){
        $data = $options;
        $map = array(
            'id' => array('neq',$data['id']),
            'name' => array('eq',$data['name'])
        );
        $name = $this->where($map)->find();
        if( $name ){
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
        $result = $this->where($map)->delete();
        return $result;
    }

}
