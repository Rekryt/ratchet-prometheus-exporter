<?php
namespace RatchetPrometheusExporter;

use Exception;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;

/**
 * Class HttpRegistryServer
 * @package RatchetPrometheusExporter
 */
class HttpRegistryServer implements HttpServerInterface {
    /**
     * @var CollectorRegistry
     */
    protected CollectorRegistry $registry;

    /**
     * HttpRegistryServer constructor
     *
     * @param CollectorRegistry $collectorRegistry
     */
    public function __construct(CollectorRegistry $collectorRegistry) {
        $this->registry = $collectorRegistry;
    }

    /**
     * @param ConnectionInterface $conn
     * @param RequestInterface|null $request
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $renderer = new RenderTextFormat();
        $body = $renderer->render($this->registry->getMetricFamilySamples());

        $e = "\r\n";
        $headers = [
            'HTTP/1.1 200 OK',
            'Date: ' . date('D') . ', ' . date('m') . ' ' . date('M') . ' ' . date('Y') . ' ' . date('H:i:s') . ' GMT',
            'Server: RatchetPrometheusExporter',
            'Connection: close',
            'Content-Type: ' . RenderTextFormat::MIME_TYPE,
            'Content-Length: ' . strlen($body),
        ];

        $headers = implode($e, $headers) . $e . $e;

        $conn->send($headers . $body);
        $conn->close();
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onClose(ConnectionInterface $conn) {
    }

    /**
     * @param ConnectionInterface $conn
     * @param Exception $e
     */
    function onError(ConnectionInterface $conn, Exception $e) {
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    function onMessage(ConnectionInterface $from, $msg) {
    }
}
