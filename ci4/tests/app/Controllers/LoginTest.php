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

use PHPUnit\Framework\Attributes\DataProvider;

class LoginTest extends KalkunTestCase {

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

	#[DataProvider('database_Provider')]
	public function test_login_GET_form($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		//$result = $this->call('GET', 'login?l=french');
		// $result = $this->withUri('http://localhost/kalkun-git/ci4/public/login?l=french')
		//   ->controller(Login::class)
		//   ->execute('getIndex');
		$result = $this->call('GET', 'login?l=french');
		$data = $result->response()->getBody();

		$expected = '<title>Kalkun - Se connecter</title>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	// This is used in config_setup.php when clicking on "Log in" at the bottom of the page.
	#[DataProvider('database_Provider')]
	public function test_login_POST_form($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$result = $this->call('POST', '/', [ 'idiom' => 'french', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertRedirectTo('login?l=french');
		$result->assertStatus(302);
	}

	#[DataProvider('database_Provider')]
	public function test_login_POST_success($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('POST', 'login', ['username' => 'kalkun', 'password' => 'kalkun', 'idiom' => 'english', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertRedirectTo('kalkun');
		$result->assertStatus(302);
	}

	#[DataProvider('database_Provider')]
	public function test_login_POST_failure($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('POST', 'login', ['username' => 'kalkun', 'password' => 'wrong_password', 'idiom' => 'english', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertSessionHas('errorlogin', 'Username or password are incorrect.');
	}

	#[DataProvider('database_Provider')]
	public function test_logout($db_engine)
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
		$result = $this->withSession($session)->call('GET', 'logout');
		$data = $result->response()->getBody();

		// Check that session is closed
		$this->assertEquals(TRUE, session_status() === PHP_SESSION_NONE);
		$result->assertNull($_SESSION);
		$result->assertRedirectTo('login');
		$result->assertStatus(302);
	}

	#[DataProvider('database_Provider')]
	public function test_forgot_password_GET_form($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('GET', 'login/forgot_password');
		$data = $result->response()->getBody();
		$expected = '<title>Kalkun - Forgot your password?</title>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	#[DataProvider('database_Provider')]
	public function test_forgot_password_POST_username($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('POST', 'login/forgot_password', ['username' => 'kalkun', 'idiom' => 'english', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$expected = 'If you are a registered user, a SMS has been sent to you.';
		$result->assertSessionHas('errorlogin', $expected);
	}

	#[DataProvider('database_Provider')]
	public function test_forgot_password_POST_phone($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('POST', 'login/forgot_password', ['phone' => '+123456', 'idiom' => 'english', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertRedirectTo('login/forgot_password?l=english');
		$result->assertStatus(302);
		$expected = 'If you are a registered user, a SMS has been sent to you.';
		$result->assertSessionHas('errorlogin', $expected);
	}

	#[DataProvider('database_Provider')]
	public function test_password_reset_POST_valid_token_new_password($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$token = 'my_token';
		$this->insert('user_forgot_password', ['token' => $token])->execute();

		$result = $this->call('POST', 'login/password_reset', ['token' => $token, 'new_password' => 'my_new_password', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertRedirectTo('login?l=english');
		$result->assertStatus(302);

		$expected = 'Password changed successfully.';
		$result->assertSessionHas('errorlogin', $expected);
	}

	#[DataProvider('database_Provider')]
	public function test_password_reset_GET_form_valid_token($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$token = 'my_token';
		$this->insert('user_forgot_password', ['token' => $token])->execute();

		$result = $this->call('GET', 'login/password_reset', ['token' => $token]);
		$data = $result->response()->getBody();
		$expected = '<title>Kalkun - Password reset</title>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	#[DataProvider('database_Provider')]
	public function test_password_reset_GET_form_expired_token($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$token = 'my_token';
		$this->insert('user_forgot_password', [
			'token' => $token,
			'valid_until' => date('Y-m-d H:i:s', mktime(date('H'), date('i') - 30, date('s'), date('m'), date('d'), date('Y'))),
		])->execute();

		$result = $this->call('GET', 'login/password_reset', ['token' => $token]);
		$data = $result->response()->getBody();
		$result->assertSessionHas('errorlogin', 'Token invalid.');
		$result->assertRedirectTo('login/forgot_password?l=english');
		$result->assertStatus(302);
	}

	#[DataProvider('database_Provider')]
	public function test_password_reset_GET_form_invalid_token($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('GET', 'login/password_reset', ['token' => 'invalid_token']);
		$data = $result->response()->getBody();
		$result->assertSessionHas('errorlogin', 'Token invalid.');
		$result->assertRedirectTo('login/forgot_password?l=english');
		$result->assertStatus(302);
	}

	#[DataProvider('database_Provider')]
	public function test_password_reset_POST_invalid_token_new_password($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('POST', 'login/password_reset', ['token' => 'invalid_token', 'new_password' => 'my_new_password', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertSessionHas('errorlogin', 'Token invalid.');
		$result->assertRedirectTo('login/forgot_password?l=english');
		$result->assertStatus(302);
	}

	#[DataProvider('database_Provider')]
	public function test_password_reset_POST_form_invalid_token($db_engine)
	{
		$this->DBSetup([
			'engine' => $db_engine,
		]);
		$this->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');
		$this->DBConnect();

		$result = $this->call('POST', 'login/password_reset', ['token' => 'invalid_token', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$result->assertSessionHas('errorlogin', 'Token invalid.');
		$result->assertRedirectTo('login/forgot_password?l=english');
		$result->assertStatus(302);
	}
}
