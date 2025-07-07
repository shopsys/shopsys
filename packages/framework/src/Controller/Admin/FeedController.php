<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository;
use Shopsys\FrameworkBundle\Model\Feed\FeedRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole('ROLE_FEED')]
class FeedController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedFacade $feedFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedRegistry $feedRegistry
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository $feedModuleRepository
     */
    public function __construct(
        protected readonly FeedFacade $feedFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly Domain $domain,
        protected readonly FeedRegistry $feedRegistry,
        protected readonly FeedModuleRepository $feedModuleRepository,
    ) {
    }

    /**
     * @param string $feedName
     * @param int $domainId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/feed/generate/{feedName}/{domainId}', requirements: ['domainId' => '\d+'])]
    #[SuperAdminOnly]
    public function generateAction(string $feedName, int $domainId): Response
    {
        $domainConfig = $this->domain->getDomainConfigById((int)$domainId);

        try {
            $this->feedFacade->generateFeed($feedName, $domainConfig);

            $this->addSuccessFlashTwig(
                t('Feed "{{ feedName }}" successfully generated.'),
                [
                    'feedName' => $feedName,
                ],
            );
        } catch (FeedNotFoundException $ex) {
            $this->addErrorFlashTwig(
                t('Feed "{{ feedName }}" not found.'),
                [
                    'feedName' => $feedName,
                ],
            );
        }

        return $this->redirectToRoute('admin_feed_list');
    }

    /**
     * @param string $feedName
     * @param int $domainId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/feed/schedule/{feedName}/{domainId}', requirements: ['domainId' => '\d+'])]
    #[CanEdit]
    public function scheduleAction(string $feedName, int $domainId): RedirectResponse
    {
        try {
            $this->feedFacade->scheduleFeedByNameAndDomainId($feedName, $domainId);

            $this->addSuccessFlashTwig(
                t('Feed "{{ feedName }}" on domain ID {{ domainId }} successfully scheduled.'),
                [
                    'feedName' => $feedName,
                    'domainId' => $domainId,
                ],
            );
        } catch (FeedNotFoundException $ex) {
            $this->addErrorFlashTwig(
                t('Feed "{{ feedName }}" on domain ID {{ domainId }} not found.'),
                [
                    'feedName' => $feedName,
                    'domainId' => $domainId,
                ],
            );
        }

        return $this->redirectToRoute('admin_feed_list');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/feed/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $feedsData = [];
        $feedConfigs = $this->feedRegistry->getAllFeedConfigs();

        $allowedDomainIds = $this->domain->getAdminEnabledDomainIds();

        foreach ($feedConfigs as $feedConfig) {
            foreach ($feedConfig->getDomainIds() as $domainId) {
                if (!in_array($domainId, $allowedDomainIds, true)) {
                    continue;
                }

                $domainConfig = $this->domain->getDomainConfigById($domainId);
                $feedInfo = $feedConfig->getFeed()->getInfo();
                $feedModulesIndexedByDomainId = $this->feedModuleRepository->getFeedModulesByConfigIndexedByDomainId($feedConfig);

                $feedTimestamp = $this->feedFacade->getFeedTimestamp($feedInfo, $domainConfig);
                $feedsData[] = [
                    'feedLabel' => $feedInfo->getLabel(),
                    'feedName' => $feedInfo->getName(),
                    'domainConfig' => $domainConfig,
                    'url' => $this->feedFacade->getFeedUrl($feedInfo, $domainConfig),
                    'created' => $feedTimestamp === null ? null : (new DateTime())->setTimestamp($feedTimestamp),
                    'generate' => null,
                    'schedule' => $feedModulesIndexedByDomainId[$domainId]->isScheduled(),
                    'additionalInformation' => $feedInfo->getAdditionalInformation(),
                ];
            }
        }

        $dataSource = new ArrayDataSource($feedsData, 'label');

        $grid = $this->gridFactory->create('feedsList', $dataSource);

        $grid->addColumn('label', 'feedLabel', t('Feed'));
        $grid->addColumn('created', 'created', t('Generated'));
        $grid->addColumn('url', 'url', t('Url address'));

        if ($this->isGranted(SystemRole::SUPER_ADMIN)) {
            $grid->addColumn('generate', 'generate', t('Generate'));
        }

        $grid->addColumn('schedule', 'schedule', t('Schedule'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Feed/listGrid.html.twig');

        return $this->render('@ShopsysFramework/Admin/Content/Feed/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
