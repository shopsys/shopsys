<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Overblog\GraphQLBundle\Controller\GraphController;
use Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker;
use Shopsys\FrontendApiBundle\Model\GraphqlConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendApiController
{
    /**
     * @param \Overblog\GraphQLBundle\Controller\GraphController $graphController
     * @param \Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker $enabledOnDomainChecker
     * @param \Shopsys\FrontendApiBundle\Model\GraphqlConfigurator $graphqlConfigurator
     */
    public function __construct(
        protected readonly GraphController $graphController,
        protected readonly EnabledOnDomainChecker $enabledOnDomainChecker,
        protected readonly GraphqlConfigurator $graphqlConfigurator,
    ) {
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

        $this->graphqlConfigurator->applyExtraConfiguration();

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

        $this->graphqlConfigurator->applyExtraConfiguration();

        return $this->graphController->batchEndpointAction($request, $schemaName);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createApiNotEnabledResponse(): Response
    {
        return new JsonResponse([
            'errors' => [['message' => 'Frontend API is not enabled on current domain']],
        ], 404);
    }
}
