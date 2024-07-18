<?php

declare(strict_types=1);

namespace Tests\App\Smoke\Http;

use App\DataFixtures\Demo\UnitDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDeletionCronModule;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Shopsys\HttpSmokeTesting\Auth\BasicHttpAuth;
use Shopsys\HttpSmokeTesting\Auth\NoAuth;
use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RouteConfig;
use Shopsys\HttpSmokeTesting\RouteConfigCustomizer;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RouteConfigCustomization
{
    protected const DEFAULT_ID_VALUE = 1;

    private ContainerInterface $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container->get('test.service_container');
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RouteConfigCustomizer $routeConfigCustomizer
     */
    public function customizeRouteConfigs(RouteConfigCustomizer $routeConfigCustomizer)
    {
        $this->filterRoutesForTesting($routeConfigCustomizer);
        $this->configureGeneralRules($routeConfigCustomizer);
        $this->configureAdminRoutes($routeConfigCustomizer);
        $this->configureFrontendRoutes($routeConfigCustomizer);
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RouteConfigCustomizer $routeConfigCustomizer
     */
    private function filterRoutesForTesting(RouteConfigCustomizer $routeConfigCustomizer)
    {
        $routeConfigCustomizer
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                if (!$info->isHttpMethodAllowed('GET')) {
                    $config->skipRoute('Only routes supporting GET method are tested.');
                }
            })
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                $adminUrl = $this->container->getParameter('admin_url');

                if (preg_match('~^(/' . $adminUrl . ')?/_~', $info->getRoutePath())) {
                    $config->skipRoute('Internal routes (prefixed with "/_") are not tested.');
                }
            })
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                if ($info->getRouteCondition() === 'request.isXmlHttpRequest()') {
                    $config->skipRoute('AJAX-only routes are not tested.');
                }
            })
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                if (!preg_match('~^(admin|front)_~', $info->getRouteName())) {
                    $config->skipRoute('Only routes for front-end and administration are tested.');
                }
            })
            ->customizeByRouteName('admin_login_check', function (RouteConfig $config) {
                $config->skipRoute(
                    'Used by firewall to catch login requests. '
                    . 'See http://symfony.com/doc/current/reference/configuration/security.html#check-path',
                );
            })
            ->customizeByRouteName('admin_domain_selectdomain', function (RouteConfig $config) {
                $config->skipRoute('Used only for internal setting of selected domain by tab control in admin.');
            })
            ->customizeByRouteName('admin_domainfilter_selectdomain', function (RouteConfig $config) {
                $config->skipRoute('Used only for internal setting of selected domain by tab control in admin.');
            })
            ->customizeByRouteName('admin_feed_generate', function (RouteConfig $config) {
                $config->skipRoute('Do not rewrite XML feed by test products.');
            })
            ->customizeByRouteName('admin_feed_schedule', function (RouteConfig $config) {
                $config->skipRoute('Do not schedule XML feed by test.');
            })
            ->customizeByRouteName('admin_logout', function (RouteConfig $config) {
                $config->skipRoute('There is different security configuration in TEST environment.');
            })
            ->customizeByRouteName('admin_unit_delete', function (RouteConfig $config) {
                $config->skipRoute('temporarily not tested until it will be optimized in US-1517.');
            })
            ->customizeByRouteName('admin_domain_list', function (RouteConfig $config) {
                if ($this->isSingleDomain()) {
                    $config->skipRoute('Domain list in administration is not available when only 1 domain exists.');
                }
            })
            ->customizeByRouteName('admin_access_denied', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet(
                    'This route serves as "access_denied_url" (see security.yaml) and always redirects to a referer (or dashboard).',
                )
                    ->setExpectedStatusCode(403);
            })
            ->customizeByRouteName('admin_flag_delete', function (RouteConfig $config) {
                $config->skipRoute('Deletion of flag from ShopSys is disabled.');
            });
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RouteConfigCustomizer $routeConfigCustomizer
     */
    private function configureGeneralRules(RouteConfigCustomizer $routeConfigCustomizer)
    {
        $routeConfigCustomizer
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                foreach ($info->getRouteParameterNames() as $name) {
                    if ($info->isRouteParameterRequired($name) && preg_match('~^(id|.+Id)$~', $name)) {
                        $debugNote = 'Route requires ID parameter "%s". Using %d by default.';
                        $config->changeDefaultRequestDataSet(sprintf($debugNote, $name, self::DEFAULT_ID_VALUE))
                            ->setParameter($name, self::DEFAULT_ID_VALUE);
                    }
                }
            })
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                if (preg_match('~(_delete$)|(^admin_mail_deletetemplate$)|(^admin_(stock|store)_setdefault$)~', $info->getRouteName())) {
                    $debugNote = 'Add CSRF token for any delete action during test execution. '
                        . '(Routes are protected by RouteCsrfProtector.)';
                    $config->changeDefaultRequestDataSet($debugNote)
                        ->addCallDuringTestExecution(
                            function (RequestDataSet $requestDataSet, ContainerInterface $container) {
                                $container = $container->get('test.service_container');
                                /** @var \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector */
                                $routeCsrfProtector = $container->get(RouteCsrfProtector::class);
                                /** @var \Symfony\Component\Security\Csrf\CsrfTokenManager $csrfTokenManager */
                                $csrfTokenManager = $container->get('security.csrf.token_manager');

                                $tokenId = $routeCsrfProtector->getCsrfTokenId($requestDataSet->getRouteName());
                                $token = $csrfTokenManager->getToken($tokenId);

                                $parameterName = RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER;
                                $requestDataSet->setParameter($parameterName, $token->getValue());
                            },
                        );
                    $config->changeDefaultRequestDataSet('Expect redirect by 302 for any delete action.')
                        ->setExpectedStatusCode(302);
                }
            });
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RouteConfigCustomizer $routeConfigCustomizer
     */
    private function configureAdminRoutes(RouteConfigCustomizer $routeConfigCustomizer)
    {
        $routeConfigCustomizer
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                if (preg_match('~^admin_~', $info->getRouteName())) {
                    $config->changeDefaultRequestDataSet('Log as "admin" to administration.')
                        ->setAuth(new BasicHttpAuth('admin', 'admin123'));
                }
            })
            ->customize(function (RouteConfig $config, RouteInfo $info) {
                if (preg_match('~^admin_(superadmin_|translation_list$)~', $info->getRouteName())) {
                    $config->changeDefaultRequestDataSet('Only superadmin should be able to see this route.')
                        ->setExpectedStatusCode(403);
                    $config->addExtraRequestDataSet('Should be OK when logged in as "superadmin".')
                        ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                        ->setExpectedStatusCode(200);
                }
            })
            ->customizeByRouteName('admin_login', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Admin login should redirect by 302.')
                    ->setExpectedStatusCode(302);
                $config->addExtraRequestDataSet(
                    'Admin login should not redirect for users that are not logged in yet.',
                )
                    ->setAuth(new NoAuth())
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_login_sso', function (RouteConfig $config, RouteInfo $info) {
                $debugNote = sprintf('Route "%s" should always just redirect.', $info->getRouteName());
                $config->changeDefaultRequestDataSet($debugNote)
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_default_schedulecron', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Standard admin is not allowed to schedule cron')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('Superadmin can schedule cron')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_default_cronenable', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Standard admin is not allowed to enable cron')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('Superadmin can enable cron')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_default_crondisable', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Standard admin is not allowed to disable cron')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('Superadmin can disable cron')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_administrator_edit', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Standard admin is not allowed to edit superadmin (with ID 1)')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('Superadmin can edit superadmin')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(200);
                $config->addExtraRequestDataSet('Editing normal administrator (with ID 2) should be OK.')
                    ->setParameter('id', 2)
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_administrator_myaccount', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('My account redirects to edit page')
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_category_edit', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('It is forbidden to edit category with ID 1 as it is the root.')
                    ->setExpectedStatusCode(404);
                $config->addExtraRequestDataSet('Editing normal category should be OK.')
                    ->setParameter('id', 2)
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_bestsellingproduct_detail', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Category with ID 1 is the root, use ID 2 instead.')
                    ->setParameter('categoryId', 2);
            })
            ->customizeByRouteName('admin_pricinggroup_delete', function (RouteConfig $config) {
                $config->skipRoute('Deleting pricing group is not necessary.');
            })
            ->customizeByRouteName('admin_product_edit', function (RouteConfig $config) {
                $config->addExtraRequestDataSet('Edit product that is a main variant (ID 82).')
                    ->setParameter('id', 82);
                $config->addExtraRequestDataSet('Edit product that is a variant (ID 75).')
                    ->setParameter('id', 75);
            })
            ->customizeByRouteName('admin_unit_delete', function (RouteConfig $config) {
                $unit = $this->getPersistentReference(UnitDataFixture::UNIT_PIECES, entityClassName: Unit::class);
                $newUnit = $this->getPersistentReference(UnitDataFixture::UNIT_CUBIC_METERS, entityClassName: Unit::class);

                $debugNote = sprintf(
                    'Delete unit "%s" and replace it by "%s".',
                    $unit->getName('en'),
                    $newUnit->getName('en'),
                );
                $config->changeDefaultRequestDataSet($debugNote)
                    ->setParameter('id', $unit->getId())
                    ->setParameter('newId', $newUnit->getId());
            })
            ->customizeByRouteName('admin_vat_delete', function (RouteConfig $config) {
                $vat = $this->getPersistentReference(VatDataFixture::VAT_SECOND_LOW, Domain::FIRST_DOMAIN_ID, Vat::class);
                $newVat = $this->getPersistentReference(VatDataFixture::VAT_LOW, Domain::FIRST_DOMAIN_ID, Vat::class);

                $debugNote = sprintf('Delete VAT "%s" and replace it by "%s".', $vat->getName(), $newVat->getName());
                $config->changeDefaultRequestDataSet($debugNote)
                    ->setParameter('id', $vat->getId())
                    ->setParameter('newId', $newVat->getId());
            })
            ->customizeByRouteName('admin_redis_clean', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Only superadmin can clean the storefront query cache.')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('You can clean the storefront query cache when logged in as superadmin.')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_redis_show', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('You are not allowed to access storefront cache clean. Log in as superadmin.')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('As superadmin, you are allowed to access storefront cache clean.')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_currency_list', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Currency setting is available only to superadmin.')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('Should be OK when logged in as "superadmin".')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_currency_delete', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Currency setting is available only to superadmin.')
                    ->setExpectedStatusCode(403);
            })
            ->customizeByRouteName('admin_currency_deleteconfirm', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Currency setting is available only to superadmin.')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('Should be OK when logged in as "superadmin".')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_default_crondetail', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Use correct ID of cron module.')
                    ->setParameter('serviceId', VatDeletionCronModule::class);
            })
            ->customizeByRouteName('admin_blogcategory_edit', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('It is forbidden to edit blog category with ID 1 as it is the root.')
                    ->setExpectedStatusCode(404);
                $config->addExtraRequestDataSet('Editing normal category should be OK.')
                    ->setParameter('id', 2)
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_stock_savesettings', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Route just for save stock setting form, route is always redirected without render own page.')
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_categoryseo_newfilters', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('It is forbidden to create category SEO combinations from category with ID 1 as it is the root category.')
                    ->setExpectedStatusCode(404);
                $config->addExtraRequestDataSet('Category SEO combinations from non-root category should be OK.')
                    ->setParameter('categoryId', 2)
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_categoryseo_newcombinations', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('It is forbidden to create category SEO combinations from category with ID 1 as it is the root category.')
                    ->setExpectedStatusCode(404);
                $config->addExtraRequestDataSet('Category SEO combinations from non-root category should be OK.')
                    ->setParameter('categoryId', 2)
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_categoryseo_readycombination', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Check route with data-fixture parameters.')
                    ->setParameter('categoryId', 8)
                    ->setParameter('choseCategorySeoMixCombinationJson', '{"domainId":1,"categoryId":8,"flagId":1,"ordering":null,"parameterValueIdsByParameterIds":{"38":75,"40":79,"37":73,"39":77}}');
            })
            ->customizeByRouteName('admin_unused_friendly_url_delete', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Unused friendly URL may be deleted only when there is any and CSRF token is provided')
                    ->addCallDuringTestExecution(function (RequestDataSet $requestDataSet, ContainerInterface $container) {
                        $container = $container->get('test.service_container');
                        /** @var \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector */
                        $routeCsrfProtector = $container->get(RouteCsrfProtector::class);
                        /** @var \Symfony\Component\Security\Csrf\CsrfTokenManager $csrfTokenManager */
                        $csrfTokenManager = $container->get('security.csrf.token_manager');

                        $tokenId = $routeCsrfProtector->getCsrfTokenId($requestDataSet->getRouteName());
                        $token = $csrfTokenManager->getToken($tokenId);

                        $parameterName = RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER;
                        $requestDataSet->setParameter($parameterName, $token->getValue());
                    })
                    ->setParameter('domainId', Domain::FIRST_DOMAIN_ID)
                    ->setParameter('slug', 'unused-friendly-url');
            })
            ->customizeByRouteName('admin_administrator_enable-two-factor-authentication', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Standard admin is not allowed to edit superadmin (with ID 1)')
                    ->setParameter('twoFactorAuthenticationType', Administrator::TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH)
                    ->setExpectedStatusCode(302);
                $config->addExtraRequestDataSet('Editing normal administrator (with ID 2) should be OK.')
                    ->setParameter('id', 2)
                    ->setParameter('twoFactorAuthenticationType', Administrator::TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH)
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName('admin_administrator_disable-two-factor-authentication', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Standard admin is not allowed to edit superadmin (with ID 1)')
                    ->setExpectedStatusCode(302);
                $config->addExtraRequestDataSet('Two factor authentication is not enabled for normal administrator (with ID 2).')
                    ->setParameter('id', 2)
                    ->setExpectedStatusCode(302);
            })
            ->customizeByRouteName('admin_cspheader_setting', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('CSP setting is available only to superadmin.')
                    ->setExpectedStatusCode(403);
                $config->addExtraRequestDataSet('Login as "superadmin" to see CSP setting page')
                    ->setAuth(new BasicHttpAuth('superadmin', 'admin123'))
                    ->setExpectedStatusCode(200);
            })
            ->customizeByRouteName(['admin_customeruser_loginascustomeruser'], function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet()->setExpectedStatusCode(403);
            })
            ->customizeByRouteName('admin_languageconstant_edit', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Constants using translation keys from StoreFront')
                    ->setParameter('key', 'Cart');
            })
            ->customizeByRouteName('admin_languageconstant_delete', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Constants using translation keys from StoreFront')
                    ->setParameter('key', 'Cart');
            })
            ->customizeByRouteName('admin_product_productnamesbycatnums', function (RouteConfig $config) {
                $config->changeDefaultRequestDataSet('Use catnums instead of ID')
                    ->setParameter('catnums', '9177759,7700768,9146508')
                    ->setExpectedStatusCode(200);
            });
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RouteConfigCustomizer $routeConfigCustomizer
     */
    private function configureFrontendRoutes(RouteConfigCustomizer $routeConfigCustomizer)
    {
        $routeConfigCustomizer->customize(function (RouteConfig $config, RouteInfo $info) {
            if (preg_match('~^front_~', $info->getRouteName())) {
                $config->skipRoute('Frontend routes are not smoke tested anymore (JS Storefront is used)');
            }
        });
    }

    /**
     * @template T
     * @param string $name
     * @param int|null $domainId
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    private function getPersistentReference($name, ?int $domainId = null, ?string $entityClassName = null)
    {
        /** @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade */
        $persistentReferenceFacade = $this->container
            ->get(PersistentReferenceFacade::class);

        if ($domainId !== null) {
            return $persistentReferenceFacade->getReferenceForDomain($name, $domainId, $entityClassName);
        }

        return $persistentReferenceFacade->getReference($name, $entityClassName);
    }

    /**
     * @return bool
     */
    private function isSingleDomain()
    {
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->container->get(Domain::class);

        return count($domain->getAll()) === 1;
    }
}
