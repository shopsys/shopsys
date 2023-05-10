import { BreadcrumbFragmentApi } from 'graphql/generated';
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
                    __html: JSON.stringify([
                        {
                            '@context': 'https://schema.org',
                            '@type': 'BreadcrumbList',
                            itemListElement: breadcrumbs.map((breadcrumb, index) => {
                                const breadcrumbSlugWithoutLeadingSlash =
                                    breadcrumb.slug.charAt(0) === '/' ? breadcrumb.slug.slice(1) : breadcrumb.slug;
                                const breadcrumbAbsoluteUrl = url + breadcrumbSlugWithoutLeadingSlash;

                                return {
                                    '@type': 'ListItem',
                                    position: index + 1,
                                    name: breadcrumb.name,
                                    item: index === breadcrumbs.length - 1 ? undefined : breadcrumbAbsoluteUrl,
                                };
                            }),
                        },
                    ]),
                }}
                key="breadcrumbs-metadata"
            />
        </Head>
    );
};
