<?php
/**
 * Ratchet Prometheus Exporter
 *
 * @package RatchetPrometheusExporter
 * @author Krupkin Sergey <rekrytkw@gmail.com>
 */
namespace RatchetPrometheusExporter;

// Load dependencies
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use Error;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Storage\Redis;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

// Load environments
if (is_file('.env')) {
    Dotenv::createImmutable(__DIR__)->load();
}
$host = $_ENV['HOST'] ? $_ENV['HOST'] : '0.0.0.0';
$port = $_ENV['PORT'] ? $_ENV['PORT'] : 80;

// Get react Loop
$loop = Loop::get();

// Create socket and http server
$socket = new SocketServer($host . ':' . $port, [], $loop);

/**
 * Set redis options
 * @see https://github.com/promphp/prometheus_client_php#usage
 */
Redis::setDefaultOptions([
    'host' => $_ENV['REDIS_HOST'] ? $_ENV['REDIS_HOST'] : 'redis',
    'port' => $_ENV['REDIS_PORT'] ? $_ENV['REDIS_PORT'] : 6379,
    'password' => $_ENV['REDIS_PASSWORD'] ? $_ENV['REDIS_PASSWORD'] : null,
    'database' => $_ENV['REDIS_DB'] ? $_ENV['REDIS_DB'] : 0,
    'timeout' => 0.1, // in seconds
    'read_timeout' => '10', // in seconds
    'persistent_connections' => false,
]);

$registry = new CollectorRegistry(new Redis());
$http = new HttpServer(new HttpRegistryServer($registry));

// Add HTTP server to loop
$server = new IoServer($http, $socket, $loop);

// Add timers from json config to loop
if (is_file('config.json')) {
    $config = json_decode(file_get_contents('config.json'));
    if (isset($config->timers)) {
        foreach ($config->timers as $timer) {
            try {
                new Timer($registry, $timer);
            } catch (MetricsRegistrationException $e) {
                echo $e->getMessage() . PHP_EOL;
            } catch (Error $e) {
                die($e->getMessage() . PHP_EOL);
            }
        }
    }
}

// Starting server
echo 'Listening on: ' . $host . ':' . $port . PHP_EOL;
$server->run();
