<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Loader;
use think\Request;

class Yuztcatch 
{
            
    public function index(){
        $data = Request::instance()->param();
        $nid = Db::name('old_house3')->insertGetId($data);
        return json(['code' => 200, 'data' => $nid]);
        
    }

    public function pic(){

    	$data = Request::instance()->param();
        $nid = Db::name('picture3')->insertGetId($data);
        return json(['code' => 200, 'data' => $nid]);
        
    }
    public function a(){
        // 
        set_time_limit(0);
        $thumb = Db::name('picture')->where('thumb','like','http%')->field('thumb,id')->limit(50)->select();
        // print_r($thumb);
        // exit;
        foreach ($thumb as $key => $value) {
            $r = $this->crabImage($value['thumb']);
            Db::name('picture')->where('id', $value['id'])->update(['thumb' => 'images/'.$r['file_name']]);
        }


        // $r = $this->crabImage('https://ke-image.ljcdn.com/hdic-frame/standard_4e665d5f-0274-476c-90ee-34ca83688314.png!m_fill,w_240,h_180,l_bk,f_jpg,ls_30?from=ke.com');

        echo 111;
        // print_r($r);
    }

    public function crabImage($imgUrl, $saveDir='./images/', $fileName=null){
        if(empty($imgUrl)){
            echo 1;
            return false;
        }
        //获取图片信息大小
        $imgSize = getImageSize($imgUrl);
        if(!in_array($imgSize['mime'],array('image/jpg', 'image/gif', 'image/png', 'image/jpeg'),true)){
            echo 2;

            return false;
        }
     
        //获取后缀名
        $_mime = explode('/', $imgSize['mime']);
        $_ext = '.'.end($_mime);
     
        if(empty($fileName)){  //生成唯一的文件名
            $fileName = uniqid(time(),true).$_ext;
        }
     
        //开始攫取
        ob_start();
        readfile($imgUrl);
        $imgInfo = ob_get_contents();
        ob_end_clean();
     
        if(!file_exists($saveDir)){
            mkdir($saveDir,0777,true);
        }
        $fp = fopen($saveDir.$fileName, 'a');
        $imgLen = strlen($imgInfo);    //计算图片源码大小
        $_inx = 2048;   //每次写入2k
        $_time = ceil($imgLen/$_inx);
        for($i=0; $i<$_time; $i++){
            fwrite($fp,substr($imgInfo, $i*$_inx, $_inx));
        }
        fclose($fp);
        return array('file_name'=>$fileName,'save_path'=>$saveDir.$fileName);
    }

} 	