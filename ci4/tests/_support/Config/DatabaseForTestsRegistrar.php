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

namespace Tests\Support\Config;

use App\TestUtils\DBVars;
use App\TestUtils\KalkunDatabaseTestTrait;

require_once __DIR__.'/../../testutils/DBVars.php';
require_once __DIR__.'/../../testutils/KalkunDatabaseTestTrait.php';

class DatabaseForTestsRegistrar
{
    protected static array $dbConfig = [
        "pgsql" => [
            'DSN'        => '',
            'hostname'   => 'localhost',
            'username'   => DBVars::USERNAME,
            'password'   => DBVars::PASSWORD,
            'database'   => DBVars::DATABASE,
            'schema'     => 'public',
            'DBDriver'   => 'Postgre',
            'DBPrefix'   => '',
            'pConnect'   => false,
            'DBDebug'    => true,
            'charset'    => 'utf8',
            'swapPre'    => '',
            'failover'   => [],
            'port'       => 5432,
            'dateFormat' => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ],
        "mysql" => [
            'DSN'          => '',
            'hostname'     => 'localhost',
            'username'     => DBVars::USERNAME,
            'password'     => DBVars::PASSWORD,
            'database'     => DBVars::DATABASE,
            'DBDriver'     => 'MySQLi',
            'DBPrefix'     => '',
            'pConnect'     => false,
            'DBDebug'      => true,
            'charset'      => 'utf8mb4',
            'DBCollat'     => 'utf8mb4_general_ci',
            'swapPre'      => '',
            'encrypt'      => false,
            'compress'     => false,
            'strictOn'     => false,
            'failover'     => [],
            'port'         => 3306,
            'numberNative' => false,
            'foundRows'    => false,
            'dateFormat'   => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ],
        "sqlite" => [
            'DSN'         => '',
            'hostname'    => '127.0.0.1',
            'username'    => '',
            'password'    => '',
            'database'    => '', // Is fed in self::Database()
            'DBDriver'    => 'SQLite3',
            'DBPrefix'    => '',
            'pConnect'    => false,
            'DBDebug'     => true,
            'charset'     => 'utf8',
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => true,
            'failover'    => [],
            'port'        => 3306,
            'foreignKeys' => true,
            'busyTimeout' => 1000,
            'synchronous' => null,
            'dateFormat'  => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ],
        "pgsql_invalid" => [
            'DSN'        => '',
            'hostname'   => 'localhost',
            'username'   => DBVars::USERNAME,
            'password'   => DBVars::PASSWORD,
            'database'   => DBVars::DATABASE.'INVALID',
            'schema'     => 'public',
            'DBDriver'   => 'Postgre',
            'DBPrefix'   => '',
            'pConnect'   => false,
            'DBDebug'    => true,
            'charset'    => 'utf8',
            'swapPre'    => '',
            'failover'   => [],
            'port'       => 5432,
            'dateFormat' => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ],
        "mysql_invalid" => [
            'DSN'          => '',
            'hostname'     => 'localhost',
            'username'     => DBVars::USERNAME,
            'password'     => DBVars::PASSWORD,
            'database'     => DBVars::DATABASE.'INVALID',
            'DBDriver'     => 'MySQLi',
            'DBPrefix'     => '',
            'pConnect'     => false,
            'DBDebug'      => true,
            'charset'      => 'utf8mb4',
            'DBCollat'     => 'utf8mb4_general_ci',
            'swapPre'      => '',
            'encrypt'      => false,
            'compress'     => false,
            'strictOn'     => false,
            'failover'     => [],
            'port'         => 3306,
            'numberNative' => false,
            'foundRows'    => false,
            'dateFormat'   => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ],
        "sqlite_invalid" => [
            'DSN'         => '',
            'hostname'    => '127.0.0.1',
            'username'    => '',
            'password'    => '',
            'database'    => '', // Is fed in self::Database()
            'DBDriver'    => 'SQLite3',
            'DBPrefix'    => '',
            'pConnect'    => false,
            'DBDebug'     => true,
            'charset'     => 'utf8',
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => true,
            'failover'    => [],
            'port'        => 3306,
            'foreignKeys' => true,
            'busyTimeout' => 1000,
            'synchronous' => null,
            'dateFormat'  => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ],
    ];

    public static function Database(): array
    {
        self::$dbConfig['sqlite']['database'] = KalkunDatabaseTestTrait::get_db_path(DBVars::DATABASE);

        $config = [];

        // Under GitHub Actions, we can set an ENV var named 'DB'
        // so that we can test against multiple databases.
        $group = env('DB', 'SQLite3');

        $config['tests'] = self::$dbConfig[$group] ?? [];

        return $config;
    }
}
