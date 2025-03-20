<?php

namespace plugin\webniu\app\web\controller;

use support\Request;
use support\Response;
use plugin\webniu\app\model\Article;
use plugin\webniu\app\model\ArticleCategory; 
use support\exception\BusinessException;

/**
 * 系统公告 
 */
class ArticleController extends Crud
{
    
    /**
     * @var Article
     */
    protected $model = null;

    /**
     * 不需要鉴权的方法
     * @var array
     */
    protected $noNeedAuth = ['catselect'];

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Article;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('article/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('article/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('article/update');
    }

    /**
     * 分类列表
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function catselect(Request $request): Response
    {
        $this->model    = new ArticleCategory; 
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 分类插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function catinsert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $this->model    = new ArticleCategory; 
            $data           = $this->insertInput($request);
            $id             = $this->doInsert($data); 
            return $this->json(0, '添加成功', ['id' => $id]);
        }
    }

    /**
     * 分类更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function catupdate(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $this->model    = new ArticleCategory; 
            [$id, $data] = $this->updateInput($request);
            $this->doUpdate($id, $data);
            return $this->json(0,'更新成功'); 
        } 
    }

    /**
     * 分类删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function catdelete(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $this->model= new ArticleCategory; 
            $ids        = $this->deleteInput($request);
            $this->doDelete($ids);
            return $this->json(0,'删除成功');
        } 
    }

}
