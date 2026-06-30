<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Model\Elasticsearch\ElasticsearchEntityDataFacade;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ElasticsearchEntityDataController extends AdminBaseController
{
    public function __construct(
        protected readonly ElasticsearchEntityDataFacade $elasticsearchEntityDataFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(
        path: '/elasticsearch/entity-data/{indexName}/{id}/{domainId}/{hideDomainSwitch}',
        name: 'admin_elasticsearch_entity_data',
        requirements: [
            'indexName' => '[\w\-]+',
            'id' => '\d+',
            'domainId' => '\d+',
            'hideDomainSwitch' => '0|1',
        ],
        defaults: ['domainId' => null],
    )]
    #[SuperAdminOnly]
    public function detailAction(string $indexName, int $id, ?int $domainId, bool $hideDomainSwitch = false): Response
    {
        $selectedDomainId = $this->resolveSelectedDomainId($domainId);
        $elasticsearchEntityData = $this->elasticsearchEntityDataFacade->getElasticsearchEntityData(
            $indexName,
            $selectedDomainId,
            $id,
        );

        return $this->render('@ShopsysAdministration/content/elasticsearch/entityDataModal.html.twig', [
            'domains' => $this->domain->getAdminEnabledDomains(),
            'selectedDomainId' => $selectedDomainId,
            'hideDomainSwitch' => $hideDomainSwitch,
            'entityId' => $id,
            'indexName' => $indexName,
            'elasticsearchEntityData' => $elasticsearchEntityData,
        ]);
    }

    protected function resolveSelectedDomainId(?int $requestedDomainId): int
    {
        $adminEnabledDomainIds = $this->domain->getAdminEnabledDomainIds();

        if ($requestedDomainId !== null && in_array($requestedDomainId, $adminEnabledDomainIds, true)) {
            return $requestedDomainId;
        }

        return array_first($adminEnabledDomainIds);
    }
}
