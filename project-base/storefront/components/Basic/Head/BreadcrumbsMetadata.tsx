import { BreadcrumbFragmentApi } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { getStringWithoutLeadingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Head from 'next/head';

type BreadcrumbsMetadataProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const BreadcrumbsMetadata: FC<BreadcrumbsMetadataProps> = ({ breadcrumbs }) => {
    const { url } = useDomainConfig();

    return (
        <Head>
            <script
                type="application/ld+json"
                id="breadcrumbs-metadata"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify({
                        '@context': 'https://schema.org',
                        '@type': 'BreadcrumbList',
                        itemListElement: breadcrumbs.map((breadcrumb, index) => {
                            const breadcrumbAbsoluteUrl = url + getStringWithoutLeadingSlash(breadcrumb.slug);

                            return {
                                '@type': 'ListItem',
                                position: index + 1,
                                name: breadcrumb.name,
                                item: index === breadcrumbs.length - 1 ? undefined : breadcrumbAbsoluteUrl,
                            };
                        }),
                    }),
                }}
                key="breadcrumbs-metadata"
            />
        </Head>
    );
};
