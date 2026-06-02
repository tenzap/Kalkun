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
namespace App\Controllers;

use App\TestUtils\KalkunTestCase;
use App\TestUtils\KalkunDatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
//use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

require_once __DIR__.'/../../testutils/KalkunTestCase.php';
require_once __DIR__.'/../../testutils/KalkunDatabaseTestTrait.php';

use Symfony\Component\DomCrawler\Crawler;
use PHPUnit\Framework\Attributes\DataProvider;

class KalkunTest extends KalkunTestCase {

	use FeatureTestTrait;
	use KalkunDatabaseTestTrait;

	public function setUp() : void
	{
		parent::setUp();
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
	}

	public static function database_Provider()
	{
		return self::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_add_folder_POST_no_source_url($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('POST', 'kalkun/add_folder', ['folder_name' => 'folder_name_for_add_folder', 'id_user' => '1', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertRedirectTo('');
		$result->assertStatus(302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_add_folder_POST_with_source_url($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$source_url = 'messages/folder/inbox';

		$result = $this->withSession($session)->call('POST', 'kalkun/add_folder', ['folder_name' => 'folder_name_for_add_folder', 'id_user' => '1', 'source_url' => $source_url, csrf_token() => csrf_hash()]);
		$result->assertRedirectTo($source_url);
		$result->assertStatus(302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_filter_ajaxGET($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$filter_id = '123456789';

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/delete_filter/'.$filter_id);
		$data = $result->response()->getBody();
		$this->assertEmpty($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_filter_ajaxGET_none($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/delete_filter');
		$data = $result->response()->getBody();
		$this->assertEmpty($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_folder_GET_none($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'kalkun/delete_folder');

		// This isn't supported by ci-phpunit-test, so use the workaround below
		//$result->assertHeader('Refresh', '0;url=http://localhostvendor/bin/index.php/');

		// $catched_redirection = $this->CI->output->_status['redirect'];
		// $expected = 'Redirect to ' . $this->CI->config->item('base_url') . 'index.php/';
		// $this->assertEquals($expected, $catched_redirection);
		$result->assertRedirectTo('/');
		$result->assertStatus(302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_folder_GET($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$folder_id = '123456789';
		$result = $this->withSession($session)->call('GET', 'kalkun/delete_folder/'.$folder_id);

		// This isn't supported by ci-phpunit-test, so use the workaround below
		//$result->assertHeader('Refresh', '0;url=http://localhostvendor/bin/index.php/');

		// $catched_redirection = $this->CI->output->_status['redirect'];
		// $expected = 'Redirect to ' . $this->CI->config->item('base_url') . 'index.php/';
		// $this->assertEquals($expected, $catched_redirection);
		$result->assertRedirectTo('/');
		$result->assertStatus(302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_csrf_hash($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'kalkun/get_csrf_hash');
		$data = $result->response()->getBody();
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_statistic_GET_none($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/get_statistic');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$data = $result->response()->getBody();
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_statistic_GET_days($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/get_statistic/days');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$data = $result->response()->getBody();
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_statistic_GET_weeks($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/get_statistic/weeks');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$data = $result->response()->getBody();
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_statistic_GET_months($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/get_statistic/months');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$data = $result->response()->getBody();
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_statistic_GET_invalid($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/get_statistic/invalid');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$data = $result->response()->getBody();
		$this->assertJson($data);
		// Should return same value as 'days'
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_statistic_GET_days_nonadmin($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'user',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/get_statistic/days');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$data = $result->response()->getBody();
		$this->assertJson($data);
	}

	#[DataProvider('database_Provider')]
	public function test_index($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', '/');
		$data = $result->response()->getBody();
		$crawler = new Crawler($data);
		$this->assertEquals('Kalkun / Dashboard', $this->crawler_text($crawler->filter('title')));

		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_index_outgoing_disabled($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		config('Kalkun')->disable_outgoing = TRUE;

		$result = $this->withSession($session)->call('GET', '/');
		$data = $result->response()->getBody();
		$expected = '<div class="warning">Outgoing SMS disabled. Contact system administrator.</div>';
		$this->_assertStringContainsString($expected, $data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_notification($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'kalkun/notification');
		$data = $result->response()->getBody();
		$expected = '<!-- Values are filled in javascript -->';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_notification_ajax($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$headers = [
			'X-Requested-With' => 'XMLHttpRequest',
		];
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'kalkun/notification');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$data = $result->response()->getBody();
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertTrue(array_key_exists('signal', $data_decoded));
		$this->assertTrue(array_key_exists('battery', $data_decoded));
		$this->assertTrue(array_key_exists('status', $data_decoded));
	}



	public static function phone_number_validation_Provider()
	{
		return self::prepend_db_engine([
			'get_valid_number' => ['GET', '+33612345678', 'FR', TRUE],
			'get_invalid_number' => ['GET', '0612345678', '', FALSE],
			'post_valid_number' => ['POST', '+33612345678', 'FR', TRUE],
			'post_invalid_number' => ['POST', '0612345678', '', FALSE],
		]);
	}

	/**
	 * @dataProvider phone_number_validation_Provider
	 */
	#[DataProvider('phone_number_validation_Provider')]
	public function test_phone_number_validation($db_engine, $method, $phone, $region, $expected)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = ['phone' => $phone, 'region' => $region];
		if ($method === 'POST')
		{
			$post_data[csrf_token()] = csrf_hash();
		}

		$result = $this->withSession($session)->call($method, 'kalkun/phone_number_validation', $post_data);
		$data = $result->response()->getBody();
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data);
		if ($expected === TRUE)
		{
			$this->assertEquals('true', $data_decoded);
		}
		else
		{
			$this->assertNotEquals('true', $data_decoded);
		}
	}

	public static function phone_number_validation_multiple_Provider()
	{
		return self::prepend_db_engine([
			'get_valid_number' => ['GET', '+33612345678, +33623456789', 'FR', TRUE],
			'get_valid_number2' => ['GET', '+33612345678, 0623456789', 'FR', TRUE],
			'get_invalid_number' => ['GET', '0612345678, 0623456789', '', FALSE],
			'post_valid_number' => ['POST', '+33612345678, +33623456789', 'FR', TRUE],
			'post_valid_number2' => ['POST', '+33612345678, 0623456789', 'FR', TRUE],
			'post_invalid_number' => ['POST', '0612345678, 0623456789', '', FALSE],
		]);
	}

	/**
	 * @dataProvider phone_number_validation_multiple_Provider
	 */
	#[DataProvider('phone_number_validation_multiple_Provider')]
	public function test_phone_number_validation_multiple($db_engine, $method, $phone, $region, $expected)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = ['phone' => $phone, 'region' => $region];
		if ($method === 'POST')
		{
			$post_data[csrf_token()] = csrf_hash();
		}

		$result = $this->withSession($session)->call($method, 'kalkun/phone_number_validation_multiple', $post_data);
		$data = $result->response()->getBody();
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data);
		if ($expected === TRUE)
		{
			$this->assertEquals('true', $data_decoded);
		}
		else
		{
			$this->assertNotEquals('true', $data_decoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_rename_folder_POST($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$source_url = 'messages/folder/inbox';

		$result = $this->withSession($session)->call('POST', 'kalkun/rename_folder', ['edit_folder_name' => 'folder_name_for_edit_folder', 'id_folder' => '1', 'source_url' => $source_url, csrf_token() => csrf_hash()]);
		$result->assertRedirectTo($source_url);
		$result->assertStatus(302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_invalid($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
		$this->expectExceptionMessage('Page Not Found');
		$result = $this->withSession($session)->call('GET', 'settings/invalid');
		//$result->assertStatus(404);
		//$this->assertResponseCode(404);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_none($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
		$this->expectExceptionMessage('Page Not Found');
		$result = $this->withSession($session)->call('GET', 'settings');
		//$result->assertStatus(404);
		//$this->assertResponseCode(404);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_general($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'settings/general');
		$data = $result->response()->getBody();
		$expected = '<td>Country calling code</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_personal($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'settings/personal');
		$data = $result->response()->getBody();
		$expected = '<td>Username</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_appearance($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'settings/appearance');
		$data = $result->response()->getBody();
		$expected = '<td>Background image</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_password($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'settings/password');
		$data = $result->response()->getBody();
		$expected = '<td>New password</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_general($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = [
			'option' => 'general',
			'language' => 'english',
			'paging' => '20',
			'permanent_delete' => 'false',
			'delivery_report' => 'default',
			'conversation_sort' => 'asc',
			'dial_code' => 'FR',
			csrf_token() => csrf_hash(),
		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/general');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_personal($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkun',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
			csrf_token() => csrf_hash(),
		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/personal');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_personal_change_kalkun_username($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkunNew',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
			csrf_token() => csrf_hash(),
		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/personal');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_personal_change_kalkun_username_demo_mode($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		config('Kalkun')->demo_mode = TRUE;

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkunNew',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
			csrf_token() => csrf_hash(),
		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/personal');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully (except username for kalkun user which can\'t be changed in demo mode)';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_personal_change_to_existing_username($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '2',
			'level' => 'user',
			'username' => 'notKalkun',
		];

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkun',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
			csrf_token() => csrf_hash(),

		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/personal');
		$result->assertStatus(302);
		$expected = 'Username already taken';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_appearance($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = [
			'option' => 'appearance',
			'theme' => 'green',
			'bg_image_option' => 'true',
			csrf_token() => csrf_hash(),

		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/appearance');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_password($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = [
			'option' => 'password',
			'current_password' => 'kalkun',
			'new_password' => 'kalkun_new_password',
			'confirm_password' => 'kalkun_new_password',
			csrf_token() => csrf_hash(),

		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/password');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_password_wrong_current_password($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = [
			'option' => 'password',
			'current_password' => 'kalkun_wrong',
			'new_password' => 'kalkun_new_password',
			'confirm_password' => 'kalkun_new_password',
			csrf_token() => csrf_hash(),

		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/password');
		$result->assertStatus(302);
		$expected = 'Wrong password';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_password_demo_mode($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		config('Kalkun')->demo_mode = TRUE;

		$post_data = [
			'option' => 'password',
			'current_password' => 'kalkun',
			'new_password' => 'kalkun_new_password',
			'confirm_password' => 'kalkun_new_password',
			csrf_token() => csrf_hash(),

		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/password');
		$result->assertStatus(302);
		$expected = 'Password modification forbidden in demo mode.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_filters_insert($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$post_data = [
			'option' => 'filters',
			'id_filter' => '',
			'from' => '+123456',
			'has_the_words' => 'keyword',
			'id_folder' => '1',
			'id_user' => '1',
			csrf_token() => csrf_hash(),

		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/filters');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_save_filters_update($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$this->insert('user_folders')
						->insert('filter', [
							'from' => '+12345600',
							'has_the_words' => 'keyword will be changed',
						])->execute();

		$post_data = [
			'option' => 'filters',
			'id_filter' => '1',
			'from' => '+123456',
			'has_the_words' => 'keyword',
			'id_folder' => '11',
			'id_user' => '1',
			csrf_token() => csrf_hash(),

		];

		$result = $this->withSession($session)->call('POST', 'settings/save', $post_data);
		$result->assertRedirectTo('settings/filters');
		$result->assertStatus(302);
		$expected = 'Settings saved successfully.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);

		$result = $this->db->table('user_filters')
				->where('id_filter', '1')
				->get();
		$this->assertEquals('+123456', $result->getRow()->from);
		$this->assertEquals('keyword', $result->getRow()->has_the_words);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_settings_filters($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'settings/filters');
		$data = $result->response()->getBody();
		$expected = '<a href="javascript:void(0);" id="addnewfilter">Create a new filter</a>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_unread_count($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$session = [
			'loggedin' => 'TRUE',
			'id_user' => '1',
			'level' => 'admin',
			'username' => 'kalkun',
		];

		$result = $this->withSession($session)->call('GET', 'kalkun/unread_count');
		$data = $result->response()->getBody();
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertTrue(array_key_exists('in', $data_decoded));
		$this->assertTrue(array_key_exists('draft', $data_decoded));
		$this->assertTrue(array_key_exists('spam', $data_decoded));
	}
}
