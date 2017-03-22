<?php
namespace Lachesis;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        $lachesisConfig = isset($config['lachesis']) ? $config['lachesis'] : [
            'enabled' => false,
        ];

        $lachesisConfig['log_dir'] = $lachesisConfig['log_dir'] ?? 'data/kharon/lachesis';
        $lachesisConfig['log_file'] = $lachesisConfig['log_file'] ?? null;
        $enabled = $config['lachesis']['enabled'] ?? false;
        $debug = $config['lachesis']['debug'] ?? false;

        $adapter = new Adapter($config['db'] ?? []);
        if ($enabled == false) {
            return $adapter;
        }

        $adapter->setProfiler(new Lachesis($lachesisConfig));

        return $adapter;
    }
}
