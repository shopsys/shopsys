import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import Head from 'next/head';
import { getStringWithoutLeadingSlash } from 'utils/parsing/stringWIthoutSlash';

type BreadcrumbsMetadataProps = {
    breadcrumbs: TypeBreadcrumbFragment[];
};

export const BreadcrumbsMetadata: FC<BreadcrumbsMetadataProps> = ({ breadcrumbs }) => {
    const { url } = useDomainConfig();

    return (
        <Head>
            <script
                key="breadcrumbs-metadata"
                id="breadcrumbs-metadata"
                type="application/ld+json"
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
            />
        </Head>
    );
};
