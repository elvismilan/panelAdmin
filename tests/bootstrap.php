<?php

declare(strict_types=1);

use Core\Database;
use Core\RbacCache;
use Core\Session;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

$GLOBALS['__test_cases'] = [];
$GLOBALS['__test_stats'] = ['passed' => 0, 'failed' => 0, 'skipped' => 0];
$GLOBALS['__test_messages'] = [];

final class TestSkip extends RuntimeException
{
}

function test(string $name, callable $callback): void
{
    $GLOBALS['__test_cases'][] = ['name' => $name, 'callback' => $callback];
}

function assert_true(bool $condition, string $message = 'Expected condition to be true.'): void
{
    if ($condition !== true) {
        throw new RuntimeException($message);
    }
}

function assert_false(bool $condition, string $message = 'Expected condition to be false.'): void
{
    if ($condition !== false) {
        throw new RuntimeException($message);
    }
}

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $label = $message !== '' ? $message . ' ' : '';
        throw new RuntimeException($label . 'Expected ' . var_export($expected, true) . ' but got ' . var_export($actual, true) . '.');
    }
}

function assert_null(mixed $actual, string $message = 'Expected value to be null.'): void
{
    if ($actual !== null) {
        throw new RuntimeException($message);
    }
}

function assert_not_null(mixed $actual, string $message = 'Expected value not to be null.'): void
{
    if ($actual === null) {
        throw new RuntimeException($message);
    }
}

function assert_array_has_key(string|int $key, array $array, string $message = ''): void
{
    if (!array_key_exists($key, $array)) {
        throw new RuntimeException($message !== '' ? $message : 'Missing expected array key: ' . $key);
    }
}

function skip_test(string $message): never
{
    throw new TestSkip($message);
}

function reset_server_globals(): void
{
    $_GET = [];
    $_POST = [];
    $_REQUEST = [];
    $_COOKIE = [];
    $_FILES = [];
    $_SERVER = [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
        'SCRIPT_NAME' => '/index.php',
        'HTTP_HOST' => 'localhost',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => '80',
    ];
}

function reset_environment(): void
{
    unset(
        $_ENV['APP_URL'],
        $_ENV['SITE_ROOT'],
        $_ENV['DB_DSN'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        $_ENV['SESSION_COOKIE_SECURE'],
        $_ENV['SESSION_COOKIE_SAMESITE'],
        $_ENV['SESSION_COOKIE_DOMAIN'],
        $_ENV['AUTH_ACTIVE_STATUS'],
        $_ENV['TEST_RESET_EMAIL']
    );
}

function reset_session_state(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        Session::destroy();
    }

    if (session_status() !== PHP_SESSION_NONE) {
        @session_write_close();
    }

    $_SESSION = [];
    if (session_id() !== '') {
        @session_id('');
    }
    @session_id('test_' . bin2hex(random_bytes(8)));
}

function reset_static_cache(string $className, string $property): void
{
    $reflection = new ReflectionClass($className);
    if (!$reflection->hasProperty($property)) {
        return;
    }

    $refProperty = $reflection->getProperty($property);
    $refProperty->setAccessible(true);
    $defaultValue = $refProperty->getDefaultValue();
    $refProperty->setValue(null, $defaultValue);
}

function reset_database_instances(): void
{
    reset_static_cache(Database::class, 'instances');
}

function reset_permission_caches(): void
{
    reset_static_cache(Core\Permission::class, 'requestPermissionMatrix');
    reset_static_cache(Core\Permission::class, 'requestElementMap');
    reset_static_cache(Core\Permission::class, 'requestElementIdByPath');
    reset_static_cache(Core\MenuService::class, 'requestMenuByGroup');
    reset_static_cache(Core\RbacVersion::class, 'requestSyncDone');

    reset_session_state();
    RbacCache::clearSession();
}

function with_sqlite_database(callable $callback): void
{
    $dbFile = sys_get_temp_dir() . '/paneladmin-test-' . bin2hex(random_bytes(6)) . '.sqlite';

    reset_database_instances();
    reset_permission_caches();
    $_ENV['DB_DSN'] = 'sqlite:' . $dbFile;
    $_ENV['DB_USER'] = '';
    $_ENV['DB_PASS'] = '';

    try {
        $db = Database::fromEnv();
        $callback($db->getConnection(), $dbFile);
    } finally {
        reset_database_instances();
        reset_permission_caches();
        if (is_file($dbFile)) {
            @unlink($dbFile);
        }
    }
}

function load_test_files(array $files): void
{
    foreach ($files as $file) {
        require $file;
    }
}

function run_registered_tests(): int
{
    foreach ($GLOBALS['__test_cases'] as $case) {
        $name = (string) ($case['name'] ?? 'unnamed');
        $callback = $case['callback'] ?? null;

        if (!is_callable($callback)) {
            $GLOBALS['__test_messages'][] = ['stream' => 'stderr', 'message' => "[FAIL] {$name} - callback invalido\n"];
            $GLOBALS['__test_stats']['failed']++;
            continue;
        }

        try {
            reset_server_globals();
            reset_environment();
            reset_permission_caches();
            $callback();
            $GLOBALS['__test_messages'][] = ['stream' => 'stdout', 'message' => "[OK] {$name}\n"];
            $GLOBALS['__test_stats']['passed']++;
        } catch (TestSkip $skip) {
            $GLOBALS['__test_messages'][] = ['stream' => 'stdout', 'message' => "[SKIP] {$name} - {$skip->getMessage()}\n"];
            $GLOBALS['__test_stats']['skipped']++;
        } catch (Throwable $e) {
            $GLOBALS['__test_messages'][] = ['stream' => 'stderr', 'message' => "[FAIL] {$name} - {$e->getMessage()}\n"];
            $GLOBALS['__test_stats']['failed']++;
        } finally {
            reset_permission_caches();
            reset_database_instances();
        }
    }

    foreach ($GLOBALS['__test_messages'] as $entry) {
        $stream = ($entry['stream'] ?? 'stdout') === 'stderr' ? STDERR : STDOUT;
        fwrite($stream, (string) ($entry['message'] ?? ''));
    }

    $stats = $GLOBALS['__test_stats'];
    fwrite(STDOUT, sprintf(
        "\nResumen: %d OK, %d FAIL, %d SKIP\n",
        (int) $stats['passed'],
        (int) $stats['failed'],
        (int) $stats['skipped']
    ));

    return (int) $stats['failed'];
}
