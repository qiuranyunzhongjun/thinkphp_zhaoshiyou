<?php

namespace Home\Controller;

use Common\Controller\HomeBaseController;

/**
 * 给外部提供的接口InterfaceController
 */
class InterfaceController extends HomeBaseController {

    public function index(){
        dump(dirname(__FILE__));
        dump(dirname(__DIR__));
    }
    //导出代码
    public function export(){
        $xlsName  = "User用户数据表";
        $xlsCell  = array(
            array('id','编号'),
            array('sex','性别'),
        );
        $xlsData  = M('user')->field('id,sex')->select();
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    //导出Excel的例子
    public function expUser($xlsName='', $xlsCell=array(), $xlsModel=''){
        $xlsName  = "User用户数据表";
        $xlsCell  = array(
            array('id','账号序列'),
            array('nickname','名字'),
            array('mobile','手机号'),
            array('status','状态'),
            array('addtime','创建时间'),
        );
        $xlsModel = M('Member');

        $xlsData  = $xlsModel->Field('id,nickname,mobile,status,addtime')->select();
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['status'] = 1 ? '正常':'锁定';
            $xlsData[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    //导入操作
    public function impUser(){

        if (!empty($_FILES)) {


            import('Org.Net.UploadFile');

            $config=array(
                'allowExts'=>array('xlsx','xls'),
                'savePath'=>'./Uploads/',
//                'saveRule'=>'time',
            );
            $upload = new \UploadFile($config);

            if (!$upload->upload()) {
                $this->error($upload->getErrorMsg());
            } else {
                $info = $upload->getUploadFileInfo();

            }

            vendor("PHPExcel.PHPExcel");

            $file_name=$info[0]['savepath'].$info[0]['savename'];
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数

            for($i=2;$i<=$highestRow;$i++)
            {
                $data['username'] = $data['mobile'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
                $data['nickname'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                $data['status'] =1;
                $data['group'] =1;
                $data['addtime']= $data['updatetime'] = time();
                M('Member')->add($data);
            }
            $this->success('导入成功！');
        }else
        {
            $this->error("请选择上传的文件");
        }

    }

    //导出操作
    public function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle.date('_Ymd_His');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));//第一行标题
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}