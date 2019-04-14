<?php

declare(strict_types = 1);

namespace Shopsys\PohodaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JShopsysController extends Controller
{
    private const BACKGROUND_REQUEST_ACTION_TIME = 'time';

    /**
     * @Route("/{action}", requirements={"action" = ".*\.php"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jShopsysAction(Request $request, $action)
    {
        $response = new Response();
        $response
            ->setStatusCode(Response::HTTP_OK)
            ->headers->set('Content-Type', 'text/plain');

        if (1 !== preg_match('~^(.*)\.php~', $action, $matchedActions)) {
            return $response
                ->setStatusCode(Response::HTTP_NOT_FOUND)
                ->setContent('Neznámá akce: ' . $action);
        }

        if ($matchedActions[1] === self::BACKGROUND_REQUEST_ACTION_TIME) {
            return $this->timeCheckAction($request, new Response());
        }

        /**
         * JShopsys sends request as "objednavky/objednavky_export.php", etc.
         * These requests are transformed into form, which is defined in app/config/config.yml - shopsys_pohoda.jshopsys.action_routes
         * At the same time, the request from jShopsys can also contain another values inside $_REQUEST,
         * for example parameter "odkdy", see usage in JShopsysOrderExportFacade
         */
        $actionNamePostfix = str_replace(
            ['/', '-'],
            ['.', '_'],
            $matchedActions[1]
        );

        /**
         * Based on the action, the appropriate service is found and method call is called on this class
         * Example
         *   - request from jShopsys = objednavky/objednavky_export.php?odkdy=123456789
         *   - this request implies configuration shopsys_pohoda.jshopsys.actions_routes.objednavky.objednavky_export, see app/config/config.yml
         *   - this configuration implies service Shopsys\PohodaBundle\Model\Order\JShopsys\JShopsysOrderExportFacade
         */
        $pohodaJShopsysActionServiceName = sprintf('shopsys_pohoda.jshopsys.actions_routes.%s', $actionNamePostfix);

        if (!$this->container->has($pohodaJShopsysActionServiceName)) {
            return $response
                ->setStatusCode(Response::HTTP_NOT_FOUND)
                ->setContent('Neznámá akce: ' . $action);
        }

        /** @var \Shopsys\PohodaBundle\Component\JShopsys\JShopsysActionCallableInterface $actionService */
        $actionService = $this->container->get($pohodaJShopsysActionServiceName);

        return $actionService->call($request, $response);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function timeCheckAction(Request $request, Response $response): Response
    {
        return $response->setContent(time());
    }
}
