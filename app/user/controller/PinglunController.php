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

class PinglunController extends UserBaseController
{
    function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 个人中心评论管理
     */
    public function index()
    {
        $editData = new UserModel();
        $data = $editData->pinglun();
        $user = cmf_get_current_user();

        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }
    public function select()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 添加评论
     */
    public function add()
    {
        $tiwen_url='';
        $tiwen_title='';
        //从提问转过来
        $data = $this->request->param();
        if(isset($data['tiwen_id'])) {
            $tiwen_id = $data['tiwen_id'];
            $taskQuery = Db::name('zhidaotaskdata');
            $task = $taskQuery->field('title,return_url')->where('pinglun_id', $tiwen_id)->find();
            $tiwen_url=$task['return_url'];
            $tiwen_title=base64_decode($task['title']);
        }

        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("user");
        $usermobile=$userQuery->where('id',$userId)->find();
        $user_mobile=$usermobile['mobile'];

        $cook=Db::name('zhidaobaiducook');
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.user_mobile' => $user_mobile
        ];
        $mybaidu = $cook->alias('a')->join('zhidaobaiducook_jiage w','a.jiage_id = w.id')->field('a.id,w.title')->where($where)->order('a.id desc')->select();

        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->where(array('id'=>$user['id']))->find();
        $this->assign('mybaidu',count($mybaidu)>0?$mybaidu:'');
        $this->assign('myscore',$coin['score']);
        $this->assign('tiwen_url',$tiwen_url);
        $this->assign('tiwen_title',$tiwen_title);
        $jiageQuery=Db::name('user_jiage')->where('id',1)->find();
        $this->assign("jiage", $jiageQuery['huida']);
        $this->assign("jiage2", $jiageQuery['huida2']);
        return $this->fetch();
    }

    public function edit()
    {
        $data = $this->request->param();
        $user = cmf_get_current_user();

        $taskQuery=Db::name('zhidaotaskdata');
        $pinglunQuery=Db::name('pinglun_post');
        $where['pinglun_id']=$data['id'];
        $where['return_code']=['in','0,2,3'];
        $pinglun=$pinglunQuery->field('post_url,post_title')->where('id',$data['id'])->find();
        $data=$taskQuery->field('id,content')->where($where)->select();

        $this->assign($user);
        $this->assign('get_url',$pinglun['post_url']);
        $this->assign('title',$pinglun['post_title']);
        $this->assign("lists", $data);

        return $this->fetch();
    }

    public function tiwenedit()
    {
        $data = $this->request->param();
        $user = cmf_get_current_user();

        $taskQuery=Db::name('zhidaotaskdata');
        $pinglunQuery=Db::name('pinglun_post');
        $where['pinglun_id']=$data['id'];
        $where['return_code']=['in','0,2,3'];

        $data=$taskQuery->field('id,title,content')->where($where)->find();

        $this->assign($user);
        $this->assign('post',$data);

        return $this->fetch();
    }
    /**
     * 批量添加评论
     */
    public function piliangadd()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->where(array('id'=>$user['id']))->find();

        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("user");
        $usermobile=$userQuery->where('id',$userId)->find();
        $user_mobile=$usermobile['mobile'];

        $cook=Db::name('zhidaobaiducook');
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.user_mobile' => $user_mobile
        ];
        $mybaidu = $cook->alias('a')->join('zhidaobaiducook_jiage w','a.jiage_id = w.id')->field('a.id,w.title')->where($where)->order('a.id desc')->select();
        $this->assign('mybaidu',count($mybaidu)>0?$mybaidu:'');
        $this->assign('myscore',$coin['score']);
        $jiageQuery=Db::name('user_jiage')->where('id',1)->find();
        $this->assign("jiage", $jiageQuery['huida']);
        $this->assign("jiage2", $jiageQuery['huida2']);
        return $this->fetch();
    }

    public function pilianglist(){
        $data = $this->request->param();
        $key=urlencode($data['post_title']);
        $pram=['key'=>$key,'ssyq'=>4];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);
        echo $result[0];
    }

    public function addPost()
    {
        $data = $this->request->param();
        $editData = new UserModel();
        $res=$editData->pinglunadd($data);
        if(strpos($res, "err:") !== false){
            $re=explode(':',$res);
            $this->error('添加失败！内容包含禁止词语【'.$re[1].'】');
        }
        $this->success('添加成功！', url('user/pinglun/index'));
    }

    public function editPost()
    {
        $data = $this->request->param();
        $taskQuery=Db::name('zhidaotaskdata');
        $pinglunQuery=Db::name('pinglun_post');
        $CookieQuery=Db::name('zhidaobaiducook');
        //禁词验证
        $jinciQuery=Db::name('pinglun_jinci_post');
        $jinci = $jinciQuery->field('post_title')->where(['delete_time'=>0])->select();
        foreach($data['content'] as $v){
            //内容禁词检测
            foreach ($jinci as $val) {
                if (strpos($v, $val['post_title']) !== false) {
                    $this->error('添加失败！内容包含禁止词语【'.$val['post_title'].'】');
                }
            }
        }

        foreach($data['id'] as $k=>$v) {
            //读取任务信息
            $task = $taskQuery->field('user_id,content_id,is_ok,zhidao,pinglun_id,get_url,title,content')->where('id', $v)->find();
            //标记失败任务
            $taskQuery->where('id', $v)->update(['delete_time'=>time()]);
            //更新任务时间
            $pinglunQuery->where('id',$task['pinglun_id'])->update(['create_time'=>time()]);
            //任务cookie类型
            $cookie_type=$pinglunQuery->field('cookie_type')->where('id',$task['pinglun_id'])->find();
            //随机百度cookie
            $baidu_cookie = $CookieQuery->field('baidu_cookie')->where(['cookie_fail'=>['lt', 10],'type'=>$cookie_type['cookie_type'],'delete_time'=>0])->order('rand()')->limit(1)->find();
            //生成任务列表
            $renwudata = ['user_id'=>$task['user_id'],'is_ok'=>$task['is_ok'],'title'=>$task['title'],'pinglun_id' => $task['pinglun_id'], 'content_id' => $task['content_id'], 'zhidao' => $task['zhidao'], 'get_url' => $task['get_url'], 'content' => base64_encode($data['content'][$k]), 'baidu_cookie' => $baidu_cookie['baidu_cookie'], 'create_time' => time()];
            $taskQuery->insert($renwudata);
        }

        $this->success('添加成功！', url('user/pinglun/index'));
    }

    public function piliangaddPost(){
        $data = $this->request->param();
        $editData = new UserModel();
        $res=$editData->pinglunPiliangadd($data);
        if(strpos($res, "err:") !== false){
            $re=explode(':',$res);
            $this->error('添加失败！内容包含禁止词语【'.$re[1].'】');
        }
        $this->success('添加成功！', url('user/pinglun/index'));
    }


    public function pinglunzhixing()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $data = $this->request->param();
        if(isset($data['id'])) {
            //当前任务名称
            $PinglunQuery = Db::name("pinglun_post");
            $title = $PinglunQuery->field('post_title')->where('id', $data['id'])->find();
            $this->assign('title',$title['post_title']);
        }

        $editData = new UserModel();
        $data = $editData->pinglunzhixing($data);
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }

    public function pinglunshibai(){
        $data = $this->request->param();
        $tiwenQuery=Db::name('pinglun_post');
        $tiwenQuery->where('id',$data['id'])->update(['create_time'=>time()]);
        //获取失败评论信息
        $renwuQuery=Db::name('zhidaotaskdata');
        $pinglun=$renwuQuery->field('id,pinglun_id,content_id,get_url,content')->where(['pinglun_id'=>$data['id'],'delete_time'=>0,'return_code'=>[['neq',1],['neq','']]])->select();

        $CookieQuery=Db::name('zhidaobaiducook');
        foreach($pinglun as $v) {
            //标记失败任务
           $renwuQuery->where('id', $v['id'])->update(['delete_time'=>time()]);
            //随机百度cookie
            $baidu_cookie = $CookieQuery->field('baidu_cookie')->where(['cookie_fail'=>['lt', 10],'delete_time'=>0])->order('rand()')->limit(1)->find();

            //生成任务列表
            $renwudata = ['pinglun_id' => $v['pinglun_id'], 'content_id' => $v['content_id'], 'zhidao' => 'hd', 'get_url' => $v['get_url'], 'content' => $v['content'], 'baidu_cookie' => $baidu_cookie['baidu_cookie'], 'create_time' => time()];
            $renwuQuery->insert($renwudata);
        }

        $this->success('添加成功！', url('user/pinglun/index'));
    }
    /**
     * 评论内容数量
     */
    public function content_count()
    {
        $data = $this->request->param();
        $text_path=$data['text_path'];  //txt文件路径

        $myFile = file('.'.$text_path);
        $count=count($myFile);
        $data=[];
        foreach($myFile as $v){
            $str = mb_convert_encoding($v, 'utf-8', 'gbk');
            $str = trim($str);
            $str = str_replace(array("\r\n", "\r", "\n", "\t", "　","'"), "", $str);//去除换行符

            if ($str) {
                $data[]=$str;
            }
        }
        $result=['count'=>count($data),'data'=>$data];
        return json_encode($result);
    }
    /* 判断url重新获取帐号*/
    public function url_mybaidu(){
        $data = $this->request->param();
        $userId               = cmf_get_current_user_id();
        $url=$data['url'];
        //查询url是否存在
        $tiwen=Db::name('pinglun_post')->field('cookie_id')->where(['user_id'=>$userId,'post_type'=>2,'post_url'=>$url])->order('id desc')->limit(1)->find();
        if($tiwen){
            $where['a.id']=['<>',$tiwen['cookie_id']];
        }
        $user_mobile=session('user.mobile');
        $cook=Db::name('zhidaobaiducook');
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.user_mobile' => $user_mobile
            //'a.id'=>['<>',$tiwen['cookie_id']]
        ];
        $mybaidu = $cook->alias('a')->join('zhidaobaiducook_jiage w','a.jiage_id = w.id')->field('a.id,w.title')->where($where)->order('a.id desc')->select();
        echo '<select name="cookie_id" id="selectCookie" class="form-control">';
		foreach($mybaidu as $v){
		    echo '<option value="'.$v['id'].'">[ ID:'.$v['id'].' ] '.$v['title'].'</option>';
        }
        echo '</select>';
    }
    /**
     * 个人中心百度帐号购买
     */
    public function baidu()
    {
        $cook=Db::name('zhidaobaiducook');
        $where = [
            'create_time' => ['>=', 0],
            'delete_time' => 0,
            'user_mobile' => 0
        ];
        $data = $cook->where($where)->order('id desc')->paginate(10);
        $jiage=Db::name('zhidaobaiducook_jiage');
        $items=array();
        foreach($data->items() as $v){
            $jiagetitle=$jiage->field('title,jiage')->where('id',$v['jiage_id'])->find();
            $v['jiage_name']=$jiagetitle['title'];
            $v['jiage']=$jiagetitle['jiage'];
            $items[]=$v;
        }
        $user = cmf_get_current_user();
        $userQuery=Db::name('user');
        $coin=$userQuery->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);
        $this->assign($user);
        $this->assign("page", $data->render());
        $this->assign("lists", $items);
        return $this->fetch();
    }
    public function mybaidu()
    {
        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("user");
//        $usermobile=$userQuery->field('mobile')->where('id',$userId)->cache(true)->find();
//        $user_mobile=$usermobile['mobile'];
        $user_mobile=session('user.mobile');

        $cook=Db::name('zhidaobaiducook');
        $where = [
            'create_time' => ['>=', 0],
            'delete_time' => 0,
            'user_mobile' => $user_mobile
        ];
        $data = $cook->where($where)->order('id desc')->paginate(10);
        $jiage=Db::name('zhidaobaiducook_jiage');
        $items=array();
        foreach($data->items() as $v){
            $jiagetitle=$jiage->field('title,jiage')->where('id',$v['jiage_id'])->find();
            $v['jiage_name']=$jiagetitle['title'];
            $v['jiage']=$jiagetitle['jiage'];
            $items[]=$v;
        }
        $user = cmf_get_current_user();

        $this->assign($user);
        $this->assign("page", $data->render());
        $this->assign("lists", $items);
        return $this->fetch();
    }
    public function baidugoumai()
    {
        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("user");
//        $usermobile=$userQuery->field('mobile')->where('id',$userId)->cache(true)->find();
//        $user_mobile=$usermobile['mobile'];
        $user_mobile=session('user.mobile');

        $data = $this->request->param();
        $cook_id=$data['id'];
        $cookQuery=Db::name('zhidaobaiducook');

        //查询价格
        $jiage_id=$cookQuery->field('jiage_id')->where('id',$cook_id)->find();

        $jiage=Db::name('zhidaobaiducook_jiage')->field('jiage,title')->where('id',$jiage_id['jiage_id'])->find();

        //积分减少
        $result=$userQuery->where('id',$userId)->setDec('score',$jiage['jiage']);

        //增加明细记录
        $userMoneyQuery            = Db::name("user_money_log");
        $data2=[];
        $data2['user_id']=$userId;
        $data2['create_time']=time();
        $data2['type']=2;
        $data2['post_title']='购买百度【'.$jiage['title'].'】';
        $data2['score']=$jiage['jiage'];
        $userMoneyQuery->insert($data2);

        $result=$cookQuery->where('id',$cook_id)->update(['user_mobile'=>$user_mobile]);


        $this->success('购买成功！', url('user/pinglun/mybaidu'));
    }
    /**
     * 个人中心提问管理
     */
    public function tiwen()
    {
        $editData = new UserModel();
        $data = $editData->pinglunTiwen();
//        //获取任务执行情况
//        $taskQuery=Db::name('zhidaotaskdata');
//        $list=[];
//        foreach($data['lists'] as $v){
//            $return_code=$taskQuery->field('return_code,return_url')->where(['pinglun_id'=>$v['id'],'delete_time'=>0])->order('id desc')->limit(1)->find();
//            $v['return_code']=$return_code['return_code'];
//            $v['return_url']=$return_code['return_url'];
//            $list[]=$v;
//        }

        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }
    /**
     * 添加提问
     */
    public function tiwenadd()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);

        //获取上次的百度Cookie
        $tiwenQuery=Db::name('pinglun_post');
        $post_cookie='';
        $baidu_cookie=$tiwenQuery->field('post_cookie')->where(['user_id'=>$user['id'],'post_type'=>2])->order('id desc')->limit(1)->find();
        if($baidu_cookie['post_cookie']){
            $post_cookie=$baidu_cookie['post_cookie'];
        }

        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("user");
//        $usermobile=$userQuery->field('mobile')->where('id',$userId)->cache(true)->find();
//        $user_mobile=$usermobile['mobile'];
        $user_mobile=session('user.mobile');

        $cook=Db::name('zhidaobaiducook');
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.user_mobile' => $user_mobile
        ];
        $mybaidu = $cook->alias('a')->join('zhidaobaiducook_jiage w','a.jiage_id = w.id')->field('a.id,w.title')->where($where)->order('a.id desc')->select();
        $this->assign('mybaidu',count($mybaidu)>0?$mybaidu:'');
        $this->assign('post_cookie',$post_cookie);
        $jiageQuery=Db::name('user_jiage')->where('id',1)->find();
        $this->assign("jiage", $jiageQuery['tiwen']);
        $this->assign("jiage2", $jiageQuery['tiwen2']);
        return $this->fetch();
    }
    public function tiwenaddPost()
    {
        $data = $this->request->param();
        $editData = new UserModel();
        $res=$editData->pingluntiwenadd($data);
        if(strpos($res, "err:") !== false){
            $re=explode(':',$res);
            $this->error('添加失败！内容包含禁止词语【'.$re[1].'】');
        }
        $this->success('添加成功！', url('user/pinglun/tiwen'));
    }
    public function tiweneditPost()
    {
        $data = $this->request->param();
        $taskQuery=Db::name('zhidaotaskdata');
        $pinglunQuery=Db::name('pinglun_post');
        $CookieQuery=Db::name('zhidaobaiducook');
        //禁词验证
        $jinciQuery=Db::name('pinglun_jinci_post');
        $jinci = $jinciQuery->field('post_title')->where(['delete_time'=>0])->select();
        foreach ($jinci as $val) {
            if (strpos($data['post_title'], $val['post_title']) !== false) {
                $this->error('添加失败！内容包含禁止词语【'.$val['post_title'].'】');
            }
            if (strpos($data['post_content'], $val['post_title']) !== false) {
                $this->error('添加失败！内容包含禁止词语【'.$val['post_title'].'】');
            }
        }

        //读取任务信息
        $task = $taskQuery->field('user_id,content_id,zhidao,pinglun_id')->where('id', $data['id'])->find();
        //标记失败任务
        $taskQuery->where('id', $data['id'])->update(['delete_time'=>time()]);
        //更新任务时间
        $pinglunQuery->where('id',$task['pinglun_id'])->update(['create_time'=>time()]);
        //任务cookie类型
        $cookie_type=$pinglunQuery->field('cookie_type')->where('id',$task['pinglun_id'])->find();
        //随机百度cookie
        $baidu_cookie = $CookieQuery->field('baidu_cookie')->where(['cookie_fail'=>['lt', 10],'type'=>$cookie_type['cookie_type'],'delete_time'=>0])->order('rand()')->limit(1)->find();
        //生成任务列表
        $renwudata = ['user_id'=>$task['user_id'],'title'=>base64_encode($data['post_title']),'pinglun_id' => $task['pinglun_id'], 'zhidao' => $task['zhidao'], 'content' => base64_encode($data['post_content']), 'baidu_cookie' => $baidu_cookie['baidu_cookie'], 'create_time' => time()];
        $taskQuery->insert($renwudata);

        $this->success('添加成功！', url('user/pinglun/tiwen'));
    }

    public function tiwenshibai(){
        $data = $this->request->param();
        //获取提问信息
        $renwuQuery=Db::name('pinglun_post');
        $pinglun=$renwuQuery->field('post_title,post_content,post_cookie')->where('id',$data['id'])->find();
        $renwuQuery->where('id',$data['id'])->update(['create_time'=>time()]);
        //失败任务标记
        $taskQuery=Db::name('zhidaotaskdata');
        $taskQuery->where('pinglun_id',$data['id'])->update(['delete_time'=>time()]);
        //插入新提问任务
        $taskData = ['pinglun_id' => $data['id'], 'zhidao' => 'tw', 'title' => base64_encode($pinglun['post_title']), 'content'=>$pinglun['post_content'],'baidu_cookie' => $pinglun['post_cookie'], 'create_time' => time()];

        $taskQuery->insert($taskData);

        $this->success('添加成功！', url('user/pinglun/tiwen'));
    }
}