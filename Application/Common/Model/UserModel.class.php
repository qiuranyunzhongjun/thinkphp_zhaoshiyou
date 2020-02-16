<?php

namespace Common\Model;

use Common\Model\BaseModel;

/**
 * ModelName
 */
class UserModel extends BaseModel {

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

    //    获取用户数据
    public function getAllData() {
        $data = $this
                ->field('id,name,sex,shangquan,ditie,checkintime,checkoutime,tenant_long,age,work,budget,phone,weixin,personality,motion,entertainment')
                ->select();
        // 获取第一条数据
        $first = $data[0];
        $first['title'] = array();
        $user_data[$first['id']] = $first;
        // 组合数组
        foreach ($data as $k => $v) {
            foreach ($user_data as $m => $n) {
                $uids = array_map(function($a) {
                    return $a['id'];
                }, $user_data);
                if (!in_array($v['id'], $uids)) {
                    $v['title'] = array();
                    $user_data[$v['id']] = $v;
                }
            }
        }
        // 组合管理员title数组
        foreach ($user_data as $k => $v) {
            foreach ($data as $m => $n) {
                if ($n['id'] == $k) {
                    $user_data[$k]['title'][] = $n['title'];
                }
            }
            $user_data[$k]['title'] = implode('、', $user_data[$k]['title']);
        }
        // 管理组title数组用顿号连接
        return $user_data;
    }
    
    
     /**
     * 修改数据
     * @param   array   $map    where语句数组形式
     * @param   array   $data   数据
     * @return  boolean         操作是否成功
     */
    public function editData($map,$data){
        // 去除键值首位空格
        foreach ($data as $k => $v) {
            $data[$k]=trim($v);
        }
        
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            return false;
        }else{
            // 验证通过
            $result=$this
                ->where(array($map))
                ->save($data);
            return $result;
        }
    }
    
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
