import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import {
    getTransportAndPaymentValidationMessages,
    useHandleTransportAndPaymentLoadingAndRedirect,
    useLoadTransportAndPaymentFromLastOrder,
} from './helpers';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
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
import { useState } from 'react';
import Skeleton from 'react-loading-skeleton';
import { usePersistStore } from 'store/usePersistStore';

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
        <OrderLayout
            activeStep={2}
            contentSkeleton={isTransportAndPaymentSkeletonVisible ? <Skeleton className="h-64 w-full" /> : null}
            isTransportOrPaymentLoading={isTransportSelectionLoading || isPaymentSelectionLoading}
        >
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
                isLoading={(isTransportSelectionLoading || isPaymentSelectionLoading) && !!transport && !!payment}
                nextStepClickHandler={onSelectTransportAndPaymentHandler}
                hasDisabledLook={
                    hasValidationErrors(validationMessages) || isTransportSelectionLoading || isPaymentSelectionLoading
                }
            />

            {isErrorPopupVisible && (
                <ErrorPopup
                    fields={validationMessages}
                    gtmMessageOrigin={GtmMessageOriginType.transport_and_payment_page}
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                />
            )}
        </OrderLayout>
    );
};
