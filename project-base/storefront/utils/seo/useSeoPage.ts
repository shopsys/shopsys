import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSeoPageQuery } from 'graphql/requests/seoPage/queries/SeoPageQuery.generated';
import { useRouter } from 'next/router';
import { extractSeoPageSlugFromUrl } from 'utils/seo/extractSeoPageSlugFromUrl';

export const useSeoPage = () => {
    const { url } = useDomainConfig();
    const router = useRouter();

    const pageSlug = extractSeoPageSlugFromUrl(router.asPath, url);

    const [{ data: seoPageData }] = useSeoPageQuery({
        variables: {
            pageSlug: pageSlug!,
        },
        pause: !pageSlug,
    });

    return seoPageData?.seoPage ?? null;
};
