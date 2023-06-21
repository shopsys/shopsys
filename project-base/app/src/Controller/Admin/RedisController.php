<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedisController extends AdminBaseController
{
    /**
     * @param \App\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        private CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @Route("/superadmin/redis/clean-storefront-query-cache")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cleanAction(Request $request): Response
    {
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache();

        $this->addSuccessFlashTwig(
            t('Storefront queries cache has been cleaned.'),
        );

        return $this->redirectToRoute('admin_redis_show');
    }

    /**
     * @Route("/superadmin/redis/show-clean-storefront-query-cache")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request): Response
    {
        return $this->render('Admin/Content/StorefrontCache/clean.html.twig');
    }
}
