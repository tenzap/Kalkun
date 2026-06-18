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
 * User_model Class
 *
 * Handle all user database activity
 *
 * @package		Kalkun
 * @subpackage	User
 * @category	Models
 */
class UserModel extends Model {

    use KalkunPhonenumberTrait;

	protected $table = 'DUMMY';
	protected $allowedFields = [];

	// --------------------------------------------------------------------

	public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
	{
		parent::__construct($db, $validation);
	}

	/**
	 * Get User
	 *
	 * @access	public
	 * @param	mixed $param
	 * @return	object
	 */
	function getUsers($param)
	{
		$q = $this->builder('user_settings');
		$q->join('user', 'user.id_user = user_settings.id_user');
		switch ($param['option'])
		{
			case 'all':
				$q->select('*');
				break;

			case 'paginate':
				$q->limit($param['limit'], $param['offset']);
				break;

			case 'by_iduser':
				$q->where('user.id_user', $param['id_user']);
				break;

			case 'search':
				$search_word = strtolower(service('request')->getPost('search_name') ?? '');
				$q->like('realname', $search_word, 'both', null, TRUE);
				break;
		}
		$q->orderBy('realname');
		return $q->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Add User
	 *
	 * @access	public
	 * @param	mixed
	 * @return
	 */
	function addUser()
	{
		helper('kalkun');

		$q = $this->builder('user');
		$q->set('realname', trim(service('request')->getPost('realname')));
		$q->set('username', trim(service('request')->getPost('username')));
		$this->_phone_number_validation(service('request')->getPost('phone_number'));
		$q->set('phone_number', $this->phone_format_e164(service('request')->getPost('phone_number')));
		$q->set('level', service('request')->getPost('level'));

		// edit mode
		if (service('request')->getPost('id_user'))
		{
			if (config('Kalkun')->demo_mode
				&& intval(service('request')->getPost('id_user')) === 1)
			{
				if (service('request')->getPost('username') !== 'kalkun')
				{
					// Restore username to 'kalkun'
					$q->set('username', 'kalkun');
				}
				if (service('request')->getPost('level') !== 'admin')
				{
					// Restore level to 'admin'
					$q->set('level', 'admin');
				}
			}
			$q->where('id_user', service('request')->getPost('id_user'));
			$q->update();
		}
		else
		{
			$q->set('password', password_hash(service('request')->getPost('password'), PASSWORD_BCRYPT));
			$q->insert();

			// user_settings
			$q = $this->builder('user_settings');
			$q->set('theme', 'blue');
			$q->set('signature', 'false;');
			$q->set('permanent_delete', 'false');
			$q->set('paging', '20');
			$q->set('bg_image', 'true;background.jpg');
			$q->set('delivery_report', 'default');
			$q->set('language', 'english');
			$q->set('conversation_sort', 'asc');
			$q->set('id_user', $this->db->insertID());

			$q->insert();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete User
	 *
	 * @access	public
	 * @param	number $id_user ID user to delete
	 * @return
	 */
	function delUsers($id_user)
	{
		$this->builder('sms_used')->delete(array('id_user' => $id_user));
		$this->builder('user_folders')->delete(array('id_user' => $id_user));
		$this->builder('pbk')->delete(array('id_user' => $id_user));
		$this->builder('pbk_groups')->delete(array('id_user' => $id_user));
		$this->builder('user_settings')->delete(array('id_user' => $id_user));
		$this->builder('user')->delete(array('id_user' => $id_user));
	}

	// --------------------------------------------------------------------

	/**
	 * Search User
	 *
	 * @access	public
	 * @param	string $realname
	 * @return	object
	 */
	function search_user($realname)
	{
		$search_word = strtolower($realname);
		return $this->builder('user_settings')
			->join('user', 'user.id_user = user_settings.id_user')
			->like('realname', $search_word, 'both', null, TRUE)
			->orderBy('realname')
			->get();
	}
}
