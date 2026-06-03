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
use App\Libraries\KalkunPhonenumberTrait;
/**
 * Kalkun Class
 *
 * @package		Kalkun
 * @subpackage	Base
 * @category	Controllers
 */

class Kalkun extends MYController {

	use KalkunPhonenumberTrait;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Index/Dashboard
	 *
	 * Display dashboard page
	 *
	 * @access	public
	 */
	public function getIndex()
	{
		helper('i18n');
		$this->Phonebook_model = model('PhonebookModel');
		$data['main'] = 'main/dashboard/home';
		$data['title'] = 'Dashboard';
		if (config('Kalkun')->disable_outgoing)
		{
			$data['alerts'][] = tr_raw('Outgoing SMS disabled. Contact system administrator.');
		}
		return view('main/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Statistic
	 *
	 * Get statistic data that used to render the graph
	 *
	 * @param string (days, weeks, months)
	 * @access	public
	 */
	function get_statistic($type = 'days')
	{
		// count number of days
		switch ($type)
		{
			case 'days':
			default:
				$days = 10;
				$format = 'M-d';
				break;

			case 'weeks':
				$days = 30;
				$format = 'W';
				$prefix = tr_raw('date_week').' ';
				break;

			case 'months':
				$days = 60;
				$format = 'M-Y';
				break;
		}

		$this->Kalkun_model = model('KalkunModel');
		// generate data points
		$x = array();
		for ($i = 0; $i <= $days; $i++)
		{
			$key = date($format, mktime(0, 0, 0, date('m'), date('d') - $i, date('Y')));

			if (isset($prefix))
			{
				$key = $prefix.$key;
			}

			if ( ! isset($yout[$key]))
			{
				$yout[$key] = 0;
			}

			if ( ! isset($yin[$key]))
			{
				$yin[$key] = 0;
			}

			if ( ! in_array($key, $x))
			{
				$x[] = $key;
			}

			$param['sms_date'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $i, date('Y')));
			if (session()->get('level') !== 'admin')
			{
				$param['user_id'] = session()->get('id_user');
			}
			$yout[$key] += $this->Kalkun_model->get_sms_used('date', $param, 'out');
			$yin[$key] += $this->Kalkun_model->get_sms_used('date', $param, 'in');
		}

		$yout = array_values($yout);
		$yin = array_values($yin);
		$points = count($x) - 1;

		$result = [
			'labels' => array_reverse($x),
			'datasets' => [
				[
					'label' => tr_raw('Outgoing SMS'),
					'backgroundColor' => '#21759B',
					'data' => array_reverse($yout),
					'borderWidth' => 1,
				],
				[
					'label' => tr_raw('Incoming SMS'),
					'backgroundColor' => '#639F45',
					'data' => array_reverse($yin),
					'borderWidth' => 1,
				],
			],
		];

		return $this->response->setJSON($result);
	}

	// --------------------------------------------------------------------

	/**
	 * Notification
	 *
	 * Display notification
	 * Modem status
	 * Used by the autoload function and called via AJAX.
	 *
	 * @access	public
	 */
	function notification()
	{
		$this->Kalkun_model = model('KalkunModel');
		$status = $this->Kalkun_model->get_gammu_info('last_activity')->getRow('UpdatedInDB');
		$response['signal'] = intval($this->Kalkun_model->get_gammu_info('phone_signal')->getRow('Signal'));
		$response['signal_lbl'] = tr_raw('{0}%', NULL, $this->Kalkun_model->get_gammu_info('phone_signal')->getRow('Signal'));
		$response['battery'] = intval($this->Kalkun_model->get_gammu_info('phone_battery')->getRow('Battery'));
		$response['battery_lbl'] = tr_raw('{0}%', NULL, $this->Kalkun_model->get_gammu_info('phone_battery')->getRow('Battery'));
		if ( ! empty($status))
		{
			helper('kalkun');
			$status = get_modem_status($status, config('Kalkun')->modem_tolerant);
			if ($status === 'connect')
			{
				$response['status'] = 'connected';
				$response['status_lbl'] = tr_raw('Connected');
			}
			else
			{
				$response['status'] = 'disconnected';
				$response['status_lbl'] = tr_raw('Disconnected');
			}
		}
		else
		{
			$response['status'] = 'Unknown';
			$response['status_lbl'] = tr_raw('Unknown');
		}

		if ($this->request->isAjax())
		{
			return $this->response->setJSON($response);
		}
		else
		{
			return view('main/notification');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Unread Count
	 *
	 * Show unread inbox/spam/draft and alert when new sms arrived
	 * Used by the autoload function and called via AJAX.
	 *
	 * @access	public
	 */
	function unread_count()
	{
		$unread_count['in'] = $this->Message_model->get_messages([
			'readed' => FALSE,
			'uid' => session()->get('id_user'),
		])->getNumRows();
		$unread_count['draft'] = 0;
		$unread_count['spam'] = $this->Message_model->get_messages([
			'readed' => FALSE,
			'id_folder' => '6',
			'uid' => session()->get('id_user'),
		])->getNumRows();

		return $this->response->setJSON($unread_count);
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
		$this->Kalkun_model = model('KalkunModel');
		$this->Kalkun_model->add_folder();
		return redirect()->to($this->request->getPost('source_url') !== NULL ? $this->request->getPost('source_url') : '');
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
		$this->Kalkun_model = model('KalkunModel');
		$this->Kalkun_model->rename_folder();
		return redirect()->to(strval($this->request->getPost('source_url')));
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
		$this->Kalkun_model = model('KalkunModel');
		$this->Kalkun_model->delete_folder($id_folder);
		return redirect()->to('/');
	}

	// --------------------------------------------------------------------

	/**
	 * Settings
	 *
	 * Display and handle change on settings/user preference
	 *
	 * @access	public
	 */
	function settings($type = NULL)
	{
		$data['title'] = 'Settings';
		$valid_type = array('general', 'personal', 'appearance', 'password', 'save', 'filters');
		if ( ! in_array($type, $valid_type))
		{
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$this->Kalkun_model = model('KalkunModel');
		if ($_POST && $type === 'save')
		{
			$option = $this->request->getPost('option');
			// check password
			if ($option === 'password')
			{
				if (config('Kalkun')->demo_mode && intval(session()->get('id_user')) === 1)
				{
					session()->setFlashdata('notif', tr_raw('Password modification forbidden in demo mode.'));
					return redirect()->to('settings/'.$option);
				}
				if ( ! password_verify($this->request->getPost('current_password'), $this->Kalkun_model->get_setting()->getRow('password')))
				{
					session()->setFlashdata('notif', tr_raw('Wrong password'));
					return redirect()->to('settings/'.$option);
				}
			}
			else
			{
				if ($option === 'personal')
				{
					if ($this->request->getPost('username') !== session()->get('username'))
					{
						if ($this->Kalkun_model->check_setting(array('option' => 'username', 'username' => $this->request->getPost('username')))->getNumRows() > 0)
						{
							session()->setFlashdata('notif', tr_raw('Username already taken'));
							return redirect()->to('settings/'.$option);
						}
					}
				}
			}
			$this->Kalkun_model->update_setting($option);
			if (config('Kalkun')->demo_mode
				&& intval(session()->get('id_user')) === 1
				&& $this->request->getPost('username') !== 'kalkun')
			{
				session()->setFlashdata('notif', tr_raw('Settings saved successfully (except username for kalkun user which can\'t be changed in demo mode)'));
			}
			else
			{
				session()->setFlashdata('notif', tr_raw('Settings saved successfully.'));
			}
			return redirect()->to('settings/'.$option);
		}

		if ($type === 'filters')
		{
			$data['filters'] = $this->Kalkun_model->get_filters(session()->get('id_user'));
			$data['my_folders'] = $this->Kalkun_model->get_folders('all');
		}

		$data['main'] = 'main/settings/setting';
		$data['settings'] = $this->Kalkun_model->get_setting();
		$data['type'] = 'main/settings/'.$type;

		return view('main/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Filter
	 *
	 * @access	public
	 */
	function delete_filter($id_filter = NULL)
	{
		$this->Kalkun_model = model('KalkunModel');
		$this->Kalkun_model->delete_filter($id_filter);
	}

	// --------------------------------------------------------------------

	/**
	 * Check phone number validity
	 *
	 * returns a json string used by jquery validation plugin
	 * "true" if phone number is valid
	 * "an error message" if not
	 */
	function phone_number_validation()
	{
		$region;
		$phone;
		if ($this->request->is('POST'))
		{
			$phone = $this->request->getPost('phone');
			$region = $this->request->getPost('region');
		}
		else if ($this->request->is('GET'))
		{
			$phone = $this->request->getGet('phone');
			$region = $this->request->getGet('region');
		}
		$result = $this->is_phone_number_valid($phone, $region);

		if ($result === TRUE)
		{
			$result = 'true';
		}
		else
		{
			$result = tr_raw($result);
		}

		return $this->response->setJSON($result, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Check multiple phone number validity
	 *
	 * returns a json string used by jquery validation plugin
	 * "true" if all phone numbers are valid
	 * "an error message with the faulty number" if not
	 */
	function phone_number_validation_multiple()
	{
		$region;
		$phone;
		if ($this->request->is('POST'))
		{
			$phone = $this->request->getPost('phone');
			$region = $this->request->getPost('region');
		}
		else if ($this->request->is('GET'))
		{
			$phone = $this->request->getGet('phone');
			$region = $this->request->getGet('region');
		}

		$tmp_dest = explode(',', $phone);
		foreach ($tmp_dest as $key => $val)
		{
			$result = $this->is_phone_number_valid($val, $region);
			if ($result !== TRUE)
			{
				return $this->response->setJSON(tr_raw($result).' ('.trim($val).')', TRUE);
			}
		}
		return $this->response->setJSON('true', TRUE);
	}

	function get_csrf_hash()
	{
		return $this->response->setJSON(csrf_hash(), TRUE);
	}
}
