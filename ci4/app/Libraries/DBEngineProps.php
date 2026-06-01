<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2024 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */
namespace App\Libraries;

class DBEngineProps {

	private $Postgre = [
		'name' => 'postgre',
		'file' => 'pgsql',
		'human' => 'PostgreSQL',
		'driver' => 'pgsql',
		];

	private $mysql = [
		'name' => 'mysql',
		'file' => 'mysql',
		'human' => 'MySQL',
		'driver' => 'mysql',
		];

	private $MySQLi = [
		'name' => 'mysqli',
		'file' => 'mysql',
		'human' => 'MySQLi',
		'driver' => 'mysqli',
		];

	private $SQLite3 = [
		'name' => 'sqlite',
		'file' => 'sqlite',
		'human' => 'SQLite3',
		'driver' => 'sqlite3',
		];

	private $DBDriver = '';

	function __construct(string $driver)
	{
		// valid and supported driver
		$valid_driver = array('Postgre', 'MySQLi', 'SQLite3');

		if ( ! in_array($driver, $valid_driver))
		{
			//show_error("Database driver you're using is not supported", 500);
			// CI4-TODO
			throw new \RuntimeException("Database driver you're using is not supported");
		}

		// driver is the value defined in the CI4 Database group configuration under "DBDriver" key.
		$this->DBDriver = $driver;
	}

	public function getName()
	{
		return $this->{$this->DBDriver}['name'];
	}
	public function getFile()
	{
		return $this->{$this->DBDriver}['file'];
	}
	public function getHuman()
	{
		return $this->{$this->DBDriver}['human'];
	}
	public function getDriver()
	{
		return $this->{$this->DBDriver}['driver'];
	}
}
