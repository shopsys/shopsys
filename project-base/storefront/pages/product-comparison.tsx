import { CommonLayout } from 'components/Layout/CommonLayout';
import { ProductComparison } from 'components/Pages/ProductComparison/ProductComparison';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';

const ProductComparisonPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [productComparisonUrl] = getInternationalizedStaticUrls(['/product-comparison'], url);
    const breadcrumb: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Product comparison'), slug: productComparisonUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.product_comparison, breadcrumb);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Product comparison')}>
            <ProductComparison breadcrumb={breadcrumb} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default ProductComparisonPage;
