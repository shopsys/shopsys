import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { CartContent } from 'components/Pages/Cart/CartContent';
import { CartStickyBar } from 'components/Pages/Cart/CartStickyBar';
import { EmptyCart } from 'components/Pages/Cart/EmptyCart';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmCartViewEvent } from 'gtm/utils/pageReadyEvents/useGtmCartViewEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useRef } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const CartPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const cartPreviewRef = useRef<HTMLDivElement>(null);

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.cart);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);
    useGtmCartViewEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <CommonLayout
                isFetchingData={isCartFetchingOrUnavailable}
                pageTypeOverride="cart"
                title={t('Shopping cart')}
                bottomContent={cart?.items.length ? <CartStickyBar originalButtonRef={cartPreviewRef} /> : undefined}
            >
                {cart?.items.length ? <CartContent cart={cart} cartPreviewRef={cartPreviewRef} /> : <EmptyCart />}
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                redisClient,
                domainConfig,
                t,
                currentCustomerUserPrefetchMode: 'full',
                authenticationConfig: {
                    authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                },
            }),
);

export default CartPage;
