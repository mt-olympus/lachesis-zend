<?php
namespace Lachesis;

use Zend\Db\Adapter\Profiler\Profiler as ZendProfiler;
use Zend\Db\Adapter\ParameterContainer;
use Psr\Http\Message\RequestInterface;

class Lachesis extends ZendProfiler
{
    const QUERY = 'query';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const SELECT = 'select';

    private $enabled;
    private $logDir = 'data/kharon/lachesis';
    private $logFile = null;
    private $data;
    private $extra;
    private $apiKey = 0;
    private $debug = false;
    private $request;

    public function __construct(array $config, $extra = [])
    {
        $this->enabled = $config['enabled'] ?? true;
        $this->debug = $config['debug'] ?? false;
        $this->apiKey = $config['api_key'] ?? 0;

        $this->logDir = $config['log_dir'] ?? 'data/kharon/lachesis';
        $this->logFile = $config['log_file'] ?? null;

        if (!empty($extra)) {
            $this->extra = $extra;
        }
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function importHeaders(RequestInterface $request)
    {
        $data = [];

        $header = $request->getHeader('X-Request-Id');
        if (count($header) > 0) {
            $data['request_id'] = $header[0];
        }
        $header = $request->getHeader('X-Request-Name');
        if (count($header) > 0) {
            $data['request_name'] = $header[0];
        }
        return $data;
    }

    public function profilerFinish()
    {
        if ($this->enabled == false) {
            return;
        }

        $ret = parent::profilerFinish();
        $profile = $ret->getLastProfile();
        switch (strtolower(substr(ltrim($profile['sql']), 0, 6))) {
            case 'select':
                $queryType = static::SELECT;
                break;
            case 'insert':
                $queryType = static::INSERT;
                break;
            case 'update':
                $queryType = static::UPDATE;
                break;
            case 'delete':
                $queryType = static::DELETE;
                break;
            default:
                $queryType = static::QUERY;
                break;
        }
        $data = [
            'api_key' => $this->apiKey,
            'type' => $queryType,
            'sql'     => $profile['sql'],
            'start'   => $profile['start'],
            'end'     => $profile['end'],
            'elapsed' => round($profile['elapse'] * 1000 * 1000), // in microseconds
        ];
        if (!empty($this->extra)) {
            $data = array_merge($this->extra, $data);
        }
        if ($this->request !== null) {
            $data = array_merge($this->importHeaders($this->request), $data);
        }
        if (isset($profile['parameters'])) {
            if ($profile['parameters'] instanceof ParameterContainer) {
                $data['parameters'] = $profile['parameters']->getNamedArray();
            } elseif (is_array($profile['parameters'])) {
                $data['parameters'] = $profile['parameters'];
            }
        }
        if ($this->debug) {
            $data['stack'] = debug_backtrace();
        }

        $logFile = !empty($this->logFile) ? $this->logFile : $this->logDir . '/sql-' . getmypid() . '-' . microtime(true) . '.kharon';
        file_put_contents($logFile, json_encode($data, null, 100) . PHP_EOL, FILE_APPEND);
    }
}
