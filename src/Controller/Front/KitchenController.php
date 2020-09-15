<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class KitchenController extends FrontBaseController
{
    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Kitchen/index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('Front/Content/Kitchen/list.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listModernKitchenAction(): Response
    {
        return $this->render('Front/Content/Kitchen/variants/modernKitchen.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listRusticalKitchenAction(): Response
    {
        return $this->render('Front/Content/Kitchen/variants/rusticalKitchen.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listClassicKitchenAction(): Response
    {
        return $this->render('Front/Content/Kitchen/variants/classicKitchen.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(): Response
    {
        return $this->render('Front/Content/Kitchen/detail.html.twig');
    }
}
