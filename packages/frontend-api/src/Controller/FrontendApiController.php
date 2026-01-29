<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Overblog\GraphQLBundle\Controller\GraphController;
use Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade;
use Shopsys\FrontendApiBundle\Model\GraphqlConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendApiController
{
    /**
     * @param \Overblog\GraphQLBundle\Controller\GraphController $graphController
     * @param \Shopsys\FrontendApiBundle\Model\GraphqlConfigurator $graphqlConfigurator
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade $detectionFacade
     */
    public function __construct(
        protected readonly GraphController $graphController,
        protected readonly GraphqlConfigurator $graphqlConfigurator,
        protected readonly DetectionFacade $detectionFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $schemaName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function endpointAction(Request $request, ?string $schemaName = null): Response
    {
        $this->detectionFacade->setFrontendApiSourceAndUserIdentifier();

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
        $this->detectionFacade->setFrontendApiSourceAndUserIdentifier();

        $this->graphqlConfigurator->applyExtraConfiguration();

        return $this->graphController->batchEndpointAction($request, $schemaName);
    }
}
