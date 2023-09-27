import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useTransportsQueryApi } from 'graphql/generated';
import { hasValidationErrors } from 'helpers/errors/hasValidationErrors';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/useChangeTransportInCart';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import {
    getTransportAndPaymentValidationMessages,
    useHandleTransportAndPaymentLoadingAndRedirect,
    useLoadTransportAndPaymentFromLastOrder,
} from './helpers';
import Skeleton from 'react-loading-skeleton';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

export const TransportAndPaymentContent: FC = () => {
    const router = useRouter();
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();
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

    const validationMessages = getTransportAndPaymentValidationMessages(
        transport,
        pickupPlace,
        payment,
        paymentGoPayBankSwift,
        t,
    );

    const isTransportAndPaymentSkeletonVisible = useHandleTransportAndPaymentLoadingAndRedirect(
        areTransportsLoading,
        isLoadingTransportAndPaymentFromLastOrder,
    );

    const onSelectTransportAndPaymentHandler = () => {
        if (hasValidationErrors(validationMessages)) {
            setErrorPopupVisibility(true);

            return;
        }

        router.push(contactInformationUrl);
    };

    return (
        <>
            <OrderLayout
                activeStep={2}
                isTransportOrPaymentLoading={isTransportSelectionLoading || isPaymentSelectionLoading}
                contentSkeleton={isTransportAndPaymentSkeletonVisible ? <Skeleton className="h-64 w-full" /> : null}
            >
                {!!transportsData?.transports.length && (
                    <TransportAndPaymentSelect
                        transports={transportsData.transports}
                        lastOrderPickupPlace={lastOrderPickupPlace}
                        changeTransportInCart={changeTransportInCart}
                        changePaymentInCart={changePaymentInCart}
                        isTransportSelectionLoading={isTransportSelectionLoading}
                    />
                )}

                <OrderAction
                    buttonBack={t('Back')}
                    buttonNext={t('Contact information')}
                    hasDisabledLook={
                        hasValidationErrors(validationMessages) ||
                        isTransportSelectionLoading ||
                        isPaymentSelectionLoading
                    }
                    isLoading={(isTransportSelectionLoading || isPaymentSelectionLoading) && !!transport && !!payment}
                    withGapTop
                    withGapBottom
                    buttonBackLink={cartUrl}
                    nextStepClickHandler={onSelectTransportAndPaymentHandler}
                />

                {isErrorPopupVisible && (
                    <ErrorPopup
                        onCloseCallback={() => setErrorPopupVisibility(false)}
                        fields={validationMessages}
                        gtmMessageOrigin={GtmMessageOriginType.transport_and_payment_page}
                    />
                )}
            </OrderLayout>
        </>
    );
};
