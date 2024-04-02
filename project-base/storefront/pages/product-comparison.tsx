import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { ProductComparison } from 'components/Pages/ProductComparison/ProductComparison';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const ProductComparisonPage: NextPage<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [productComparisonUrl] = getInternationalizedStaticUrls(['/product-comparison'], url);
    const breadcrumbs: BreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Product comparison'), slug: productComparisonUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.product_comparison, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('Product comparison')}>
            <ProductComparison />

            <LastVisitedProducts />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default ProductComparisonPage;
