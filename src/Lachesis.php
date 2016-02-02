<?php
namespace Lachesis;

use Zend\Db\Adapter\Adapter;

class Lachesis extends Adapter
{
    private $profiler;

    /**
     * {@inheritDoc}
     * @see \Zend\Db\Adapter\Adapter::query()
     */
    public function query($sql, $parametersOrQueryMode = self::QUERY_MODE_PREPARE, \Zend\Db\ResultSet\ResultSetInterface $resultPrototype = null)
    {
        $this->profiler->startQuery($sql);
        $return = parent::query($sql, $parametersOrQueryMode, $resultPrototype);
        $this->profiler->endQuery();
        return $return;

    }

    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }
}
