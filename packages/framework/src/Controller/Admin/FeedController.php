<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedFacade $feedFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly FeedFacade $feedFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @Route("/feed/generate/{feedName}/{domainId}", requirements={"domainId" = "\d+"})
     * @param string $feedName
     * @param int $domainId
     */
    public function generateAction($feedName, $domainId)
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
     * @Route("/feed/list/")
     */
    public function listAction()
    {
        $feedsData = [];

        $feedsInfo = $this->feedFacade->getFeedsInfo();
        foreach ($feedsInfo as $feedInfo) {
            foreach ($this->domain->getAll() as $domainConfig) {
                $feedTimestamp = $this->feedFacade->getFeedTimestamp($feedInfo, $domainConfig);
                $feedsData[] = [
                    'feedLabel' => $feedInfo->getLabel(),
                    'feedName' => $feedInfo->getName(),
                    'domainConfig' => $domainConfig,
                    'url' => $this->feedFacade->getFeedUrl($feedInfo, $domainConfig),
                    'created' => $feedTimestamp === null ? null : (new DateTime())->setTimestamp($feedTimestamp),
                    'actions' => null,
                    'additionalInformation' => $feedInfo->getAdditionalInformation(),
                ];
            }
        }

        $dataSource = new ArrayDataSource($feedsData, 'label');

        $grid = $this->gridFactory->create('feedsList', $dataSource);

        $grid->addColumn('label', 'feedLabel', t('Feed'));
        $grid->addColumn('created', 'created', t('Generated'));
        $grid->addColumn('url', 'url', t('Url address'));
        if ($this->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $grid->addColumn('actions', 'actions', t('Action'));
        }

        $grid->setTheme('@ShopsysFramework/Admin/Content/Feed/listGrid.html.twig');

        return $this->render('@ShopsysFramework/Admin/Content/Feed/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
