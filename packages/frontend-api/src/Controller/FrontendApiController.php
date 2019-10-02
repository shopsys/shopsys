<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Overblog\GraphQLBundle\Controller\GraphController;
use Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendApiController
{
    /**
     * @var \Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker
     */
    protected $enabledOnDomainChecker;

    /**
     * @var \Overblog\GraphQLBundle\Controller\GraphController
     */
    protected $graphController;

    /**
     * @param \Overblog\GraphQLBundle\Controller\GraphController $graphController
     * @param \Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker $enabledOnDomainChecker
     */
    public function __construct(
        GraphController $graphController,
        EnabledOnDomainChecker $enabledOnDomainChecker
    ) {
        $this->enabledOnDomainChecker = $enabledOnDomainChecker;
        $this->graphController = $graphController;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $schemaName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function endpointAction(Request $request, ?string $schemaName = null): Response
    {
        if (!$this->enabledOnDomainChecker->isEnabledOnCurrentDomain()) {
            return $this->createApiNotEnabledResponse();
        }

        return $this->graphController->endpointAction($request, $schemaName);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $schemaName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function batchEndpointAction(Request $request, ?string $schemaName = null): Response
    {
        if (!$this->enabledOnDomainChecker->isEnabledOnCurrentDomain()) {
            return $this->createApiNotEnabledResponse();
        }

        return $this->graphController->batchEndpointAction($request, $schemaName);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createApiNotEnabledResponse(): Response
    {
        return new JsonResponse(['errors' => [['message' => 'Frontend API is not enabled on current domain']]], 404);
    }
}
