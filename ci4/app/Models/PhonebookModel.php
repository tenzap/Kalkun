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
 * Phonebook_model Class
 *
 * Handle all phonebook database activity
 *
 * @package		Kalkun
 * @subpackage	Phonebook
 * @category	Models
 */
class PhonebookModel extends Model {

    use KalkunPhonenumberTrait;

	// --------------------------------------------------------------------
	public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
	{
		parent::__construct($db, $validation);
	}
	/**
	 * Get Phonebook
	 *
	 * @access	public
	 * @param	mixed $param
	 * @return	object
	 */
	function get_phonebook($param)
	{
		if (isset($param['id_user']) && ! empty($param['id_user']))
		{
			$user_id = $param['id_user'];
		}
		else
		{
			$user_id = session()->get('id_user') ;
		}
		$q = null;
		switch ($param['option'])
		{
			case 'all':
				$q = $this->builder('pbk');
				$q->select('*');
				$q->select('pbk.ID as id_pbk');
				$q->select('pbk_groups.Name as GroupName');
				$q->where('pbk.id_user', $user_id);
				$q->join('user_group', 'user_group.id_pbk=pbk.ID', 'left');
				$q->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups', 'left');
				$q->orderBy('pbk.Name');
				break;

			case 'paginate':
				$q = $this->builder('pbk');
				$q->select('*');
				$q->select('ID as id_pbk');
				if (isset($param['public']) && $param['public'])
				{
					$q->where('is_public', 'true');
				}
				else
				{
					$q->where('id_user', $user_id);
				}
				$q->orderBy('Name');
				if (isset($param['limit']) && isset($param['offset']))
				{
					$q->limit($param['limit'], $param['offset']);
				}
				break;

			case 'by_idpbk':
				$q = $this->builder('pbk');
				$q->select('pbk.*');
				$q->select('pbk.ID as id_pbk');
				$q->select('pbk.Name as Name');
				$q->select('pbk_groups.Name as GroupName');
				$q->where('pbk.id_user', $user_id);
				$q->join('user_group', 'user_group.id_pbk=pbk.ID', 'left');
				$q->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups', 'left');
				$q->where('pbk.ID', $param['id_pbk']);
				break;

			case 'group':
				$q = $this->builder('pbk_groups');
				$q->select('*');
				$q->select('Name as GroupName');
				if (isset($param['public']) && $param['public'])
				{
					$q->where('is_public', 'true');
				}
				else
				{
					$q->where('id_user', $user_id);
				}
				$q->orderBy('Name');
				break;

			case 'group_paginate':
				$q = $this->builder('pbk_groups');
				$q->select('*');
				$q->select('Name as GroupName');
				if (isset($param['public']) && $param['public'])
				{
					$q->where('is_public', 'true');
				}
				else
				{
					$q->where('id_user', $user_id);
				}
				$q->orderBy('Name');
				$q->limit($param['limit'], $param['offset']);
				break;

			case 'groupname':
				$q = $this->builder('pbk_groups');
				$q->select('ID');
				$q->select('Name as GroupName');
				$q->groupStart()
					->where('id_user', $user_id)
					->orWhere('is_public', 'true')
					->groupEnd();
				$q->where('ID', $param['id']);
				break;

			case 'bynumber':
				// search phone number prefix
				$arr_number = $this->convert_phonenumber(array('number' => $param['number'], 'id_user' => $user_id));

				$q = $this->builder('pbk');
				$q->select('*');
				$q->select('ID as id_pbk');
				$q->groupStart()
					->where('id_user', $user_id)
					->orWhere('is_public', 'true')
					->groupEnd();
				$q->whereIn('Number', $arr_number);
				break;

			case 'bygroup':
				$q = $this->builder('pbk');
				$q->select('*');
				$q->select('pbk.Name as Name');
				$q->select('pbk_groups.Name as GroupName');
				$q->join('user_group', 'user_group.id_pbk=pbk.ID');
				$q->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups');
				$q->groupStart()
					->where('pbk_groups.id_user', $user_id)
					->orWhere('pbk_groups.is_public', 'true')
					->groupEnd();
				$q->where('user_group.id_pbk_groups', $param['group_id']);
				$q->orderBy('pbk.Name', 'asc');

				if (isset($param['limit']) && isset($param['offset']))
				{
					$q->limit($param['limit'], $param['offset']);
				}
				break;

			case 'search':
				$search_word = strtolower($this->input->post('search_name'));
				$q = $this->builder('pbk');
				$q->select('*');
				$q->select('ID as id_pbk');
				$q->groupStart()
						  ->where('id_user', $user_id)
						  ->orWhere('is_public', 'true')
					->groupEnd();
				$q->groupStart()
						->like('LOWER('.$q->protect_identifiers('Name').')', $search_word)
						->or_like('LOWER('.$q->protect_identifiers('Number').')', $search_word)
					->groupEnd();
				$q->orderBy('Name');
				break;

			case 'public':
				$q = $this->builder('pbk');
				$q->select('*');
				$q->select('pbk.ID as id_pbk');
				$q->select('pbk_groups.Name as GroupName');
				$q->where('pbk.is_public', 'true');
				$q->join('user_group', 'user_group.id_pbk=pbk.ID', 'left');
				$q->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups', 'left');
				$q->orderBy('pbk.Name');
				break;
		}
		//echo $q->last_query();
		return $q->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Search Phonebook
	 *
	 * @access	public
	 * @param	mixed $param
	 * @return	object
	 */
	function search_phonebook($param)
	{
		$search_word = strtolower($param['query']);
		$this->db->from('pbk');
		$this->db->select('Number as id');
		$this->db->select('Name as name');
		$this->db->groupStart()
			->where('id_user', $param['uid'])
			->orWhere('is_public', 'true')
			->groupEnd();
		$this->db->like('LOWER('.$this->db->protect_identifiers('Name').')', $search_word);
		$this->db->orderBy('Name');
		return $this->db->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Search Group
	 *
	 * @access	public
	 * @param	mixed $param
	 * @return	object
	 */
	function search_group($param)
	{
		$search_word = strtolower($param['query']);
		$this->db->from('pbk_groups');
		$this->db->select('ID as id');
		$this->db->select('Name as name');
		$this->db->groupStart()
			->where('pbk_groups.id_user', $param['uid'])
			->orWhere('is_public', 'true')
			->groupEnd();
		$this->db->like('LOWER('.$this->db->protect_identifiers('Name').')', $search_word);
		$this->db->orderBy('Name');
		$this->db->join('user_group', 'user_group.id_pbk_groups=pbk_groups.ID');
		$this->db->group_by('Name');
		$this->db->group_by('ID');
		return $this->db->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Add Contact
	 *
	 * @access	public
	 * @param	mixed $param
	 * @return
	 */
	function add_contact($param)
	{
		$this->load->helper('kalkun');
		$this->db->set('Name', $param['Name']);
		$this->db->set('Number', phone_format_e164($param['Number']));
		$this->db->set('id_user', $param['id_user']);
		$this->db->set('is_public', $param['is_public']);

		// edit mode
		if (isset($param['id_pbk']))
		{
			$this->db->where('ID', $param['id_pbk']);
			$this->db->update('pbk');
		}
		else
		{
			$this->db->insert('pbk');
		}

		// optimisation required.
		if (isset($param['id_pbk']))
		{
			$pbk_id = $param['id_pbk'];
		}
		else
		{
			$pbk_id = $this->db->insert_id();
		}

		//delete past groups
		$this->db->delete('user_group', array('id_pbk' => $pbk_id));

		// now insert the lastest
		if (isset($param['GroupID']))
		{
			if ( ! empty($param['GroupID']))
			{
				$this->db->set('id_pbk', $pbk_id);
				$this->db->set('id_pbk_groups', $param['GroupID']);
				$this->db->set('id_user', $param['id_user']);
				$this->db->insert('user_group');
			}
		}
		if (isset($param['Groups']))
		{
			if ( ! empty($param['Groups']))
			{
				$groups = array_unique(explode(',', $param['Groups']));
				$CI = &get_instance();
				foreach ($groups as $_grp)
				{
					$group_id = $CI->Phonebook_model->group_id($_grp, $param['id_user']);

					if ($group_id !== NULL)
					{
						$this->db->set('id_pbk', $pbk_id);
						$this->db->set('id_pbk_groups', $group_id);
						$this->db->set('id_user', $param['id_user']);
						$this->db->insert('user_group');
					}
				}
			}
		}
	}

	function multi_attach_group()
	{
		$id_group = $this->input->post('id_group');
		$id_pbk = $this->input->post('id_pbk');

		//This case probably never happens because it is filtered out in 'application/views/js_init/phonebook/js_phonebook.php'
		if ($id_group === 'null')
		{
			show_error('Invalid Group ID', 400);
		}

		//parse group value
		if (preg_match('/-/', $id_group))
		{
			$mode = 'delete';
			$id_group = substr($id_group, 1);
		}
		else
		{
			$mode = 'add';
		}

		if ($mode === 'delete')
		{
			$this->db->delete('user_group', array('id_pbk' => $id_pbk, 'id_pbk_groups' => $id_group));
		}
		else
		{ // Add Mode
			$this->db->from('user_group');
			$this->db->where('id_pbk', $id_pbk);
			$this->db->where('id_pbk_groups', $id_group);

			if ($this->db->get()->num_rows() < 1)
			{
				$this->db->set('id_pbk', $id_pbk);
				$this->db->set('id_pbk_groups', $id_group);
				$this->db->set('id_user', session()->get('id_user'));
				$this->db->insert('user_group');
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add Group
	 *
	 * @access	public
	 * @param	mixed $param
	 * @return
	 */
	function add_group()
	{
		$this->db->set('Name', trim($this->input->post('group_name')));
		$this->db->set('id_user', trim($this->input->post('pbkgroup_id_user')));
		$this->db->set('is_public', $this->input->post('is_public') ? 'true' : 'false');

		// edit mode
		if ($this->input->post('pbkgroup_id'))
		{
			$this->db->where('ID', $this->input->post('pbkgroup_id'));
			$this->db->update('pbk_groups');
		}
		else
		{
			$this->db->insert('pbk_groups');
		}
	}

	// --------------------------------------------------------------------

	/**
	* Get Groups ID for a Group Name
	*
	* @access	public
	* @param	text $group_name
	* @param	number $user_id
	* @return
	*/
	function group_id($group_name, $user_id)
	{
		$this->db->select('*');
		$this->db->from('pbk_groups');
		$this->db->where('Name', $group_name);
		$this->db->where('id_user', $user_id);
		return @$this->db->get()->row()->ID;
	}

	// --------------------------------------------------------------------

	/**
	* Get Groups Name for a Group ID
	*
	* @access	public
	* @param	string $group_name
	* @param	number $user_id
	* @return
	*/
	function group_name($group_id, $user_id)
	{
		$this->db->select('*');
		$this->db->from('pbk_groups');
		$this->db->where('ID', $group_id);
		$this->db->where('id_user', $user_id);
		return @$this->db->get()->row()->Name;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Groups for  a contact id
	 *
	 * @access	public
	 * @param	number $pbk_id
	 * @param	number $user_id
	 * @return
	 */
	function get_groups($pbk_id, $user_id)
	{
		$this->db->select('user_group.id_pbk_groups as GroupID');
		$this->db->select('pbk_groups.Name as GroupName');
		$this->db->from('user_group');
		$this->db->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups');
		$this->db->where('user_group.id_user', $user_id);
		$this->db->where('user_group.id_pbk', $pbk_id);
		$q = $this->db->get();
		$GroupID = $GroupName = '';
		foreach ($q->result() as $_gp)
		{
			$GroupName .= $_gp->GroupName.',';
			$GroupID .= $_gp->GroupID .',';
		}
		$GroupName = substr($GroupName, 0, strlen($GroupName) - 1);
		$GroupID = substr($GroupID, 0, strlen($GroupID) - 1);
		return (object) array('GroupNames' => $GroupName, 'GroupIDs' => $GroupID);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Contact
	 *
	 * @access	public
	 * @param	number $id_contact
	 * @return
	 */
	function delete_contact()
	{
		$this->db->delete('pbk', array('ID' => $this->input->post('id')));
		$this->db->delete('user_group', array('id_pbk' => $this->input->post('id')));
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Group
	 *
	 * @access	public
	 * @param	mixed $id_group
	 * @return
	 */
	function delete_group()
	{
		$this->db->delete('pbk', array('GroupID' => $this->input->post('id')));
		$this->db->delete('pbk_groups', array('ID' => $this->input->post('id')));
		$this->db->delete('user_group', array('id_pbk_groups' => $this->input->post('id')));
	}

	// --------------------------------------------------------------------

	/**
	 * Get Phonenumber (original, localization, and internationalization )
	 *
	 * @access	public
	 * @param	array $param
	 * @return array
	 */
	function convert_phonenumber($param)
	{
		if ( ! isset($param['id_user']))
		{
			$param['id_user'] = '';
		}
		$this->load->model('Kalkun_model');
		$country_code = $this->Kalkun_model->get_setting($param['id_user'])->row('country_code');
		$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$dial_code = '+'.$phoneNumberUtil->getCountryCodeForRegion(strval($country_code));
		$number_local = str_replace($dial_code, '0', $param['number']);
		$number_inter = $dial_code.substr($param['number'], 1);
		return array($param['number'], $number_local, $number_inter);
	}
}
