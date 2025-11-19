import { getInternationalizedStaticUrl } from './getInternationalizedStaticUrls';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.ssr';
import { DynamicBreadcrumbsSettings, StaticBreadcrumb, StaticBreadcrumbsSettings } from 'types/breadcrumbs';
import { Translate, TranslationKeys } from 'types/translation';

export const getBreadcrumbs = async (
    staticRoutes: StaticBreadcrumbsSettings,
    dynamicBreadcrumbsSettings: DynamicBreadcrumbsSettings,
    pathname: string,
    t: Translate,
) => {
    const staticBreadcrumbsSetting = getStaticBreadcrumbsSetting(staticRoutes, t);

    if (pathname in staticBreadcrumbsSetting) {
        return staticBreadcrumbsSetting[pathname];
    }

    const dynamicRoute = Object.keys(dynamicBreadcrumbsSettings).find((dynamicRoute) =>
        pathname.includes(dynamicRoute),
    );

    if (dynamicRoute) {
        const dynamicBreadcrumbsFunction = dynamicBreadcrumbsSettings[dynamicRoute];

        return await dynamicBreadcrumbsFunction(pathname, t);
    }

    return null;
};

export const mapToBreadcrumbFragments = (staticBreadcrumbs: StaticBreadcrumb[], t: Translate) =>
    staticBreadcrumbs.map(
        (breadcrumb) =>
            ({
                __typename: 'Link',
                name: t(breadcrumb.name as TranslationKeys),
                slug: breadcrumb.slug ? getInternationalizedStaticUrl(breadcrumb.slug) : '',
            }) as TypeBreadcrumbFragment,
    );

const getStaticBreadcrumbsSetting = (
    staticRoutes: StaticBreadcrumbsSettings,
    t: Translate,
): { [key: string]: TypeBreadcrumbFragment[] } =>
    Object.fromEntries(
        Object.entries(staticRoutes).map(([key, settings]) => [key, mapToBreadcrumbFragments(settings, t)]),
    );
