import getConfig from 'next/config';
import { NextRouter, useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';

const {
    publicRuntimeConfig: { shouldUseDefer },
} = getConfig();

type DeferPage = 'product' | 'category' | 'homepage';

type DeferPlace = (
    | typeof PRODUCT_PAGE_DEFER_ORDER
    | typeof CATEGORY_PAGE_DEFER_ORDER
    | typeof HOMEPAGE_DEFER_ORDER
)[number];

const getDeferPage = (router: NextRouter): DeferPage | 'non_deferred' => {
    if (router.pathname === FriendlyPagesDestinations.product) {
        return 'product';
    } else if (
        router.pathname === FriendlyPagesDestinations.category ||
        router.pathname === FriendlyPagesDestinations.seo_category
    ) {
        return 'category';
    } else if (router.pathname === '/') {
        return 'homepage';
    }

    return 'non_deferred';
};

export const useDeferredRender = (place: DeferPlace) => {
    const hadClientSideNavigation = useSessionStore((s) => s.hadClientSideNavigation);
    const router = useRouter();
    const page = getDeferPage(router);
    const isDeferredPage = page !== 'non_deferred';
    const [shouldRender, setShouldRender] = useState(!shouldUseDefer || !isDeferredPage || hadClientSideNavigation);

    useEffect(() => {
        let timer: NodeJS.Timeout | undefined;

        if (!shouldRender) {
            const defer = getDeferByPageAndPlace(page as DeferPage, place);
            timer = setTimeout(() => {
                setShouldRender(true);
            }, defer);
        }

        return () => {
            clearTimeout(timer);
        };
    }, []);

    return shouldRender;
};

const PRODUCT_PAGE_DEFER_ORDER = [
    'loaders',
    'footer',
    'last_visited',
    'add_to_cart',
    'related_products_tab',
    'autocomplete_search',
    'cart_in_header',
    'menu_iconic',
    'navigation',
    'mobile_menu',
    'comparison_and_wishlist_button',
    'recommended_products',
    'accessories',
    'newsletter',
    'user_consent',
    'gtm_head_script',
] as const;

const CATEGORY_PAGE_DEFER_ORDER = [
    'loaders',
    'footer',
    'product_list',
    'filter_panel',
    'sorting_bar',
    'recommended_products',
    'last_visited',
    'autocomplete_search',
    'cart_in_header',
    'menu_iconic',
    'navigation',
    'mobile_menu',
    'newsletter',
    'user_consent',
    'gtm_head_script',
] as const;

const HOMEPAGE_DEFER_ORDER = [
    'loaders',
    'blog_preview',
    'footer',
    'recommended_products',
    'promoted_products',
    'last_visited',
    'autocomplete_search',
    'cart_in_header',
    'menu_iconic',
    'navigation',
    'mobile_menu',
    'newsletter',
    'user_consent',
    'gtm_head_script',
] as const;

const deferConfigByPages = {
    product: PRODUCT_PAGE_DEFER_ORDER,
    category: CATEGORY_PAGE_DEFER_ORDER,
    homepage: HOMEPAGE_DEFER_ORDER,
};

const getDeferByPageAndPlace = (page: DeferPage, place: DeferPlace) => {
    const deferWaveIndex = deferConfigByPages[page].indexOf(place as any);

    return deferWaveIndex === -1 ? 0 : DEFER_START + deferWaveIndex * DEFER_GAP;
};

const DEFER_START = 150;
const DEFER_GAP = 75;
