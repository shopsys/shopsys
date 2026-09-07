import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import Head from 'next/head';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringWithoutLeadingSlash } from 'utils/parsing/stringWIthoutSlash';
import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';

type BreadcrumbsMetadataProps = {
    breadcrumbs: TypeBreadcrumbFragment[];
};

export const BreadcrumbsMetadata: FC<BreadcrumbsMetadataProps> = ({ breadcrumbs }) => {
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const items = [{ name: t('Home page'), slug: '/' }, ...breadcrumbs];

    return (
        <Head>
            <script
                key="breadcrumbs-metadata"
                id="breadcrumbs-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: serializeJsonForScriptTag({
                        '@context': 'https://schema.org',
                        '@type': 'BreadcrumbList',
                        itemListElement: items.map((breadcrumb, index) => {
                            const breadcrumbAbsoluteUrl = url + getStringWithoutLeadingSlash(breadcrumb.slug);

                            return {
                                '@type': 'ListItem',
                                position: index + 1,
                                name: breadcrumb.name,
                                item: index === items.length - 1 ? undefined : breadcrumbAbsoluteUrl,
                            };
                        }),
                    }),
                }}
            />
        </Head>
    );
};
