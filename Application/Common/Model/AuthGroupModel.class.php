<?php

namespace Common\Model;

use Common\Model\BaseModel;

/**
 * 权限规则model
 */
class AuthGroupModel extends BaseModel {

    protected $_validate = array(
        array('title','require','用户组名不能为空')
    );
    
    
    /**
     * 传递主键id删除数据
     * @param  array   $map  主键id
     * @return boolean       操作是否成功
     */
    public function deleteData($map) {
        $result = $this->where($map)->delete();
        $group_map = array(
            'group_id' => $map['id']
        );
        // 删除关联表中的组数据
        D('AuthGroupAccess')->deleteData($group_map);
        return $result;
    }

}
