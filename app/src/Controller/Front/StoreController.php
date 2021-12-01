<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends FrontBaseController
{
    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(StoreFacade $storeFacade, Domain $domain)
    {
        $this->storeFacade = $storeFacade;
        $this->domain = $domain;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Store/store.html.twig', [
            'stores' => $this->storeFacade->getStoresEnabledOnDomainIndexedByStoreId($this->domain->getId()),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $store = $this->storeFacade->getById($id);

        return $this->render('Front/Content/Store/detail.html.twig', [
            'store' => $store,
        ]);
    }
}
