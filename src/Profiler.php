<?php
namespace Lachesis;

class Profiler
{
    const CONNECT = 'connect';
    const QUERY = 'query';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const SELECT = 'select';
    const TRANSACTION = 'transaction';
    const COMMIT = 'commit';

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

    public function startQuery($sql, $parameters = null, $stack = null)
    {
        if (!$this->enabled) {
            return null;
        }
        if ($stack == null) {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        switch (strtolower(substr(ltrim($sql), 0, 6))) {
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
        $this->data = [
            'type' => $queryType,
            'sql'     => $sql,
            'start'   => microtime(true),
            'end'     => 0,
            'elapsed' => 0,
            'parameters' => $parameters,
            'stack'   => $stack
        ];
    }
    public function endQuery()
    {
        if (!$this->enabled) {
            return false;
        }
        $now = microtime(true);
        $this->data['end'] = $now;
        $this->data['elapsed'] = $now - $this->data['start'];

        $logFile = $this->logDir . '/sql-' . getmypid() . '-' . $now . '.kharon';
        file_put_contents($logFile, json_encode($this->data, null, 100));
    }
}
