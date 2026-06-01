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
namespace App\Models\Gateway;

/**
 * Clickatell_model Class
 *
 * Handle all messages database activity
 * for Clickatell <http://clickatell.com>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
// require_once('Nongammu_model.php');

class ClickatellModel extends NongammuModel {

	private $gateway;
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
	{
		parent::__construct($db, $validation);
		$this->gateway = $this->config->item('gateway');

		if (empty($this->gateway['url']))
		{
			$this->gateway['url'] = 'http://api.clickatell.com';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Send Messages (Still POC)
	 * Using HTTP API <http://www.clickatell.com/apis-scripts/apis/http-s/>
	 *
	 * @return void
	 */
	function really_send_messages($data)
	{
		$gateway = $this->gateway;
		file_get_contents($gateway['url'].'/http/sendmsg?user='.$gateway['username'].
			'&password='.$gateway['password'].'&api_id='.$gateway['api_id'].'&to='.$data['dest'].'&text='.urlencode($data['message']));
	}
}
