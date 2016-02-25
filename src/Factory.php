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

        $data = [];
        if ($serviceLocator->has('Request')) {
            $data = $this->prepareData($serviceLocator->get('Request'));
        }
        $adapter = new Adapter($serviceLocator->get('config')['db']);
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
