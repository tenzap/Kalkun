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

namespace App\Libraries;

use App\Controllers\BaseController;

/**
 * MY_Controller Class
 *
 * Base controller
 *
 * @package		Kalkun
 * @subpackage	Base
 * @category	Controllers
 */
class MYController extends BaseController {

	protected $Kalkun_model = null;
	protected $Message_model = null;
	protected $User_model = null;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct($login = TRUE)
	{
		// CI4-TODO parent::__construct();

		//$this->load->database();
		$db = db_connect();

		if ($login)
		{
			$this->session = session();

			// session/login check is done in \App\Filters\IsLoggedInFilter

			$this->Kalkun_model = model('KalkunModel');

			// language
			helper('i18n');
			$lang = $this->Kalkun_model->get_setting()->getRow('language') ?? 'english';
			$locale = Language::$idiom_to_locale[$lang];
			service('language', $locale)->load('kalkun_lang');
			service('language', $locale)->load('date_lang');
			// $this->lang->load('kalkun', $lang);
			// $this->lang->load('date', $lang);

			// Message routine
			$this->_message_routine();
		}
	}

	function _message_routine()
	{
		$this->User_model = model('UserModel');
		$this->Message_model = model('MessageModel');
		$uid = $this->session->get('id_user');

		$outbox = $this->Message_model->get_user_outbox($uid);
		foreach ($outbox->getResult() as $tmp)
		{
			$id_message = $tmp->id_outbox;

			// if still on outbox, means message not delivered yet
			if ($this->Message_model->get_messages(array('id_message' => $id_message, 'type' => 'outbox'))->getNumRows() > 0)
			{
				// do nothing
			}
			// if exist on sentitems then update sentitems ownership, else delete user_outbox
			else
			{
				if ($this->Message_model->get_messages(array('id_message' => $id_message, 'type' => 'sentitems'))->getNumRows() > 0)
				{
					$this->Message_model->insert_user_sentitems($id_message, $uid);
					$this->Message_model->delete_user_outbox($id_message);
				}
				else
				{
					$this->Message_model->delete_user_outbox($id_message);
				}
			}
		}
	}
}
