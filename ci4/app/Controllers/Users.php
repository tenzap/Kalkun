<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------
namespace App\Controllers;

use App\Libraries\MYController;

/**
 * Users Class
 *
 * @package		Kalkun
 * @subpackage	Users
 * @category	Controllers
 */
class Users extends MYController {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();

		$this->User_model = model('UserModel');
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * Display list of all users
	 *
	 * @access	public
	 */
	function index()
	{
		helper('i18n');
		$data['title'] = tr_raw('User', 'default');
		$this->load->library('pagination');
		$config['base_url'] = site_url().'/users/index/';
		$config['total_rows'] = $this->User_model->getUsers(array('option' => 'all'))->num_rows();
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span class="current_page">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 3;

		$this->pagination->initialize($config);
		$data['pagination_links'] = $this->pagination->create_links();
		$param = array('option' => 'paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3, 0));

		$data['main'] = 'main/users/index';
		if ($_POST)
		{
			$data['users'] = $this->User_model->getUsers(array('option' => 'search'));
		}
		else
		{
			$data['users'] = $this->User_model->getUsers($param);
		}

		helper('kalkun');
		if ($this->request->isAjax())
		{
			return view('main/users/users_list', $data);
		}
		else
		{
			return view('main/layout', $data);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add user
	 *
	 * Display Add/Update an user form
	 *
	 * @access	public
	 */
	function add_user()
	{
		helper('form');
		$type = $this->request->getGet('type');
		$data['tmp'] = '';

		if ($type === 'edit')
		{
			$id_user = $this->request->getGet('param1');
			$data['users'] = $this->User_model->getUsers(array('option' => 'by_iduser', 'id_user' => $id_user));
		}
		return view('main/users/add_user', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add user process
	 *
	 * Process the add/update user
	 *
	 * @access	public
	 */
	function add_user_process()
	{
		helper('kalkun');
		helper('i18n');
		$this->User_model->adduser();

		if ($this->request->getPost('id_user'))
		{
			if (config('Kalkun')->demo_mode
				&& intval($this->request->getPost('id_user')) === 1)
			{
				if ($this->request->getPost('username') !== 'kalkun')
				{
					$return_msg = [
						'type' => 'error',
						'msg' => tr_raw('Modification of username of "kalkun" user forbidden in demo mode. Username was restored.'),
					];
				}
				if ($this->request->getPost('level') !== 'admin')
				{
					$return_msg = [
						'type' => 'error',
						'msg' => tr_raw('Changing role of "kalkun" user forbidden in demo mode. Role was restored.'),
					];
				}
			}
			if ( ! isset($return_msg))
			{
				$return_msg = [
					'type' => 'info',
					'msg' => tr_raw('User updated successfully.'),
				];
			}
		}
		else
		{
			$return_msg = [
				'type' => 'info',
				'msg' => tr_raw('User added successfully.'),
			];
		}

		// Return status
		return $this->response->setJSON($return_msg);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete user
	 *
	 * Delete an user
	 * All data related to deleted user (sms, phonebook, preference, etc) also deleted
	 *
	 * @access	public
	 */
	function delete_user()
	{
		$uid = $this->request->getPost('id_user');

		// get and delete all user_outbox
		$res = $this->Message_model->get_messages(array('uid' => $uid, 'type' => 'outbox'));
		foreach ($res->getResult() as $tmp)
		{
			$param = array('type' => 'single', 'option' => 'outbox', 'id_message' => $tmp->id_outbox);
			$this->Message_model->delMessages($param);
		}

		// get and delete all user_inbox
		$res = $this->Message_model->get_messages(array('uid' => $uid, 'type' => 'inbox'));
		foreach ($res->getResult() as $tmp)
		{
			$param = array('type' => 'single', 'option' => 'permanent', 'source' => 'inbox', 'id_message' => $tmp->id_inbox);
			$this->Message_model->delete_messages($param);
		}

		// get and delete all user_sentitems
		$res = $this->Message_model->get_messages(array('uid' => $uid, 'type' => 'sentitems'));
		foreach ($res->getResult() as $tmp)
		{
			$param = array('type' => 'single', 'option' => 'permanent', 'source' => 'sentitems', 'id_message' => $tmp->id_sentitems);
			$this->Message_model->delete_messages($param);
		}

		// delete the rest (user, user_settings, pbk, pbk_groups, user_folders, sms_used)
		$this->User_model->delUsers($this->request->getPost('id_user'));
	}
}
