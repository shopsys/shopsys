<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class SelfCanonicalController extends FrontBaseController
{
    private const SELF_CANONICAL_PAGES = [
        'front_homepage',
        'front_product_list',
        'front_product_detail',
        'front_article_detail',
        'front_brand_detail',
        'front_blogarticle_detail',
        'front_blogcategory_detail',
        'front_productseries_detail',
        'front_productseriescategory_detail',
        'front_productseries_list',
        'front_category_seo',
    ];

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSelfCanonicalLink(Request $request): Response
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        $isCategorySeoMix = $masterRequest->attributes->get('isCategorySeoMix');
        $routeName = $masterRequest->attributes->get('_route');
        $isSelfCanonicalNeeded = $this->isSelfCanonicalNeeded($request, $isCategorySeoMix);
        if (in_array($routeName, self::SELF_CANONICAL_PAGES, true) === true && $isSelfCanonicalNeeded) {
            $selfCanonicalLink = $this->generateUrl($routeName, ['id' => $masterRequest->attributes->get('id')], 0);
        } else {
            $selfCanonicalLink = null;
        }

        return $this->render('Front/Inline/SelfCanonical/selfCanonicalLink.html.twig', [
            'selfCanonicalLink' => $selfCanonicalLink,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param bool|null $isCategorySeoMix
     * @return bool
     */
    private function isSelfCanonicalNeeded(Request $request, $isCategorySeoMix): bool
    {
        $requestParameters = $request->query->all();
        $requestParametersKeys = array_keys($requestParameters);

        if (count($requestParametersKeys) > 1 && $isCategorySeoMix === true && isset($requestParameters['page']) === false) {
            return true;
        }

        if (empty($requestParametersKeys) === false) {
            if (in_array('product_filter_form', $requestParametersKeys, true) === false
                && isset($requestParameters['page']) === false
            ) {
                return true;
            }
        }

        return false;
    }
}
