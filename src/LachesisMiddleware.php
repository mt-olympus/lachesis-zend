<?php
namespace Lachesis;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Db\Adapter\Adapter;

class LachesisMiddleware
{
    private $lachesis;

    public function __construct(Adapter $adapter)
    {
        $this->lachesis = $adapter->getProfiler();
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param null|callable $next
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        if ($this->lachesis instanceof Lachesis) {
            $this->lachesis->setRequest($request);
        }
        return $next($request, $response);
    }
}
