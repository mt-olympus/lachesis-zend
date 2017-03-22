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

        $data = [];
        if ($container->has('Request')) {
            $data = $this->prepareData($container->get('Request'));
        }
        $adapter->setProfiler(new Lachesis($lachesisConfig, $data));

        return $adapter;
    }

    private function prepareData($request)
    {
        $data = [];

        if (!method_exists($request, 'getHeader')) {
            return $data;
        }

        $header = $request->getHeader('X-Request-Id');
        if ($header) {
            $requestId = $header->getFieldValue();
            $data['request_id'] = $requestId;
        }
        $header = $request->getHeader('X-Request-Name');
        if ($header) {
            $requestName = $header->getFieldValue();
            $data['request_name'] = $requestName;
        }
        return $data;
    }
}
