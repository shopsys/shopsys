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
     * KitchenController constructor.
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
}
