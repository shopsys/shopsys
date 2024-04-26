import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import { getTransportAndPaymentValidationMessages, useLoadTransportAndPaymentFromLastOrder } from './helpers';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useTransportsQueryApi } from 'graphql/generated';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { hasValidationErrors } from 'helpers/errors/hasValidationErrors';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/useChangeTransportInCart';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';

const ErrorPopup = dynamic(
    () => import('components/Blocks/Popup/ErrorPopup').then((component) => component.ErrorPopup),
    {
        ssr: false,
    },
);

export const TransportAndPaymentContent: FC = () => {
    const router = useRouter();
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { transport, pickupPlace, payment, paymentGoPayBankSwift, cart, isCartHydrated } = useCurrentCart();
    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        url,
    );
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const [changeTransportInCart, isTransportSelectionLoading] = useChangeTransportInCart();
    const [changePaymentInCart, isPaymentSelectionLoading] = useChangePaymentInCart();
    const [{ data: transportsData, fetching: areTransportsLoading }] = useTransportsQueryApi({
        variables: { cartUuid },
        requestPolicy: 'network-only',
    });

    const [isLoadingTransportAndPaymentFromLastOrder, lastOrderPickupPlace] = useLoadTransportAndPaymentFromLastOrder(
        changeTransportInCart,
        changePaymentInCart,
    );

    const isLoading = isLoadingTransportAndPaymentFromLastOrder || areTransportsLoading;

    const validationMessages = getTransportAndPaymentValidationMessages(
        transport,
        pickupPlace,
        payment,
        paymentGoPayBankSwift,
        t,
    );

    const onSelectTransportAndPaymentHandler = () => {
        if (hasValidationErrors(validationMessages)) {
            updatePortalContent(
                <ErrorPopup
                    fields={validationMessages}
                    gtmMessageOrigin={GtmMessageOriginType.transport_and_payment_page}
                />,
            );

            return;
        }

        router.push(contactInformationUrl);
    };

    useEffect(() => {
        if (isCartHydrated && !cart?.items.length) {
            router.replace(cartUrl);
        }
    }, [cart?.items]);

    return (
        <OrderLayout
            activeStep={2}
            isTransportOrPaymentLoading={isTransportSelectionLoading || isPaymentSelectionLoading}
        >
            {!isLoading ? (
                <>
                    {!!transportsData?.transports.length && (
                        <TransportAndPaymentSelect
                            changePaymentInCart={changePaymentInCart}
                            changeTransportInCart={changeTransportInCart}
                            isTransportSelectionLoading={isTransportSelectionLoading}
                            lastOrderPickupPlace={lastOrderPickupPlace}
                            transports={transportsData.transports}
                        />
                    )}

                    <OrderAction
                        withGapBottom
                        withGapTop
                        buttonBack={t('Back')}
                        buttonBackLink={cartUrl}
                        buttonNext={t('Contact information')}
                        nextStepClickHandler={onSelectTransportAndPaymentHandler}
                        hasDisabledLook={
                            hasValidationErrors(validationMessages) ||
                            isTransportSelectionLoading ||
                            isPaymentSelectionLoading
                        }
                        isLoading={
                            (isTransportSelectionLoading || isPaymentSelectionLoading) && !!transport && !!payment
                        }
                    />
                </>
            ) : (
                <CartLoading />
            )}
        </OrderLayout>
    );
};
