import { NextRouter, useRouter } from 'next/router';
import { startTransition, useEffect, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';
import { getPublicConfigProperty } from 'utils/config/getNextConfig';

const shouldUseDefer = getPublicConfigProperty('shouldUseDefer', false);

type DeferPage = 'product' | 'category' | 'homepage';

type DeferPlace =
    | (typeof HOMEPAGE_DEFER_WAVES)[number][1][number]
    | (typeof CATEGORY_PAGE_DEFER_WAVES)[number][1][number]
    | (typeof PRODUCT_PAGE_DEFER_WAVES)[number][1][number];

const getDeferPage = (router: NextRouter): DeferPage | 'non_deferred' => {
    if (router.pathname === FriendlyPagesDestinations.product) {
        return 'product';
    } else if (
        router.pathname === FriendlyPagesDestinations.category ||
        router.pathname === FriendlyPagesDestinations.seo_category ||
        router.pathname === FriendlyPagesDestinations.brand ||
        router.pathname === FriendlyPagesDestinations.flag
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
            if (page === 'non_deferred') {
                setShouldRender(true);
            } else {
                const defer = getDeferByPageAndPlace(page, place);

                timer = setTimeout(() => {
                    startTransition(() => {
                        setShouldRender(true);
                    });
                }, defer);
            }
        }

        return () => {
            clearTimeout(timer);
        };
    }, [page, place, shouldRender]);

    return shouldRender;
};

const HOMEPAGE_DEFER_WAVES = [
    [150, ['loaders']],
    [300, ['secondary_loaders', 'blog_preview', 'footer', 'recommended_products', 'promoted_products', 'last_visited']],
    [500, ['tertiary_loaders', 'autocomplete_search', 'cart_in_header', 'menu_iconic']],
    [700, ['navigation', 'mobile_menu']],
    [900, ['newsletter', 'user_consent', 'gtm_head_script']],
] as const;

const CATEGORY_PAGE_DEFER_WAVES = [
    [150, ['loaders']],
    [300, ['secondary_loaders', 'footer', 'product_list', 'filter_panel', 'sorting_bar', 'selected_filters']],
    [500, ['tertiary_loaders', 'recommended_products', 'last_visited']],
    [700, ['autocomplete_search', 'cart_in_header', 'menu_iconic', 'navigation', 'mobile_menu']],
    [900, ['newsletter', 'user_consent', 'gtm_head_script']],
] as const;

const PRODUCT_PAGE_DEFER_WAVES = [
    [150, ['loaders']],
    [300, ['secondary_loaders', 'footer', 'add_to_cart', 'countdown', 'comparison_and_wishlist_button']],
    [500, ['tertiary_loaders', 'related_products_tab', 'recommended_products', 'accessories', 'last_visited']],
    [700, ['autocomplete_search', 'cart_in_header', 'menu_iconic']],
    [850, ['navigation', 'mobile_menu']],
    [1000, ['newsletter', 'user_consent', 'gtm_head_script']],
] as const;

type DeferWave = readonly [number, readonly string[]];

const deferConfigByPages: Record<DeferPage, readonly DeferWave[]> = {
    product: PRODUCT_PAGE_DEFER_WAVES,
    category: CATEGORY_PAGE_DEFER_WAVES,
    homepage: HOMEPAGE_DEFER_WAVES,
};

const getDeferByPageAndPlace = (page: DeferPage, place: DeferPlace): number => {
    for (const [timing, places] of deferConfigByPages[page]) {
        if (places.includes(place)) {
            return timing;
        }
    }

    return 0;
};
