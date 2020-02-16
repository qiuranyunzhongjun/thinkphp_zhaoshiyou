<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        // 向当前client_id发送数据 
        //Gateway::sendToClient($client_id, "Hello $client_id\r\n");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\r\n");

        Gateway::sendToClient($client_id,json_encode([
            "type" =>0,
            "client_id" =>$client_id
        ]));
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
        // 向所有人发送 
        $message = json_decode($message);
        switch($message->type){
            case 0://初始化，需要绑定
                $fromid = $message->fromid;
                Gateway::bindUid($client_id, $fromid);
                Gateway::sendToUid($fromid, json_encode([
                    "type" =>'success',
                    "client_id" =>$client_id,
                    "fromid" =>$fromid
                ]));
                return;
            case 1://纯文字消息
            case 2://图片链接消息
            case 3://带表情的文字消息
            case 4://位置消息
            case 5://请求交换手机号消息
            case 6://发送收藏的室友的消息
            case 7://发送收藏的房源的消息
            case 8://房东上传的房源被收藏和取消收藏时发送的消息
                $fromid = $message->fromid;
                Gateway::sendToUid($fromid, json_encode([
                "type" =>'success',
                "fromid" =>'服务器'
                ]));
                // $message->time = time();
                if(Gateway::isUidOnline($message->toid)){
                    Gateway::sendToUid($message->toid, json_encode($message));
                }else{
                    Gateway::sendToClient($client_id,json_encode([
                        "type" =>10+$message->type,
                        "content" => $message->content
                    ]));
                }
                return;
            // case 6://响应交换手机号的请求
            //     if(Gateway::isUidOnline($message->toid)){
            //         Gateway::sendToUid($message->toid, json_encode($message));
            //     }
            //     return;
            default:
                
                $fromid = $message->fromid;
                Gateway::sendToUid($fromid, json_encode([
                "type" =>'success',
                "fromid" =>'服务器'
                ]));
                // $message->time = time();
                if(Gateway::isUidOnline($message->toid)){
                    Gateway::sendToUid($message->toid, json_encode($message));
                }else{
                    Gateway::sendToClient($client_id,json_encode([
                        "type" =>10+$message->type,
                        "content" => $message->content
                    ]));
                }
                return;
        }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 
       //GateWay::sendToAll("$client_id logout\r\n");
   }
}
