import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartList } from 'components/Pages/Cart/CartList/CartList';
import { CartSummary } from 'components/Pages/Cart/CartSummary';
import { EmptyCartWrapper } from 'components/Pages/Cart/EmptyCartWrapper';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useGtmCartViewEvent } from 'hooks/gtm/useGtmCartViewEvent';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';

const CartPage: FC<ServerSidePropsType> = () => {
    const { url } = useDomainConfig();
    const t = useTypedTranslationFunction();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const currentCart = useCurrentCart();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.cart);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmCartViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <EmptyCartWrapper currentCart={currentCart} title={t('Cart')} isCartPage>
                <CommonLayout title={t('Cart')}>
                    <OrderSteps activeStep={1} domainUrl={url} />
                    <CartList items={currentCart.cart?.items} />
                    <CartSummary />
                    <Webline>
                        <OrderAction
                            buttonBack={t('Back')}
                            buttonNext={t('Transport and payment')}
                            hasDisabledLook={false}
                            withGapTop={false}
                            withGapBottom
                            buttonBackLink="/"
                            buttonNextLink={transportAndPaymentUrl}
                        />
                    </Webline>
                </CommonLayout>
            </EmptyCartWrapper>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default CartPage;
