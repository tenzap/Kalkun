<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2026 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

namespace App\TestUtils;

use App\Libraries\DBEngineProps;

class MockInvalidDBEngineProps extends DBEngineProps {

	function __construct($driver)
	{
		parent::__construct($driver);
	}

	public function getName()
	{
		return 'name';
	}
	public function getFile()
	{
		return 'file';
	}
	public function getHuman()
	{
		return 'Invalid Database Engine (for testing)';
	}
	public function getDriver()
	{
		return 'invalid_value';
	}
}
