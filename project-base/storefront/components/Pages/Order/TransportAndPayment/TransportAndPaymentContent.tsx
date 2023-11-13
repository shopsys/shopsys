import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import { getTransportAndPaymentValidationMessages, useLoadTransportAndPaymentFromLastOrder } from './helpers';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTransportsQueryApi } from 'graphql/generated';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { hasValidationErrors } from 'helpers/errors/hasValidationErrors';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/useChangeTransportInCart';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

export const TransportAndPaymentContent: FC = () => {
    const router = useRouter();
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { transport, pickupPlace, payment, paymentGoPayBankSwift, cart } = useCurrentCart();
    const [isErrorPopupVisible, setErrorPopupVisibility] = useState(false);
    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        url,
    );

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
            setErrorPopupVisibility(true);

            return;
        }

        router.push(contactInformationUrl);
    };

    useEffect(() => {
        if (!cart?.items.length) {
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

                    {isErrorPopupVisible && (
                        <ErrorPopup
                            fields={validationMessages}
                            gtmMessageOrigin={GtmMessageOriginType.transport_and_payment_page}
                            onCloseCallback={() => setErrorPopupVisibility(false)}
                        />
                    )}
                </>
            ) : (
                <CartLoading />
            )}
        </OrderLayout>
    );
};
