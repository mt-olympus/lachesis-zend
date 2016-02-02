<?php
namespace Lachesis;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;

class Factory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $lachesisConfig = isset($config['lachesis']) ? $config['lachesis'] : [
            'enabled' => true,
        ];
        if (!isset($lachesisConfig['log_dir'])) {
            $lachesisConfig['log_dir'] = 'data/kharon/lachesis';
        }

        $adapter = new Adapter($serviceLocator->get('config')['db']);
        $adapter->setProfiler(new Lachesis($lachesisConfig['enabled'], $lachesisConfig['log_dir']));


        return $adapter;
    }
}
