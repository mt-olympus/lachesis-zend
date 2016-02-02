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

    public function __construct($enabled, $logDir = null)
    {
        $this->enabled = (bool) $enabled;

        if ($logDir != null) {
            $this->logDir = $logDir;
        }
    }

    public function profilerFinish()
    {
        $ret = parent::profilerFinish();
        $profiles = $ret->getProfiles();
        foreach ($profiles as $profile) {
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
                'type' => $queryType,
                'sql'     => $profile['sql'],
                'start'   => $profile['start'],
                'end'     => $profile['end'],
                'elapsed' => $profile['elapse'],
            ];
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
}
