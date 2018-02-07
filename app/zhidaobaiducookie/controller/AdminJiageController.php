<?php
namespace app\zhidaobaiducookie\controller;

use cmf\controller\AdminBaseController;

use app\zhidaobaiducookie\model\ZhidaobaiducookModel;
use app\zhidaobaiducookie\service\PostService;

use think\Db;
use app\admin\model\ThemeModel;

/**
 * Class AdminIndexController
 * @package app\baiduzhanghao\controller
 * @adminMenuRoot(
 *     'name'   =>'百度帐号管理',
 *     'action' =>'index',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 30,
 *     'icon'   =>'user',
 *     'remark' =>'百度帐号管理'
 * )
 */
class AdminJiageController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();
        $where = [
            'create_time' => ['>=', 0],
            'delete_time' => 0
        ];
        $jiage=Db::name('zhidaobaiducook_jiage');
        $data=$jiage->where($where)->paginate(10);

        $this->assign('articles', $data->items());
        $this->assign("page", $data->render());


        return $this->fetch();
    }
    /**
     * 添加文章
     * @adminMenu(
     *     'name'   => '添加文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章',
     *     'param'  => ''
     * )
     */
    public function add()
    {
//        $themeModel        = new ThemeModel();
//        $articleThemeFiles = $themeModel->getActionThemeFiles('chongzhixiaofei/Index/index');
//        $this->assign('index_theme_files', $articleThemeFiles);
        return $this->fetch();
    }
    /**
     * 添加文章提交
     * @adminMenu(
     *     'name'   => '添加文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $data['create_time']=time();
            $jiage=Db::name('zhidaobaiducook_jiage');

            $jiage->insert($data);

            $this->success('添加成功!', url('AdminJiage/index'));
        }

    }
    /**
     * 编辑文章
     * @adminMenu(
     *     'name'   => '编辑文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        $jiage=Db::name('zhidaobaiducook_jiage');
        $post=$jiage->where('id', $id)->find();

        $this->assign('post', $post);

        return $this->fetch();
    }
    /**
     * 编辑文章提交
     * @adminMenu(
     *     'name'   => '编辑文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
            $id = $this->request->param('id', 0, 'intval');
            $data   = $this->request->param();

            $jiage=Db::name('zhidaobaiducook_jiage');

            $jiage->where('id',$id)->update($data);

            $this->success('保存成功!', url('AdminJiage/index'));

    }

    /**
     * 文章删除
     * @adminMenu(
     *     'name'   => '文章删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $data   = $this->request->param();
        $jiage=Db::name('zhidaobaiducook_jiage');

        if (isset($data['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $jiage->where('id',$id)->update(['delete_time' => time()]);
            $this->success("删除成功！", '');
        }

        if (isset($data['ids'])) {
            $ids     = $this->request->param('ids/a');

            $result  = $jiage->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);
            if ($result) {
                $this->success("删除成功！", '');
            }
        }
    }
}

