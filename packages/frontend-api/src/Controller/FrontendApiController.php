<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Overblog\GraphQLBundle\Controller\GraphController;
use Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade;
use Shopsys\FrontendApiBundle\Component\HttpFoundation\GraphqlBatchRequestValidator;
use Shopsys\FrontendApiBundle\Model\GraphqlConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendApiController
{
    public function __construct(
        protected readonly GraphController $graphController,
        protected readonly GraphqlConfigurator $graphqlConfigurator,
        protected readonly DetectionFacade $detectionFacade,
        protected readonly GraphqlBatchRequestValidator $graphqlBatchRequestValidator,
    ) {
    }

    public function endpointAction(Request $request, ?string $schemaName = null): Response
    {
        $this->detectionFacade->setFrontendApiSourceAndUserIdentifier();

        $this->graphqlConfigurator->applyExtraConfiguration();

        return $this->graphController->endpointAction($request, $schemaName);
    }

    public function batchEndpointAction(Request $request, ?string $schemaName = null): Response
    {
        $limitViolationResponse = $this->graphqlBatchRequestValidator->getLimitViolationResponse($request);

        if ($limitViolationResponse !== null) {
            return $limitViolationResponse;
        }

        $this->detectionFacade->setFrontendApiSourceAndUserIdentifier();

        $this->graphqlConfigurator->applyExtraConfiguration();

        return $this->graphController->batchEndpointAction($request, $schemaName);
    }
}
