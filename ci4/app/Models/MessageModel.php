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
use CodeIgniter\Config\Factories;
/**
 * Message_model Class
 *
 * The real function should be handled by it's gateway engine
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
class MessageModel extends Model {

	protected $table = 'DUMMY';
	protected $allowedFields = [];
	private $gateway = '';

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
	{
		parent::__construct($db, $validation);
		$gateway_config = config('Kalkun')->gateway;
		$gateway_class = ucwords($gateway_config['engine']).'Model';
		//require_once('gateway/'.$gateway_config['engine'].'_model.php');
		$this->gateway = model('Gateway/'.$gateway_class);
	}

	public function __call($name, $arguments)
	{
		$res = call_user_func_array(array($this->gateway, $name), $arguments);
		return $res;
	}
}
