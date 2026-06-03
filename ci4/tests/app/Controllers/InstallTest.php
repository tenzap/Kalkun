<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2022-2024 Kalkun dev team
 * @author Kalkun dev team
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */
namespace App\Controllers;

use App\TestUtils\KalkunTestCase;
use App\TestUtils\KalkunDatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

use CodeIgniter\Config\Factories;
use App\TestUtils\MockInvalidDBEngineProps;

require_once __DIR__.'/../../testutils/KalkunTestCase.php';
require_once __DIR__.'/../../testutils/KalkunDatabaseTestTrait.php';
require_once __DIR__.'/../../testutils/MockInvalidDBEngineProps.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class InstallTest extends KalkunTestCase {

	use FeatureTestTrait;
	use KalkunDatabaseTestTrait;

	public function setUp() : void
	{
		parent::setUp();
		if ( ! file_exists(FCPATH . 'install'))
		{
			file_put_contents(FCPATH . 'install', '');
		}
	}

	public function test_index()
	{
		$result = $this->call('GET', 'install');
		$data = $result->response()->getBody();
		$this->_assertStringContainsString('<h1 style="float: left">Kalkun installation assistant</h1>', $data);
		$this->assertValidHtml($data);
	}

	public function test_index_slash()
	{
		$result = $this->call('GET', '');
		$data = $result->response()->getBody();
		$result->assertRedirectTo('install');
		$result->assertStatus(302);
	}

	// CI4-TODO
	public function test_index_disabled()
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}

		$result = $this->call('GET', 'install');
		$data = $result->response()->getBody();
		$expected = 'Installation has been disabled by the administrator.';

		$this->assertResponseCode(403);
		$this->_assertStringContainsString($expected, $data);

		$this->assertValidHtmlSnippet($data);
	}

	public function test_config_setup_GET()
	{
		$result = $this->call('GET', 'install/config_setup');
		$data = $result->response()->getBody();
		$this->_assertStringContainsString('<h1>Final configuration steps</h1>', $data);

		$this->assertValidHtml($data);
	}

	public function test_config_setup_POST_remove_install_file()
	{
		$result = $this->call('POST', 'install/config_setup', ['remove_install_file' => 'remove', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$this->_assertStringContainsString('<h1>Final configuration steps</h1>', $data);

		$this->assertValidHtml($data);
	}

	public function test_config_setup_POST_install_not_writable()
	{
		$dir = FCPATH;

		// Store original mode to restore it later on.
		$mode = substr(sprintf('%o', fileperms($dir)), -4);
		$modeint = intval(base_convert($mode, 8, 10));

		// Set to read-only
		chmod($dir, 0555);

		$result = $this->call('POST', 'install/config_setup', ['remove_install_file' => 'remove', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$this->_assertStringContainsString('You must remove the file manually.', $data);

		// restore original mode.
		chmod($dir, $modeint);
	}

	#[DataProvider('database_Provider')]
	public function test_requirement_check($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);

		$result = $this->call('GET', 'install/requirement_check');
		$data = $result->response()->getBody();

		$expected = '<h1>Requirements check</h1>';
		$this->_assertStringContainsString($expected, $data);

		$expected = '<input type="submit" name="submit" value="Next ›" class="button">';
		$this->_assertStringContainsString($expected, $data);

		$this->assertValidHtml($data);
	}

	// CI4-TODO
	public function test_requirement_check_error()
	{
		$this->DBSetup([
			'engine' => 'sqlite',
		]);

		$invalidDBEngineProps = new MockInvalidDBEngineProps("SQLite3");
		Factories::injectMock('libraries', 'DBEngineProps', $invalidDBEngineProps);

		$result = $this->call('GET', 'install/requirement_check');
		$data = $result->response()->getBody();

		$expected = '<h1>Requirements check</h1>';
		$this->_assertStringContainsString($expected, $data);

		$expected = '<p>Unfortunately, your system does not meet the minimum requirements to run Kalkun. Please update your system to meet the above requirements. Then click on button to check again.</p>';
		$this->_assertStringContainsString($expected, $data);
	}

	public static function database_Provider()
	{
		return self::$db_engines_to_test;
	}

	#[DataProvider('database_setup_run_db_setupProvider')]
	public function test_database_setup_GET($db_engine, $config)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config($config);
		$this->DBConnect();

		$result = $this->call('GET', 'install/database_setup');
		$data = $result->response()->getBody();
		// TODO: really check the different output depending on the $config.
		$expected = '<input type="submit" name="submit" value="‹ Previous" class="button">';
		$this->_assertStringContainsString($expected, $data);

		$this->assertValidHtml($data);
	}

	#[DataProvider('database_Provider')]
	#[RunInSeparateProcess]
	public function test_database_setup_GET_with_db_exception($db_engine)
	{
		$db = 'kalkun_testing_missing_db';

		$this->DBSetup([
			'database' => $db,
			'engine' => $db_engine
		]);
		//$this->DBConnect(); // Don't call this here so that we can see that DB connection fails in "Install" controller. Otherwise, it would fail here.

		if ($db_engine === 'sqlite')
		{
			$this->markTestIncomplete('FIXME: haven\'t found yet how to catch error opening sqlite file.');

			$dir = dirname($this->db($db_engine, $db));

			// Store original mode to restore it later on.
			$mode = substr(sprintf('%o', fileperms($dir)), -4);
			$modeint = intval(base_convert($mode, 8, 10));

			// Set dir to read-only & non-executable
			chmod($dir, 0444);
		}

		$result = $this->call('GET', 'install/database_setup');
		$data = $result->response()->getBody();

		if ($db_engine === 'sqlite')
		{
			// restore original mode.
			chmod($dir, $modeint);

			$this->assertResponseCode(500);
		}
		else
		{
			$expected = '<p class="red">There was a problem when trying to load the database.</p>';
			$this->_assertStringContainsString($expected, $data);
		}
	}

	public static function database_setup_run_db_setupProvider()
	{
		return self::prepend_db_engine([
			'gammu with pbk, fresh kalkun' => ['gammu_pbk_kalkun_fresh_install_by_installer'],
			'gammu with pbk, update kalkun 0.6' => ['gammu_pbk_kalkun_upgrade_from_0.6'],
			'gammu with pbk, update kalkun 0.7' => ['gammu_pbk_kalkun_upgrade_from_0.7'],
			'gammu with pbk, update kalkun 0.8.0' => ['gammu_pbk_kalkun_upgrade_from_0.8.0'],
			'gammu with pbk, update kalkun 0.8.3' => ['gammu_pbk_kalkun_upgrade_from_0.8.3'],
			'gammu without pbk, fresh kalkun' => ['gammu_no_pbk_kalkun_fresh_install_by_installer'],
			'gammu without pbk, update kalkun 0.8.0' => ['gammu_no_pbk_kalkun_upgrade_from_0.8.0'],
			'gammu without pbk, update kalkun 0.8.3' => ['gammu_no_pbk_kalkun_upgrade_from_0.8.3'],
		]);
	}

	/**
	 * for sqlite and other DB, this error might be displayed:
	 *	Parse error near line 29: table user_settings has 10 columns but 9 values were supplied
	 * This was fixed in 0.8.1 commit 04ff138ef2f83b538dd56b9ae40914227ac8806c
	 *
	 * This test requires to remove the "static" from CI3's DB_driver.php, line "static $preg_ec = array();"
	 * Otherwise, escaping for the DB query would fail in some cases (when switching DB engine).
	 */
	#[DataProvider('database_setup_run_db_setupProvider')]
	public function test_database_setup_POST_run_db_setup($db_engine, $config)
	{
		$this->DBSetup([
			'engine' => $db_engine
		]);
		$this->setup_config($config);
		$this->DBConnect();

		$result = $this->call('POST', 'install/database_setup', ['action' => 'run_db_setup', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$expected = '<input type="submit" name="submit" value="Continue ›" class="button">';
		$this->_assertStringContainsString($expected, $data);
	}

	public static function uses_default_encryption_keyProvider()
	{
		return [
			'default_enc_key 1' => [hex2bin(''), TRUE],
			'default_enc_key 2' => ['', TRUE],
			'non default_enc_key 1' => ['nonDefaultKey', FALSE],
		];
	}

	#[DataProvider('uses_default_encryption_keyProvider')]
	public function test_uses_default_encryption_key($enc_key, $expected)
	{
		config('Encryption')->key = $enc_key;

		$this->install = new Install;
		$data = $this->install->_uses_default_encryption_key();
		$this->assertEquals($expected, $data);
	}

	public function test_method_404()
	{
		$this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
		$this->_expectExceptionMessageMatches("/Can't find a route for/");
		$result = $this->call('GET', 'welcome/method_not_exist');
		$data = $result->response()->getBody();
	}

	public function test_APPPATH()
	{
		$actual = realpath(APPPATH);
		$expected = realpath(__DIR__ . '/../../../app');
		$this->assertEquals(
			$expected,
			$actual,
			'Your APPPATH seems to be wrong. Check your $appDirectory in app/Config/Paths.php'
		);
	}
}
