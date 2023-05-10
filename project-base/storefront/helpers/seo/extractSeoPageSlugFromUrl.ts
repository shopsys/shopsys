import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import getConfig from 'next/config';
import { StaticRewriteDomainPathsType } from 'types/staticPaths';

const isStaticTemplatePageSlug = (
    slug: string,
    staticRewritePaths: StaticRewriteDomainPathsType,
): slug is keyof StaticRewriteDomainPathsType => {
    return slug in staticRewritePaths;
};

export const extractSeoPageSlugFromUrl = (url: string, domain: string): string | null => {
    const { publicRuntimeConfig } = getConfig();
    const staticRewritePaths = publicRuntimeConfig.staticRewritePaths[domain] as StaticRewriteDomainPathsType;

    const slugOrI18nPagePath = getUrlWithoutGetParameters(url);

    if (slugOrI18nPagePath === '/' || slugOrI18nPagePath === '') {
        return '/';
    }

    if (isStaticTemplatePageSlug(slugOrI18nPagePath, staticRewritePaths)) {
        return staticRewritePaths[slugOrI18nPagePath];
    }

    const path = Object.values(staticRewritePaths).find((path) => path === slugOrI18nPagePath);

    return path ?? null;
};
