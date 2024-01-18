import { getCookie, setCookie } from 'cookies-next';
import { GetServerSidePropsContext } from 'next/types';

const LAST_VISITED_MAX_ITEMS = 10;

export const getLastVisitedProductCatalogNumbers = (context?: GetServerSidePropsContext) => {
    const lastVisitedFromCookies = getCookie('lastVisitedProducts', { req: context?.req, res: context?.res });

    if (typeof lastVisitedFromCookies === 'string') {
        return JSON.parse(lastVisitedFromCookies) as string[];
    }

    return null;
};

export const setLastVisitedProductCatalogNumbers = (newLastVisitedProductsCatalogNumber: string): void => {
    const currentLastVisitedProductsCatalogNumbers = getLastVisitedProductCatalogNumbers() || [];

    if (!currentLastVisitedProductsCatalogNumbers.includes(newLastVisitedProductsCatalogNumber)) {
        currentLastVisitedProductsCatalogNumbers.unshift(newLastVisitedProductsCatalogNumber);
    }

    const uniqueLastVisitedProductsCatalogNumbers = Array.from(new Set(currentLastVisitedProductsCatalogNumbers));

    const lastVisitedProductCatalogNumbers = JSON.stringify(
        uniqueLastVisitedProductsCatalogNumbers.slice(0, LAST_VISITED_MAX_ITEMS),
    );

    setCookie('lastVisitedProducts', lastVisitedProductCatalogNumbers, {
        path: '/',
        maxAge: 60 * 60 * 24 * 30,
    });
};
