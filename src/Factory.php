<?php
namespace Lachesis;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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

        $lachesis = new Lachesis($config['db']);

        if (isset($lachesisConfig['log_dir'])) {
            $lachesis->setLogDir($lachesisConfig['log_dir']);
        }

        return $lachesis;
    }
}
