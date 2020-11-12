<?php

namespace app\interfaces\controller;

use think\Controller;
use think\Db;
use think\Request;


class Uploadvideo extends Controller
{
	private $filepath = ROOT_PATH . 'public' . DS . 'uploads/fix'; //上传目录
    private $tmpPath; //PHP文件临时目录
    private $blobNum; //第几个文件块
    private $totalBlobNum; //文件块总数
    private $fileName; //文件名

    public function __construct(){
        $this->tmpPath = $_FILES['file']['tmp_name'];
        $this->blobNum = $_POST['blob_num'];
        $this->totalBlobNum = $_POST['total_blob_num'];
        $this->fileName = $_POST['file_name'];
    }

    public function uploadvideo(){
        // echo $this->filepath;
        $this->moveFile();
        $this->fileMerge();
    }

    private function moveFile(){
        $this->touchDir();
        $filename = $this->filepath.'/'. $this->fileName.'__'.$this->blobNum;
        move_uploaded_file($this->tmpPath,$filename);
    }

    private function fileMerge(){
        $files1 = scandir($this->filepath);
        $total = 0;
        foreach($files1 as $key => $value) {
            if(strpos($value,$this->fileName) !== false && $value != $this->fileName){
                $total++;
            };
        }

        if($total == $this->totalBlobNum){
            $blob = '';
            for($i=1; $i<= $this->totalBlobNum; $i++){
                $blob .= file_get_contents($this->filepath.'/'. $this->fileName.'__'.$i);
            }
            file_put_contents($this->filepath.'/'. $this->fileName,$blob);



            $this->deleteFileBlob();
        }

        $this->apiReturn($total);
    }

    private function deleteFileBlob(){
        for($i=1; $i<= $this->totalBlobNum; $i++){
            @unlink($this->filepath.'/'. $this->fileName.'__'.$i);
        }
    }


    public function apiReturn($total){

        $data['code'] = 0;
        $data['msg'] = '服务器上找不到此文件';

        if($total == $this->totalBlobNum){
            if(file_exists($this->filepath.'/'. $this->fileName)){
                $mb_path = 'newload/'.date('Ymd').'/'. $this->fileName;
                // 投入阿里云
                if(alioss($mb_path,$this->filepath.'/'. $this->fileName)){
                    $data['code'] = 2;
                    $data['msg'] = 'success';
                    $data['file_path'] = '/'.$mb_path;
                }else{
                    $data['code'] = 0;
                    $data['msg'] = '上传阿里云出错';
                }
            }
        }else{
            if(file_exists($this->filepath.'/'. $this->fileName.'__'.$this->blobNum)){
             $data['code'] = 1;
             $data['msg'] = 'waiting for all';
             $data['file_path'] = '';
            }
        }
        header('Content-type: application/json');
        echo json_encode($data);
    }
    private function touchDir(){
        if(!file_exists($this->filepath)){
            return mkdir($this->filepath);
        }
    }

}
