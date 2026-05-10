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

/**
 * Login Class
 *
 * @package		Kalkun
 * @subpackage	Login
 * @category	Controllers
 */
class Login extends BaseController {

	public $idiom = 'english';

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{

		// language
		helper('i18n');
		$this->request = service('request');
		if ($this->request->getPost('idiom') !== NULL)
		{
			$this->idiom = $this->request->getPost('idiom');
		}
		else
		{
			if ($this->request->getVar('l') !== NULL)
			{
				$this->idiom = $this->request->getVar('l');
			}
			else
			{
				$this->idiom = service('language')->get_idiom();
			}
		}
		//service('language')->setLocale(service('language')::$idiom_to_locale[$this->idiom]);
		service('language')->load('kalkun_lang', service('language')::$idiom_to_locale[$this->idiom]);
		$this->session = session();
		$this->Kalkun_model = model('KalkunModel');
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * Display login form and handle login process
	 *
	 * @access	public
	 */
	public function getIndex(): string
	{
		helper(['html', 'form']);
		//helper('form');
		$this->session->setFlashdata(
			'bef_login_post_data',
			$this->session->setFlashdata('bef_login_post_data')
		);
		if ($this->request->is('post') && empty($this->request->getPost('change_language')))
		{
			$this->Kalkun_model->login();
		}

		$data['idiom'] = $this->idiom;
		$data['language_list'] = service('language')->kalkun_supported_languages();
		$data['r_url'] = service('request')->getGet('r_url') ?? '';
		return view('main/login', $data);
	}

	public function postIndex(): string
	{
		return $this->getIndex();
	}

	// --------------------------------------------------------------------

	/**
	 * Logout
	 *
	 * Logout process, destroy user session
	 *
	 * @access	public
	 */
	function getLogout()
	{
		$this->session->destroy();
		redirect('login');
	}

	// --------------------------------------------------------------------

	/**
	 * Forgot Password
	 *
	 * Forgot password form
	 *
	 * @access	public
	 */
	function getForgot_password()
	{
		//$this->load->model('Message_model'); //TODO
		helper(['html', 'form']);

		if ($_POST && empty($this->request->getPost('change_language')))
		{
			$token = $this->Kalkun_model->forgot_password();

			if ( ! $token)
			{
				// Remain silent
			}
			else
			{
				// Send token to user
				$data['class'] = '1';
				$data['dest'] = $token['phone'];
				$data['date'] = date('Y-m-d H:i:s');
				$data['message'] = tr_raw('To reset your Kalkun password please visit {0}', NULL, site_url('login/password_reset/'.$token['token']).'?l='.$this->idiom);
				$data['delivery_report'] = 'default';
				$data['uid'] = 1;
				//$this->Message_model->send_messages($data); //TODO
			}
			if (empty($this->session->setFlashdata('errorlogin')))
			{
				$this->session->setFlashdata('errorlogin', tr_raw('If you are a registered user, a SMS has been sent to you.'));
			}
			redirect('login/forgot_password?l='.$this->idiom);
		}
		$data['language_list'] = service('language')->kalkun_supported_languages();
		$data['idiom'] = $this->idiom;
		view('main/forgot_password', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Password Reset
	 *
	 * Password reset form
	 *
	 * @access	public
	 */
	function getPassword_reset($token = NULL)
	{
		helper(['html', 'form']);

		$password_submitted = ($_POST && empty($this->request->getPost('change_language')));

		if ($password_submitted)
		{
			$token = $this->request->getPost('token');
		}

		$user_token = $this->Kalkun_model->valid_token($token);

		if ($user_token === FALSE)
		{
			$this->session->setFlashdata('errorlogin', tr_raw('Token invalid.'));
			redirect('login/forgot_password?l='.$this->idiom);
		}
		else
		{
			if ($password_submitted)
			{
				$this->Kalkun_model->update_password($user_token['id_user']);
				$this->Kalkun_model->delete_token($user_token['id_user']);
				$this->session->setFlashdata('errorlogin', tr_raw('Password changed successfully.'));
				redirect('login?l='.$this->idiom);
			}
			else
			{
				$data['token'] = $token;
				$data['idiom'] = $this->idiom;
				$data['language_list'] = $this->lang->kalkun_supported_languages();
				$data['idiom'] = $this->idiom;
				view('main/password_reset', $data);
			}
		}
	}
}
