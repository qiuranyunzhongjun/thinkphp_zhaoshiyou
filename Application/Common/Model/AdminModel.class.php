<?php

namespace Common\Model;

use Common\Model\BaseModel;

/**
 * ModelName
 */
class AdminModel extends BaseModel {

    // 自动验证
    protected $_validate = array(
        array('username', 'require', '用户名不能为空', 1), // 验证字段必填
        array('username', '', '用户名不能重复', 1, 'unique', 1), // 验证字段必填
        array('password', 'require', '密码不能为空', 1, '', 1), // 验证字段必填
    );
    // 自动完成
    protected $_auto = array(
        array('password', 'think_ucenter_md5', 1, 'function'), // 对password字段在新增的时候使md5函数处理
        array('register_time', 'time', 1, 'function'), // 对date字段在新增的时候写入当前时间戳
    );


    /**
     * 删除数据
     * @param   array   $map    where语句数组形式
     * @return  boolean         操作是否成功
     */
    public function deleteData($map) {
        die('禁止删除用户');
    }

    public function deleteAdminData($map) {
        if (empty($map)) {
            return false;
        }
        $result = $this->where($map)->delete();

        $uid_map = array(
            'uid' => $map['id']
        );
        // 删除关联表中的组数据
        D('AuthGroupAccess')->deleteData($uid_map);

        return $result;
    }

}
