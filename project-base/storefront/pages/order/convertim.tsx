import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { EmptyCart } from 'components/Pages/Cart/EmptyCart';
import { ConvertimContent } from 'components/Pages/Convertim/ConvertimContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmCartViewEvent } from 'gtm/utils/pageViewEvents/useGtmCartViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const ConvertimPage: FC<ServerSidePropsType> = () => {
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmCartViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <OrderLayout isFetchingData={isCartFetchingOrUnavailable} page="transport-and-payment">
                <OrderContentWrapper activeStep={2} isTransportOrPaymentLoading={isCartFetchingOrUnavailable}>
                    {cart?.items.length ? <ConvertimContent cart={cart} /> : <EmptyCart />}
                </OrderContentWrapper>
            </OrderLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default ConvertimPage;
