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
		$this->db->from('user_settings');
		$this->db->join('user', 'user.id_user = user_settings.id_user');
		switch ($param['option'])
		{
			case 'all':
				$this->db->select('*');
				break;

			case 'paginate':
				$this->db->limit($param['limit'], $param['offset']);
				break;

			case 'by_iduser':
				$this->db->where('user.id_user', $param['id_user']);
				break;

			case 'search':
				$search_word = strtolower(service('request')->getPost('search_name'));
				$this->db->like('LOWER('.$this->db->protect_identifiers('realname').')', $search_word);
				break;
		}
		$this->db->order_by('realname');
		return $this->db->get();
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
		$this->db->set('realname', trim(service('request')->getPost('realname')));
		$this->db->set('username', trim(service('request')->getPost('username')));
		$this->_phone_number_validation(service('request')->getPost('phone_number'));
		$this->db->set('phone_number', phone_format_e164(service('request')->getPost('phone_number')));
		$this->db->set('level', service('request')->getPost('level'));

		// edit mode
		if (service('request')->getPost('id_user'))
		{
			if (config('Kalkun')->demo_mode
				&& intval(service('request')->getPost('id_user')) === 1)
			{
				if (service('request')->getPost('username') !== 'kalkun')
				{
					// Restore username to 'kalkun'
					$this->db->set('username', 'kalkun');
				}
				if (service('request')->getPost('level') !== 'admin')
				{
					// Restore level to 'admin'
					$this->db->set('level', 'admin');
				}
			}
			$this->db->where('id_user', service('request')->getPost('id_user'));
			$this->db->update('user');
		}
		else
		{
			$this->db->set('password', password_hash(service('request')->getPost('password'), PASSWORD_BCRYPT));
			$this->db->insert('user');

			// user_settings
			$this->db->set('theme', 'blue');
			$this->db->set('signature', 'false;');
			$this->db->set('permanent_delete', 'false');
			$this->db->set('paging', '20');
			$this->db->set('bg_image', 'true;background.jpg');
			$this->db->set('delivery_report', 'default');
			$this->db->set('language', 'english');
			$this->db->set('conversation_sort', 'asc');
			$this->db->set('id_user', $this->db->insert_id());

			$this->db->insert('user_settings');
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
		$this->db->delete('sms_used', array('id_user' => $id_user));
		$this->db->delete('user_folders', array('id_user' => $id_user));
		$this->db->delete('pbk', array('id_user' => $id_user));
		$this->db->delete('pbk_groups', array('id_user' => $id_user));
		$this->db->delete('user_settings', array('id_user' => $id_user));
		$this->db->delete('user', array('id_user' => $id_user));
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
		$this->db->from('user_settings');
		$this->db->join('user', 'user.id_user = user_settings.id_user');
		$this->db->like('LOWER('.$this->db->protect_identifiers('realname').')', $search_word);
		$this->db->order_by('realname');
		return $this->db->get();
	}
}
