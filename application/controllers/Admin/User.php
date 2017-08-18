<?php
/**
 * Created by PhpStorm.
 * User: symphp <symphp@foxmail.com>
 * Date: 2017/8/17
 * Time: 16:16
 */

class User extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Admin_model','Admin');
	}

	/**
	 * 个人资料
	 */
	public function info()
	{
		if (IS_POST) {
			$params['sex']     = $this->input->post('sex');
			$params['phone']   = $this->input->post('phone');
			$params['email']   = $this->input->post('email');
			//选择图片后上传
			if (!empty($_FILES['head_pic']['tmp_name'])) {
				$upload = $this->headUpload();
				if ($upload['success'] == false) {
					$error['msg'] = $upload['info'];
					return $this->error($error);
				} else {
					$params['head_pic'] = json_encode($upload['info']);
					//删除旧的头像
					if ($this->admin['head_pic']) {
						$aged_head = substr($this->admin['head_pic'],1);
						if(file_exists($aged_head))
							unlink($aged_head);
					}
				}
			}
			$where['id'] = $this->admin['id'];
			//更新个人资料
			$res = $this->Admin->_update($params,$where);
			if ($res == false) {
				$error['msg'] = '更新个人资料失败';
				return $this->error($error);
			} else {
				$success['msg'] = '更新个人资料成功！';
				$success['url'] = 'info';
				return $this->success($success);
			}
		} else {
			$data['admin']  = $this->admin;
			$data['selects'] = array(
				array('status' => '3', 'msg' => '保密'),
				array('status' => '2', 'msg' => '男'),
				array('status' => '1', 'msg' => '女')
			);
			$this->display('User/info',$data);
		}
	}

	/**
	 * 用户管理
	 */
	public function index()
	{
		$field = 'id,username,sex,phone,email,reg_time,status';
		$condition['status'] = 1;
		//查询出所有的用户
		$admins = $this->Admin->_get($field,$condition);
		if ($admins == false) {
			$error['msg'] = '不存在用户信息！';
			return $this->error($error);
		} else {
			$data['admins'] = $admins;
		}
		$this->display('User/index',$data);
	}

	/**
	 * 新增用户
	 */
	public function add()
	{
		if (IS_POST) {
			$params['sex']     = $this->input->post('sex');    //性别
			$params['phone']   = $this->input->post('phone');    //手机
			$params['email']   = $this->input->post('email');    //邮箱
			$params['username']= $this->input->post('username');    //用户名
			$params['reg_time']= time();
			$params['salt']    = rand(000000,999999);    //盐
			$params['password']= hashPass($this->input->post('password'),$params['salt']);

			//判断会员是否已经注册
			$res = $this->Admin->_getOne('id',['username' => $params['username']]);
			if ($res) {
				$error['msg'] = '用户名已存在！';
				return $this->error($error);
			}

			//选择图片后上传
			if (!empty($_FILES['head_pic']['tmp_name'])) {
				$upload = $this->headUpload();
				if ($upload['success'] == false) {
					$error['msg'] = $upload['info'];
					return $this->error($error);
				} else {
					$params['head_pic'] = json_encode($upload['info']);
					//删除旧的头像
					if ($this->admin['head_pic']) {
						$aged_head = substr($this->admin['head_pic'],1);
						if(file_exists($aged_head))
							unlink($aged_head);
					}
				}
			}
			//添加会员
			$res = $this->Admin->_add($params);
			if ($res == false) {
				$error['msg'] = '添加会员失败';
				return $this->error($error);
			} else {
				$success['msg'] = '添加会员成功';
				$success['url'] = '/admin/index';
				return $this->success($success);
			}
		} else {
			$data['selects'] = array(
				array('status' => '3', 'msg' => '保密'),
				array('status' => '2', 'msg' => '男'),
				array('status' => '1', 'msg' => '女')
			);
			$this->display('User/add',$data);
		}
	}

	/**
	 * 编辑用户资料
	 */
	public function edit()
	{
		if (IS_POST) {
			$id = $this->input->post('id')??'';

			if ($id == '') {
				$error['msg'] = '参数错误！';
				return $this->error($error);
			} else if ($id == 1 && $this->admin['id'] != 1) {
				$error['msg'] = '管理员信息不允许修改！';
				return $this->error($error);
			}

			$conditions['id'] = $id;

			/** -------------- 查询用户是否存在 ---------------- **/

			$admin = $this->Admin->_getOne('id,head_pic',$conditions);

			$params['sex']     = $this->input->post('sex');
			$params['phone']   = $this->input->post('phone');
			$params['email']   = $this->input->post('email');
			$params['username']= $this->input->post('username');
			//选择图片后上传
			if (!empty($_FILES['head_pic']['tmp_name'])) {
				$upload = $this->headUpload();
				if ($upload['success'] == false) {
					$error['msg'] = $upload['info'];
					return $this->error($error);
				} else {
					$params['head_pic'] = json_encode($upload['info']);
					//删除旧的头像
					if ($admin['head_pic']) {
						$aged_head = substr($admin['head_pic'],1);
						if(file_exists($aged_head))
							unlink($aged_head);
					}
				}
			}
			//更新个人资料
			$res = $this->Admin->_update($params,$conditions);
			if ($res == false) {
				$error['msg'] = '更新资料失败';
				return $this->error($error);
			} else {
				$success['msg'] = '更新资料成功！';
				$success['url'] = 'index';
				return $this->success($success);
			}
		} else {
			$id = $this->input->get('id')??'';
			if ($id == '') {
				$error['msg'] = '参数错误！';
				return $this->error($error);
			} else {
				$admin = $this->Admin->_getOne('*',['status' => 1,'id' => $id]);
				if (!empty($admin['head_pic'])) {
					$admin['head_pic'] = json_decode($admin['head_pic']);
				}
			}
			$data['admin'] = $admin;
			$data['selects'] = array(
				array('status' => '3', 'msg' => '保密'),
				array('status' => '2', 'msg' => '女'),
				array('status' => '1', 'msg' => '男')
			);
			$this->display('User/edit',$data);
		}
	}

	/**
	 * 删除用户(逻辑删除)
	 */
	public function del()
	{
		$id = $this->input->get('id')??'';
		if ($id == '') {
			$error['msg'] = '参数错误！';
			return $this->error($error);
		} else if ($id == 1) {
			$error['msg'] = '管理员账号不允许删除！';
			return $this->error($error);
		}
		$conditions['id'] = $id;
		/** ---------------- 逻辑删除 ----------------**/
		$params['status'] = 2;
		$res = $this->Admin->_update($params,$conditions);
		if ($res == false) {
			$error['msg'] = '删除失败';
			return $this->error($error);
		} else {
			$success['msg'] = '删除成功！';
			$success['url'] = 'index';
			return $this->success($success);
		}
	}

	/**
	 * 修改密码
	 */
	public function changePass()
	{
		if (IS_POST) {
			$old_pass = $this->input->post('old_pass');    //原密码
			$new_pass = $this->input->post('new_pass');    //新密码
			$confirm_pass = $this->input->post('confirm_pass');    //确认密码

			if ($new_pass !== $confirm_pass) {
				$error['msg'] = '新密码和确认密码不相同！';
				return $this->error($error);
			}

			if ($old_pass == $new_pass) {
				$error['msg'] = '新密码不能与旧密码相同！';
				return $this->error($error);
			}

			/** -------------- 判断原密码是否正确 ---------------- **/
			$admin_info = $this->Admin->checkUser($this->admin['username'],$old_pass);
			if ($admin_info == false) {
				$error['msg'] = '旧密码不正确！';
				return $this->error($error);
			}

			//加密新密码
			$params['password'] = hashPass($new_pass,$admin_info['salt']);
			$conditions['id'] = $this->admin['id'];
			$res = $this->Admin->_update($params,$conditions);

			if ($res == false) {
				$error['msg'] = '旧密码不正确！';
				return $this->error($error);
			} else {
				$success['msg'] = '修改密码成功！';
				$success['url'] = 'user';
				return $this->success($success);
			}
		} else {
			$this->display('User/pass');
		}
	}


	/**
	 * 头像上传
	 */
	public function headUpload()
	{
		$config['upload_path']      = './public/img/uploads/head_pic/';
		$config['allowed_types']    = 'gif|jpg|png|jpeg';
		$config['max_size']     = 2048;
		$config['file_name'] = '97Admin_' . time();

		if (!file_exists($config['upload_path'])) {
			mkdir($config['upload_path'],0777,true);
		}

		$this->load->library('upload', $config);
		if (!$this->upload->do_upload('head_pic')) {
			$data = array('success' => false,'info' => $this->upload->display_errors());
		} else {
			$data = array('success' => true,'info' => substr($config['upload_path'],1).$this->upload->data()['file_name']);
		}
		return $data;
	}
}