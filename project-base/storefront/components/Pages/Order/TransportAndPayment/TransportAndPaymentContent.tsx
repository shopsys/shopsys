import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import { getTransportAndPaymentValidationMessages, useLoadTransportAndPaymentFromLastOrder } from './utils';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { SkeletonOrderContent } from 'components/Blocks/Skeleton/SkeletonOrderContent';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useTransportsQuery } from 'graphql/requests/transports/queries/TransportsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useOrderPagesAccess } from 'utils/cart/useOrderPagesAccess';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

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
    const [{ data: transportsData, fetching: areTransportsLoading }] = useTransportsQuery({
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

    const canContentBeDisplayed = useOrderPagesAccess('transport-and-payment');

    return (
        <OrderLayout>
            {!isLoading && canContentBeDisplayed ? (
                <OrderContentWrapper
                    activeStep={2}
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
                </OrderContentWrapper>
            ) : (
                <SkeletonOrderContent />
            )}
        </OrderLayout>
    );
};
