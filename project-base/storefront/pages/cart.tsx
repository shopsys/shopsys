import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { CartContent } from 'components/Pages/Cart/CartContent';
import { EmptyCart } from 'components/Pages/Cart/EmptyCart';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmCartViewEvent } from 'gtm/utils/pageViewEvents/useGtmCartViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const CartPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.cart);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmCartViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout isFetchingData={isCartFetchingOrUnavailable} pageTypeOverride="cart" title={t('Cart')}>
                {cart?.items.length ? <CartContent cart={cart} /> : <EmptyCart />}
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default CartPage;
