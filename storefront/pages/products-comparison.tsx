import { CommonLayout } from 'components/Layout/CommonLayout';
import { ProductsComparison } from 'components/Pages/ProductsComparison/ProductsComparison';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const ProductsComparisonPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('compare');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Product comparison')}>
            <ProductsComparison />
        </CommonLayout>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);

export default ProductsComparisonPage;
