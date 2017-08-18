<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\UserBaseController;
use app\user\model\UserModel;
use think\Db;
use think\Model;

class GuanjianciController extends UserBaseController
{
    function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 个人中心关键词管理
     */
    public function index()
    {
        $editData = new UserModel();
        $data = $editData->guanjianci();
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }

    /**
     * 添加关键词
     */
    public function add()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->field('coin')->where(array('id'=>$user['id']))->find();
        $this->assign('mycoin',$coin['coin']);
        return $this->fetch();
    }

    public function addPost()
    {
        $data = $this->request->param();
        $editData = new UserModel();
        $editData->guanjianciadd($data);

        $this->success('添加成功！', url('user/guanjianci/index'));
    }

    public function zhishu()
    {
        $data = $this->request->param();
        $title=urlencode($data['post_title']);
        $key_json=file_get_contents("http://api.91cha.com/index?key=34909fe411a14cbd9f0539cf27fbf6a3&kws=".$title);
        $key=json_decode($key_json,true);
        if($key['state']==1){
            echo "百度指数：".$key['data'][0]['allindex']."，&nbsp;&nbsp;360指数：".$key['data'][0]['so360index']."&nbsp;&nbsp;(此处显示仅为参考，请以实际为准。)";
        }else{

            echo "未查询到指数信息";
        }
//        echo $key_json;
//        echo $str;
        //$this->success('添加成功！', url('user/fapiao/index'));
    }

    public function listbaidu()
    {
        $data = $this->request->param();
        $key=urlencode($data['post_title']);
        $pram=['key'=>$key,'ssyq'=>1];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);
        echo $result[0];
    }
    public function listsogou()
    {
        $data = $this->request->param();
        $key=urlencode($data['post_title']);
        $pram=['key'=>$key,'ssyq'=>2];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);
        echo $result[0];
    }
    public function listso()
    {
        $data = $this->request->param();
        $key=urlencode($data['post_title']);
        $pram=['key'=>$key,'ssyq'=>3];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);
        echo $result[0];
    }

    public function paiming()
    {
        $data = $this->request->param();
        $title=urlencode($data['post_title']);
        $url=$data['post_url'];
        $ssyq_value=$data['ssyq'];
        $num=$data['num'];
        switch ($ssyq_value){
            case 1:
                $ssyq="baidu";
                break;
            case 2:
                $ssyq="sogou";
                break;
            case 3:
                $ssyq="so360";
                break;
        }
        $key_json=file_get_contents("http://if.aizhan.com:9010/AizhanSEO/keywordrank_request/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/10");
        $key=json_decode($key_json);

        $paiming_json=file_get_contents("http://if.aizhan.com:9010/AizhanSEO/keywordrank_response/".$key."/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/100");
        $paiming=json_decode(json_decode($paiming_json),true);
$i=0;
        while($paiming['IsCompleted']==false){
            sleep(2);
            $key_json=file_get_contents("http://if.aizhan.com:9010/AizhanSEO/keywordrank_request/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/10");
            $key=json_decode($key_json);

            $paiming_json=file_get_contents("http://if.aizhan.com:9010/AizhanSEO/keywordrank_response/".$key."/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/100");
            $paiming=json_decode(json_decode($paiming_json),true);
//            echo $i;
            if($i==3){
                echo "未查询到,".$num;
                exit;
            }
            $i++;

        }
//        var_dump($paiming);
        $str=$paiming['ResponseList'][0]['Rank'];
        $str=str_replace("名","",str_replace("第","",$str));
        echo $str.",".$num;
    }
}