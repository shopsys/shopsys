import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartList } from 'components/Pages/Cart/CartList/CartList';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { CartSummary } from 'components/Pages/Cart/CartSummary';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmCartViewEvent } from 'gtm/hooks/useGtmCartViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import useTranslation from 'next-translate/useTranslation';

const CartPage: FC<ServerSidePropsType> = () => {
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const { cart, isFetching } = useCurrentCart();

    const isWithFetchedCart = cart !== undefined && !isFetching;

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.cart);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmCartViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Cart')}>
                <Webline>
                    {!isWithFetchedCart && <CartLoading />}

                    {isWithFetchedCart && !!cart?.items.length && (
                        <>
                            <OrderSteps activeStep={1} domainUrl={url} />

                            <CartList items={cart.items} />

                            <CartSummary />

                            <OrderAction
                                withGapBottom
                                buttonBack={t('Back')}
                                buttonBackLink="/"
                                buttonNext={t('Transport and payment')}
                                buttonNextLink={transportAndPaymentUrl}
                                hasDisabledLook={false}
                                withGapTop={false}
                            />
                        </>
                    )}

                    {isWithFetchedCart && !cart?.items.length && (
                        <p className="my-28 text-center text-2xl" tid={TIDs.cart_page_empty_cart_text}>
                            {t('Your cart is currently empty.')}
                        </p>
                    )}
                </Webline>
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
