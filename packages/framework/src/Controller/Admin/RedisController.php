<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class RedisController extends AdminBaseController
{
    public function __construct(
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    #[Route(path: '/superadmin/redis/clean-storefront-query-cache')]
    public function cleanAction(Request $request): Response
    {
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache();

        $this->addSuccessFlashTwig(
            t('Storefront queries cache has been cleaned.'),
        );

        return $this->redirectToRoute('admin_redis_show');
    }

    #[Route(path: '/superadmin/redis/show-clean-storefront-query-cache')]
    public function showAction(Request $request): Response
    {
        return $this->render('@ShopsysAdministration/content/storefrontCache/clean.html.twig');
    }
}
