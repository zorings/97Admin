<?php
/**
 * Created by PhpStorm.
 * User: symphp
 * Date: 2017/7/6
 * Time: 14:23
 */
class Index extends Admin_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Admin_model','Admin');
	}

	/**
	 * 后台首页
	 */
	public function index()
	{
		//分页
		$page = isset($_GET['page'])?$_GET['page']:$this->page;
		$pageSize = isset($_GET['pageSize'])?$_GET['pageSize']:$this->pageSize;

		$conditions = [];
		//判断是否是超级管理员
		$admin = $this->Admin->SuperAdmin($this->admin['id']);
		if (!$admin) {
			$conditions['admin_id'] = $this->admin['id'];
		}

		$data['logs']  = $this->AdminLog->_get('*',$conditions,[],[],['page' => $page, 'count' => $pageSize]);
		$data['count'] = $this->AdminLog->_count($conditions);

		//继承分页样式
		$configPage = $this->configPage;
		$configPage['total_rows'] = $data['count'];
		$configPage['per_page'] = $pageSize;
		//生成分页
		$this->pagination->initialize($configPage);
		$data['page'] = $this->pagination->create_links();

		$this->display('index',$data);
	}
}
