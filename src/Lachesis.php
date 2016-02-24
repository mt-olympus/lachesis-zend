<?php
namespace Lachesis;

use Zend\Db\Adapter\Profiler\Profiler as ZendProfiler;
use Zend\Db\Adapter\ParameterContainer;

class Lachesis extends ZendProfiler
{
    const QUERY = 'query';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const SELECT = 'select';

    private $enabled;
    private $logDir = 'data/kharon/lachesis';
    private $data;
    private $extra;
    private $apiKey = 0;

    public function __construct($config, $extra = [])
    {
        $this->enabled = isset($config['enabled']) ? (bool) $config['enabled'] : true;
        $this->apiKey = isset($config['api_key']) ? (string) $config['api_key'] : 0;

        if (isset($config['log_dir'])) {
            $this->logDir = $config['log_dir'];
        }

        if (!empty($extra)) {
            $this->extra = $extra;
        }
    }

    public function profilerFinish()
    {
        $ret = parent::profilerFinish();
        $profiles = $ret->getProfiles();
        $profile = end($profiles);
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
        if (isset($profile['parameters'])) {
            if ($profile['parameters'] instanceof ParameterContainer) {
                $data['parameters'] = $profile['parameters']->getNamedArray();
            } elseif (is_array($profile['parameters'])) {
                $data['parameters'] = $profile['parameters'];
            }
        }
        $data['stack'] = debug_backtrace();

        $logFile = $this->logDir . '/sql-' . getmypid() . '-' . microtime(true) . '.kharon';
        file_put_contents($logFile, json_encode($data, null, 100));
    }
}
