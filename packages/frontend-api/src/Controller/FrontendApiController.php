<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Overblog\GraphQLBundle\Controller\GraphController;
use Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade;
use Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker;
use Shopsys\FrontendApiBundle\Model\GraphqlConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendApiController
{
    public function __construct(
        protected readonly GraphController $graphController,
        protected readonly EnabledOnDomainChecker $enabledOnDomainChecker,
        protected readonly GraphqlConfigurator $graphqlConfigurator,
        protected readonly DetectionFacade $detectionFacade,
    ) {
    }

    public function endpointAction(Request $request, ?string $schemaName = null): Response
    {
        $this->detectionFacade->setFrontendApiSourceAndUserIdentifier();

        if (!$this->enabledOnDomainChecker->isEnabledOnCurrentDomain()) {
            return $this->createApiNotEnabledResponse();
        }

        $this->graphqlConfigurator->applyExtraConfiguration();

        return $this->graphController->endpointAction($request, $schemaName);
    }

    public function batchEndpointAction(Request $request, ?string $schemaName = null): Response
    {
        $this->detectionFacade->setFrontendApiSourceAndUserIdentifier();

        if (!$this->enabledOnDomainChecker->isEnabledOnCurrentDomain()) {
            return $this->createApiNotEnabledResponse();
        }

        $this->graphqlConfigurator->applyExtraConfiguration();

        return $this->graphController->batchEndpointAction($request, $schemaName);
    }

    protected function createApiNotEnabledResponse(): Response
    {
        return new JsonResponse([
            'errors' => [['message' => 'Frontend API is not enabled on current domain']],
        ], 404);
    }
}
