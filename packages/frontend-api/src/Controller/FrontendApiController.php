<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Controller;

use Overblog\GraphQLBundle\Controller\GraphController;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker;
use Shopsys\FrontendApiBundle\Model\GraphqlConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendApiController
{
    use SetterInjectionTrait;

    /**
     * @var \Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker
     */
    protected $enabledOnDomainChecker;

    /**
     * @var \Overblog\GraphQLBundle\Controller\GraphController
     */
    protected $graphController;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\GraphqlConfigurator
     */
    protected $graphqlConfigurator;

    /**
     * @param \Overblog\GraphQLBundle\Controller\GraphController $graphController
     * @param \Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker $enabledOnDomainChecker
     * @param \Shopsys\FrontendApiBundle\Model\GraphqlConfigurator|null $graphqlConfigurator
     */
    public function __construct(
        GraphController $graphController,
        EnabledOnDomainChecker $enabledOnDomainChecker,
        ?GraphqlConfigurator $graphqlConfigurator = null
    ) {
        $this->enabledOnDomainChecker = $enabledOnDomainChecker;
        $this->graphController = $graphController;
        $this->graphqlConfigurator = $graphqlConfigurator;
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
        return new JsonResponse(['errors' => [['message' => 'Frontend API is not enabled on current domain']]], 404);
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\GraphqlConfigurator $graphqlConfigurator
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setGraphqlConfigurator(GraphqlConfigurator $graphqlConfigurator): void
    {
        $this->setDependency($graphqlConfigurator, 'graphqlConfigurator');
    }
}
