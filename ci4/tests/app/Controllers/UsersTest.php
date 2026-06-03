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
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class UsersTest extends KalkunTestCase {

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
	public function test_index_non_admin($db_engine)
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
			'username' => 'username',
		];

		$result = $this->withSession($session)->call('GET', 'users/index');
		$data = $result->response()->getBody();
		$result->assertRedirectTo('/');
		$result->assertStatus(302);
		$expected = 'Access denied.';
		$flashdata = session()->getFlashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	#[DataProvider('database_Provider')]
	public function test_index_GET($db_engine)
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

		$result = $this->withSession($session)->call('GET', 'users/index');
		$data = $result->response()->getBody();
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>Kalkun SMS<';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	#[DataProvider('database_Provider')]
	public function test_index_GET_no_user_in_db($db_engine)
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

		$this->db->table('user')->where('id_user', '1')->delete();

		$result = $this->withSession($session)->call('GET', 'users/index');
		$data = $result->response()->getBody();
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>No users in the database.<';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	#[DataProvider('database_Provider')]
	public function test_index_GET_ajax($db_engine)
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
		$result = $this->withSession($session)->withHeaders($headers)->call('GET', 'users/index');
		$data = $result->response()->getBody();
		$expected = '<div id="window_title_left">Users</div>';
		$this->assertThat($data, $this->logicalNot($this->stringContains($expected)));
		$expected = '>Kalkun SMS<';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	#[DataProvider('database_Provider')]
	public function test_index_POST_search_name_found($db_engine)
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

		// insert user
		$realname = 'User number 1';
		$this->insert('user', ['realname' => $realname])->execute();

		$result = $this->withSession($session)->call('POST', 'users/index', ['search_name' => 'ser NUm', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>'.$realname.'<';
		$this->_assertStringContainsString($expected, $data);
		// Check that kalkun user is not displayed
		$expected = '>Kalkun SMS<';
		$this->assertThat($data, $this->logicalNot($this->stringContains($expected)));
	}

	#[DataProvider('database_Provider')]
	public function test_index_POST_search_name_nomatch($db_engine)
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

		$result = $this->withSession($session)->call('POST', 'users/index', ['search_name' => 'nomatch', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>User not found<';
		$this->_assertStringContainsString($expected, $data);
		// Check that kalkun user is not displayed
		$expected = '>Kalkun SMS<';
		$this->assertThat($data, $this->logicalNot($this->stringContains($expected)));
		$this->assertValidHtml($data);
	}

	#[DataProvider('database_Provider')]
	#[RunInSeparateProcess]
	public function test_add_user($db_engine)
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

		$result = $this->withSession($session)->call('GET', 'users/add_user');
		$data = $result->response()->getBody();
		$expected = 'phonebook/add_user_process" id="addUser" method="post"';
		$this->_assertStringContainsString($expected, $data);
		$expected = 'id="realname" value=""';
		$this->_assertStringContainsString($expected, $data);
		//$result = $this->withSession($session)->call('GET', 'users/add_user', ['type' => 'normal', 'param1' => '']);
		$this->assertValidHtmlSnippet($data);
	}

	#[DataProvider('database_Provider')]
	#[RunInSeparateProcess]
	public function test_add_user_normal($db_engine)
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

		$result = $this->withSession($session)->call('GET', 'users/add_user', ['type' => 'normal', 'param1' => '']);
		$data = $result->response()->getBody();
		$expected = 'phonebook/add_user_process" id="addUser" method="post"';
		$this->_assertStringContainsString($expected, $data);
		$expected = 'id="realname" value=""';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	#[DataProvider('database_Provider')]
	public function test_add_user_edit($db_engine)
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

		$result = $this->withSession($session)->call('GET', 'users/add_user', ['type' => 'edit', 'param1' => '1']); //param1 is user_id to edit. 1=kalkun
		$data = $result->response()->getBody();
		$expected = 'phonebook/add_user_process" id="addUser" method="post"';
		$this->_assertStringContainsString($expected, $data);
		$expected = 'id="realname" value="Kalkun SMS"';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	#[DataProvider('database_Provider')]
	public function test_add_user_process_new_user($db_engine)
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

		$result = $this->withSession($session)->call('POST', 'users/add_user_process', [
			'realname' => 'New user from Users_tests',
			'username' => 'new_user',
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'password_for_new_user',
			//'id_user' => 'kalkun', // Only in case of edit.
			csrf_token() => csrf_hash(),
		]);
		$data = $result->response()->getBody();
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('User added successfully.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);
	}

	#[DataProvider('database_Provider')]
	public function test_add_user_process_edit_user($db_engine)
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

		$result = $this->withSession($session)->call('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun_edite', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
			csrf_token() => csrf_hash(),
		]);
		$data = $result->response()->getBody();
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('User updated successfully.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$user_record = $this->db->table('user')->where('id_user', '1')->get();
		$this->assertEquals('kalkun_edite', $user_record->getRow()->username);
		$this->assertEquals('user', $user_record->getRow()->level);
		$this->assertEquals('Kalkun SMS new realname', $user_record->getRow()->realname);
		$this->assertEquals('+33699999988', $user_record->getRow()->phone_number);
	}

	#[DataProvider('database_Provider')]
	public function test_add_user_process_edit_user_demomode_forbid_username_change($db_engine)
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

		$result = $this->withSession($session)->call('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun_edite', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'admin',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
			csrf_token() => csrf_hash(),
		]);
		$data = $result->response()->getBody();

		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('Modification of username of "kalkun" user forbidden in demo mode. Username was restored.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$user_record = $this->db->table('user')->where('id_user', '1')->get();
		$this->assertEquals('kalkun', $user_record->getRow()->username);
		$this->assertEquals('admin', $user_record->getRow()->level);
	}

	#[DataProvider('database_Provider')]
	public function test_add_user_process_edit_user_demomode_forbid_level_change($db_engine)
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

		$result = $this->withSession($session)->call('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
			csrf_token() => csrf_hash(),
		]);
		$data = $result->response()->getBody();

		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('Changing role of "kalkun" user forbidden in demo mode. Role was restored.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$user_record = $this->db->table('user')->where('id_user', '1')->get();
		$this->assertEquals('kalkun', $user_record->getRow()->username);
		$this->assertEquals('admin', $user_record->getRow()->level);
	}

	#[DataProvider('database_Provider')]
	public function test_add_user_process_edit_user_demomode_forbid_username_level_change($db_engine)
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

		$result = $this->withSession($session)->call('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun_edite', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
			csrf_token() => csrf_hash(),
		]);
		$data = $result->response()->getBody();

		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('Changing role of "kalkun" user forbidden in demo mode. Role was restored.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$user_record = $this->db->table('user')->where('id_user', '1')->get();
		$this->assertEquals('kalkun', $user_record->getRow()->username);
		$this->assertEquals('admin', $user_record->getRow()->level);
	}

	#[DataProvider('database_Provider')]
	public function test_delete_user($db_engine)
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

		// TODO: launch also when there are messages in inbox, outbox & sentitems for that user, and pbk, user_folder, sms_used

		$result = $this->withSession($session)->call('POST', 'users/delete_user', ['id_user' => '1', csrf_token() => csrf_hash()]);
		$data = $result->response()->getBody();
		$this->assertEmpty($data);
	}
}
