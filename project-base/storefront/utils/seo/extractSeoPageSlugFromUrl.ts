import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

export const extractSeoPageSlugFromUrl = (url: string, domain: string): string | null => {
    const staticRewritePathsForDomain = STATIC_REWRITE_PATHS[domain];

    const slugOrI18nPagePath = getUrlWithoutGetParameters(url);

    if (slugOrI18nPagePath === '/' || slugOrI18nPagePath === '') {
        return '/';
    }

    if (slugOrI18nPagePath in staticRewritePathsForDomain) {
        return staticRewritePathsForDomain[slugOrI18nPagePath as StaticRewritePathKeyType];
    }

    const path = Object.values(staticRewritePathsForDomain).find((path) => path === slugOrI18nPagePath);

    return path ?? null;
};
