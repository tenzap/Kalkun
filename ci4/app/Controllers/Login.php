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

use App\Libraries\Language;

/**
 * Login Class
 *
 * @package		Kalkun
 * @subpackage	Login
 * @category	Controllers
 */
class Login extends BaseController {

	public $idiom = 'english';
	private $session = null;

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
				// Use browser requested language
				$locale = service('negotiator')->language(Language::supported_locales());
				$this->idiom = Language::locale_to_idiom($locale);
			}
		}
		$locale = Language::$idiom_to_locale[$this->idiom];
		service('language', $locale)->load('kalkun_lang');
		$this->session = session();
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * Display login form and handle login process
	 *
	 * @access	public
	 */
	public function index()
	{
		helper(['html', 'form']);
		//helper('form');
		$this->session->setFlashdata(
			'bef_login_post_data',
			$this->session->setFlashdata('bef_login_post_data')
		);
		if ($this->request->is('post') && empty($this->request->getPost('change_language')))
		{
			return model('KalkunModel')->login();
		}

		$data['idiom'] = $this->idiom;
		$data['language_list'] = service('language')->kalkun_supported_languages();
		$data['r_url'] = service('request')->getGet('r_url') ?? '';
		return view('main/login', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Logout
	 *
	 * Logout process, destroy user session
	 *
	 * @access	public
	 */
	function logout()
	{
		$this->session->destroy();
		$_SESSION = array();
		return redirect()->to('login');
	}

	// --------------------------------------------------------------------

	/**
	 * Forgot Password
	 *
	 * Forgot password form
	 *
	 * @access	public
	 */
	public function forgot_password()
	{
		$this->Message_model = model('MessageModel');

		if ($this->request->is('POST') && empty($this->request->getPost('change_language')))
		{
			$token = model('KalkunModel')->forgot_password();

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
				$data['message'] = tr_raw('To reset your Kalkun password please visit {0}', NULL, site_url('login/password_reset?token='.$token['token']).'&l='.$this->idiom);
				$data['delivery_report'] = 'default';
				$data['uid'] = 1;
				$this->Message_model->send_messages($data);
			}
			if (empty($this->session->setFlashdata('errorlogin')))
			{
				$this->session->setFlashdata('errorlogin', tr_raw('If you are a registered user, a SMS has been sent to you.'));
			}
			return redirect()->to('login/forgot_password?l='.$this->idiom);
		}
		$data['language_list'] = service('language')->kalkun_supported_languages();
		$data['idiom'] = $this->idiom;
		helper(['html', 'form']);
		return view('main/forgot_password', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Password Reset
	 *
	 * Password reset form
	 *
	 * @access	public
	 */
	function password_reset()
	{
		helper(['html', 'form']);

		$password_submitted = FALSE;

		if ($this->request->is('GET')) {
			$token = $this->request->getGet('token');
		}

		if ($this->request->is('POST')) {
			$token = $this->request->getPost('token');
			$password_submitted = empty($this->request->getPost('change_language'));
		}

		$user_token = model('KalkunModel')->valid_token($token);

		if ($user_token === FALSE)
		{
			$this->session->setFlashdata('errorlogin', tr_raw('Token invalid.'));
			return redirect()->to('login/forgot_password?l='.$this->idiom);
		}
		else
		{
			if ($password_submitted)
			{
				model('KalkunModel')->update_password($user_token['id_user']);
				model('KalkunModel')->delete_token($user_token['id_user']);
				$this->session->setFlashdata('errorlogin', tr_raw('Password changed successfully.'));
				return redirect()->to('login?l='.$this->idiom);
			}
			else
			{
				$data['token'] = $token;
				$data['idiom'] = $this->idiom;
				$data['language_list'] = service('language')->kalkun_supported_languages();
				$data['idiom'] = $this->idiom;
				return view('main/password_reset', $data);
			}
		}
	}
}
