import { CommonLayout } from 'components/Layout/CommonLayout';
import { ProductsComparison } from 'components/Pages/ProductsComparison/ProductsComparison';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';

const ProductsComparisonPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [productComparisonUrl] = getInternationalizedStaticUrls(['/products-comparison'], url);
    const breadcrumb: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Product comparison'), slug: productComparisonUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.product_comparison, breadcrumb);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Product comparison')}>
            <ProductsComparison breadcrumb={breadcrumb} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) => initServerSideProps({ context, redisClient }),
);

export default ProductsComparisonPage;
