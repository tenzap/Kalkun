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

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\KalkunPhonenumberTrait;

/**
 * Kalkun_model Class
 *
 * Handle all base database activity
 *
 * @package		Kalkun
 * @subpackage	Base
 * @category	Models
 */
class KalkunModel extends Model {

    use KalkunPhonenumberTrait;

	protected $table = 'DUMMY';
	protected $allowedFields = [];
	protected $request;
	protected $session;
	// --------------------------------------------------------------------
	public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
	{
		parent::__construct($db, $validation);
		$this->request = service('request');
		$this->session = session();
	}

	// --------------------------------------------------------------------

	/**
	 * Login
	 *
	 * Check login credential and set session
	 *
	 * @access	public
	 */
	function login()
	{
		$username = $this->request->getPost('username');
		$query = $this->builder('user')
		  ->where('username', $username)
		  ->get();

		if ($query->getNumRows() === 1 && password_verify($this->request->getPost('password'), $query->getRow('password')))
		{
			$this->session->set('loggedin', 'TRUE');
			$this->session->set('level', $query->getRow('level'));
			$this->session->set('id_user', $query->getRow('id_user'));
			$this->session->set('username', $query->getRow('username'));
			if ($this->request->getPost('remember_me'))
			{
				$this->session->set('remember_me', TRUE);
			}

			if ($this->request->getPost('r_url'))
			{
				return redirect()->to($this->request->getPost('r_url'));
			}
			else
			{
				return redirect()->to('kalkun');
			}
		}
		else
		{
			$this->session->setFlashdata('errorlogin', tr_raw('Username or password are incorrect.'));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Forgot password
	 *
	 * Generate token and send token to user
	 *
	 * @access	public
	 */
	function forgot_password()
	{
		$username = $this->request->getPost('username');
		$phone = $this->request->getPost('phone');
		if ($phone)
		{
			$region = service('language')::idom_to_region($this->request->getPost('idiom'));
			$phone = $this->phone_format_e164($phone, $region);
		}

		$query = $this->builder('user')
		  ->where('username', $username)
		  ->orWhere('phone_number', $phone)
		  ->get();

		if ($query->getNumRows() === 1)
		{
			/*$this->db->from('user_forgot_password');
			$this->db->where('id_user', $query->getRow('id_user'));*/
			$user = $this->builder('user_forgot_password')->where('id_user', $query->getRow('id_user'))->get();

			if ($user->getNumRows() === 1)
			{
				$valid_token = (strtotime('now') < strtotime($user->row('valid_until'))) ? TRUE : FALSE;

				// Destroy invalid token
				if ( ! $valid_token)
				{
					//$this->Kalkun_model = model('KalkunModel');
					$this->Kalkun_model->delete_token($query->getRow('id_user'));
				}
				else
				{
					$this->session->set_flashdata('errorlogin', tr_raw('Token already generated and still active.'));
				}
			}

			if ($user->getNumRows() === 0 OR ! $valid_token)
			{
				$token = bin2hex(random_bytes(16));
				// $this->db->set('id_user', $query->getRow('id_user'));
				// $this->db->set('token', $token);
				// $this->db->set('valid_until', date('Y-m-d H:i:s', mktime(date('H'), date('i') + 30, date('s'), date('m'), date('d'), date('Y'))));
				$this->builder('user_forgot_password')
				->set('id_user', $query->getRow('id_user'))
				->set('token', $token)
				->set('valid_until', date('Y-m-d H:i:s', mktime(date('H'), date('i') + 30, date('s'), date('m'), date('d'), date('Y'))))
				->insert();
				return array('phone' => $query->getRow('phone_number'), 'token' => $token);
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid token
	 *
	 * Check valid token from reset password table
	 *
	 * @return boolean | array
	 * @access	public
	 */
	function valid_token($token = NULL)
	{
		$token_result = $this->builder('user_forgot_password')
		  ->where('token', $token)
		  ->get();

		if ($token_result->getNumRows() === 1)
		{
			if (strtotime('now') < strtotime($token_result->getRow('valid_until')))
			{
				return $token_result->getRowArray();
			}
			else
			{
				$this->builder('user_forgot_password')
				  ->where('token', $token)
				  ->delete();
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete token
	 *
	 * Delete token from reset password table
	 *
	 * @return CI_DB_query_builder instance (method chaining) or FALSE on failure
	 * @access	public
	 */
	function delete_token($id_user = NULL)
	{
		return $this->builder('user_forgot_password')
		->where('id_user', $id_user)
		->delete();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Folders
	 *
	 * List of custom folders
	 *
	 * @access	public
	 */
	function get_folders($option = NULL, $id_folder = NULL, $id_user = NULL)
	{
		$q  = $this->builder('user_folders');

		switch ($option)
		{
			case 'all':
				$q->where('id_folder >', '10');
				$q->where('id_user', $this->session->get('id_user'));
				break;

			case 'exclude':
				$q->where('id_folder >', '10');
				$q->where('id_folder !=', $id_folder);
				$q->where('id_user', $this->session->get('id_user'));
				break;

			case 'name':
				$q->where('id_folder', $id_folder);
				if ($id_folder !== '5' && $id_folder !== '6')
				{
					$q->where('id_user', $this->session->get('id_user'));
				}
				break;
		}

		$q->orderBy('name');
		return $q->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Add Folder
	 *
	 * Add custom folder
	 *
	 * @access	public
	 */
	function add_folder()
	{
		$data = array ('name' => $this->request->getPost('folder_name'), 'id_user' => $this->request->getPost('id_user'));
		$this->builder('user_folders')->insert($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Rename Folder
	 *
	 * Rename custom folder
	 *
	 * @access	public
	 */
	function rename_folder()
	{
		$this->builder('user_folders')->set('name', $this->request->getPost('edit_folder_name'))
		->where('id_folder', $this->request->getPost('id_folder'))
		->update();
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Folder
	 *
	 * Delete custom folder
	 *
	 * @access	public
	 */
	function delete_folder($id_folder = NULL)
	{
		$id_user = $this->session->get('id_user');

		// get inbox
		$q = $this->builder('inbox');
		$q->select('inbox.ID', 'id_inbox');
		$q->join('user_inbox', 'user_inbox.id_inbox=inbox.ID');
		$q->join('user_folders', 'user_folders.id_folder=inbox.id_folder');
		$q->where('user_folders.id_folder', $id_folder);
		$inbox = $q->get();

		// delete inbox and user_inbox
		foreach ($inbox->getResult() as $tmp)
		{
			$this->builder('inbox')
			->where('ID', $tmp->id_inbox)
			->delete();

			$this->builder('user_inbox')
			->where('id_inbox', $tmp->id_inbox)
			->delete();
		}

		// deprecated
		// inbox
		/* $inbox = "DELETE i, ui
				FROM user_folders AS uf
				LEFT JOIN inbox AS i ON i.id_folder = uf.id_folder
				LEFT JOIN user_inbox AS ui ON ui.id_inbox = i.ID
				WHERE uf.id_folder = '".$id_folder."'";
		$this->db->query($inbox);*/

		// get sentitems
		$q = $this->builder('sentitems');
		$q->select('sentitems.ID as id_sentitems');
		$q->join('user_sentitems', 'user_sentitems.id_sentitems=sentitems.ID');
		$q->join('user_folders', 'user_folders.id_folder=sentitems.id_folder');
		$q->where('user_folders.id_folder', $id_folder);
		$sentitems = $q->get();

		// delete sentitems and user_sentitems
		foreach ($sentitems->getResult() as $tmp)
		{
			$this->builder('sentitems')
			->where('ID', $tmp->id_sentitems)
			->delete('sentitems');

			$this->builder('user_sentitems')
			->where('id_sentitems', $tmp->id_sentitems)
			->delete('user_sentitems');
		}

		// deprecated
		// Sentitems
		/*$sentitems = "DELETE s, us
				FROM user_folders AS uf
				LEFT JOIN sentitems AS s ON s.id_folder = uf.id_folder
				LEFT JOIN user_sentitems AS us ON us.id_sentitems = s.ID
				WHERE uf.id_folder = '".$id_folder."'";
		$this->db->query($sentitems);*/

		$this->builder('user_folders')->delete(array('id_folder' => $id_folder, 'id_user' => $id_user));
	}

	// --------------------------------------------------------------------

	/**
	 * Update Setting
	 *
	 * Update setting/user preferences
	 *
	 * @access	public
	 */
	function update_setting($option)
	{
		switch ($option)
		{
			case 'general':
				$q = $this->builder('user_settings');
				$q->set('language', $this->request->getPost('language'));
				$q->set('paging', $this->request->getPost('paging'));
				$q->set('permanent_delete', $this->request->getPost('permanent_delete'));
				$q->set('delivery_report', $this->request->getPost('delivery_report'));
				$q->set('conversation_sort', $this->request->getPost('conversation_sort'));
				$q->set('country_code', $this->request->getPost('dial_code'));
				$q->where('id_user', $this->session->get('id_user'));
				$q->update();
				// Refresh language before we display any message.
				// Special case for when the user changes the language on this screen
				service('Language')->load('kalkun_lang', service('language')::$idiom_to_locale[$this->request->getPost('language')]);
				break;

			case 'personal':
				$q = $this->builder('user');
				$q->set('realname', $this->request->getPost('realname'));
				if ( ! (config('kalkun')->demo_mode
					&& intval($this->session->get('id_user')) === 1))
				{
					$q->set('username', $this->request->getPost('username'));
				}
				$this->_phone_number_validation($this->request->getPost('phone_number'));
				$q->set('phone_number', $this->phone_format_e164($this->request->getPost('phone_number')));
				$q->where('id_user', $this->session->get('id_user'));
				$q->update();

				$q = $this->builder('user_settings');
				$sig_opt = $this->request->getPost('signatureoption');
				$q->set('signature', $sig_opt.';'.$this->request->getPost('signature'));
				$q->where('id_user', $this->session->get('id_user'));
				$q->update();
				break;

			case 'appearance':
				$q = $this->builder('user_settings');
				$q->set('theme', $this->request->getPost('theme'));
				$q->set('bg_image', $this->request->getPost('bg_image_option').';background.jpg');
				$q->where('id_user', $this->session->get('id_user'));
				$q->update();
				break;

			case 'password':
				if ( ! (config('kalkun')->demo_mode && intval($this->session->get('id_user')) === 1))
				{
					$q = $this->builder('user');
					$q->set('password', password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT));
					$q->where('id_user', $this->session->get('id_user'));
					$q->update();
				}
				break;

			case 'filters':
				$id_filter = $this->request->getPost('id_filter');
				$q = $this->builder('user_filters');
				$q->set('from', $this->request->getPost('from'));
				$q->set('has_the_words', $this->request->getPost('has_the_words'));
				$q->set('id_folder', $this->request->getPost('id_folder'));
				$q->set('id_user', $this->request->getPost('id_user'));

				if ( ! empty($id_filter))
				{
					$q->where('id_filter', $id_filter);
					$q->update();
				}
				else
				{
					$q->insert();
				}
				break;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Update Password
	 *
	 * Update user password from uid
	 *
	 * @access	public
	 */
	function update_password($uid = NULL)
	{
		$this->builder('user')->set('password', password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT))
		->where('id_user', $uid)
		->update();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Setting
	 *
	 * Get setting/user preferences
	 *
	 * @access	public
	 */
	function get_setting($id_user = '')
	{
		if ($id_user === '')
		{
			$id_user = $this->session->get('id_user');
		}
		$q = $this->builder('user_settings')->where('user.id_user', $id_user)
		->join('user', 'user.id_user = user_settings.id_user')
		->get();
		return $q;
	}
	// --------------------------------------------------------------------

	/**
	 * Check Setting
	 *
	 * Check for duplicate username or phone number
	 *
	 * @access	public
	 */
	function check_setting($param)
	{
		$q = $this->builder('user');
		switch ($param['option'])
		{
			case 'username':
				$q->where('username', $param['username']);
				break;

			case 'phone_number':
				$q->where('phone_number', $this->phone_format_e164($param['phone_number']));
				break;
		}
		return $q->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Gammu Info
	 *
	 * Get gammu related information
	 *
	 * @access	public
	 */
	function get_gammu_info($option)
	{
		$q = null;
		switch ($option)
		{
			case 'gammu_version':
				$q = $this->builder('phones')
				->select('Client')
				->orderBy('UpdatedInDB', 'DESC')
				->limit('1');
				break;

			case 'db_version':
				$q = $this->builder('gammu')
				->select('Version');
				break;

			case 'last_activity':
				$q = $this->builder('phones')
				->select('UpdatedInDB')
				->orderBy('UpdatedInDB', 'DESC')
				->limit('1');
				break;

			case 'phone_imei':
				$q = $this->builder('phones')
				->select('IMEI')
				->orderBy('UpdatedInDB', 'DESC')
				->limit('1');
				break;

			case 'phone_signal':
				$q = $this->builder('phones')
				->select('Signal')
				->orderBy('UpdatedInDB', 'DESC')
				->limit('1');
				break;

			case 'phone_battery':
				$q = $this->builder('phones')
				->select('Battery')
				->orderBy('UpdatedInDB', 'DESC')
				->limit('1');
				break;
		}
		return $q->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Get SMS Used
	 *
	 * Get SMS count used by user based on date
	 *
	 * @access	public
	 */
	function get_sms_used($option, $param, $type = 'out')
	{
		switch ($option)
		{
			case 'date':
				$q = $this->builder('sms_used');
				$q->selectSum($type.'_sms_count');

				if (isset($param['sms_date_start']) && isset($param['sms_date_end']))
				{
					$q->where('sms_date >=', $param['sms_date_start']);
					$q->where('sms_date <=', $param['sms_date_end']);
				}
				else
				{
					$q->where('sms_date', $param['sms_date']);
				}

				if (isset($param['user_id']))
				{
					$q->where('id_user', $param['user_id']);
				}
				$res = $q->get()->getRow($type.'_sms_count');
				if ( ! $res)
				{
					return 0;
				}
				else
				{
					return $res;
				}
				break;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add SMS Used
	 *
	 * Add SMS counter used by user based on date
	 *
	 * @access	public
	 */
	function add_sms_used($user_id, $type = 'out')
	{
		if ( ! is_array($user_id))
		{
			$user_id = (array) $user_id;
		}

		$q = $this->builder('sms_used');

		foreach ($user_id as $uid)
		{
			$date = date('Y-m-d');
			$count = $this->_check_sms_used($date, $uid, $type);
			$q->where('sms_date', $date);
			$q->where('id_user', $uid);

			if ($q->countAllResults('sms_used') > 0)
			{
				$q->set($type.'_sms_count', $count + 1);
				$q->where('sms_date', $date);
				$q->where('id_user', $uid);
				$q->update();
			}
			else
			{
				$q->set($type.'_sms_count', '1');
				$q->set('sms_date', $date);
				$q->set('id_user', $uid);
				$q->insert();
			}
		}
	}

	function _check_sms_used($date, $user_id, $type = 'out')
	{
		$res = $this->builder('sms_used')->select($type.'_sms_count')
		->where('sms_date', $date)
		->where('id_user', $user_id)
		->get()->getRow($type.'_sms_count');
		if ( ! $res)
		{
			return 0;
		}
		else
		{
			return $res;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get user filters
	 *
	 * @access	public
	 */
	function get_filters($user_id = NULL)
	{
		$q = $this->builder('user_filters');

		if ( ! is_null($user_id))
		{
			$q->where('user_filters.id_user', $user_id);
		}

		$q->join('user_folders', 'user_folders.id_folder=user_filters.id_folder');
		return $q->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Delete user filters
	 *
	 * @access	public
	 */

	function delete_filter($id_filter = NULL)
	{
		$q = $this->builder('user_filters');
		$q->where('id_filter', $id_filter);
		return $q->delete();
	}

	function has_table_plugins()
	{
		return $this->db->tableExists('plugins');
	}

	function has_table_user_forgot_password()
	{
		return $this->db->tableExists('user_forgot_password');
	}

	function has_table_user_filters()
	{
		return $this->db->tableExists('user_filters');
	}

	function has_table_ci_sessions()
	{
		return $this->db->tableExists('ci_sessions');
	}

	function has_table_pbk()
	{
		return $this->db->tableExists('pbk');
	}

	function has_table_pbk_with_kalkun_fields()
	{
		return $this->db->fieldExists('id_user', 'pbk');
	}

	function plugins_table_has_status_column()
	{
		if ( ! $this->db->tableExists('plugins'))
		{
			return FALSE;
		}
		return $this->db->fieldExists('status', 'plugins');
	}
}
