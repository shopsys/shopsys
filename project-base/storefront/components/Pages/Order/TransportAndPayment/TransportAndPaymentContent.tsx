import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { useCurrentCart } from 'connectors/cart/Cart';
import {
    LastOrderFragmentApi,
    ListedStoreFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useStoreQueryApi,
} from 'graphql/generated';
import { hasValidationErrors } from 'helpers/errors/hasValidationErrors';
import { getGtmPickupPlaceFromLastOrder, getGtmPickupPlaceFromStore } from 'gtm/helpers/mappers';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'hooks/cart/useChangeTransportInCart';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useMemo, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { GtmMessageOriginType } from 'gtm/types/enums';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

type TransportAndPaymentContentProps = {
    transports: TransportWithAvailablePaymentsAndStoresFragmentApi[] | undefined;
    lastOrder: LastOrderFragmentApi | null;
    changeTransportInCart: ChangeTransportHandler;
    changePaymentInCart: ChangePaymentHandler;
    isTransportSelectionLoading: boolean;
    isPaymentSelectionLoading: boolean;
};

type TransportAndPaymentErrorsType = {
    transport: {
        name: 'transport';
        label: string;
        errorMessage: string | undefined;
    };
    payment: {
        name: 'payment';
        label: string;
        errorMessage: string | undefined;
    };
    goPaySwift: {
        name: 'goPaySwift';
        label: string;
        errorMessage: string | undefined;
    };
};

export const TransportAndPaymentContent: FC<TransportAndPaymentContentProps> = ({
    transports,
    lastOrder,
    changePaymentInCart,
    changeTransportInCart,
    isPaymentSelectionLoading,
    isTransportSelectionLoading,
}) => {
    const router = useRouter();
    const { url } = useDomainConfig();
    const { t } = useTranslation();
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();
    const [isErrorPopupVisible, setErrorPopupVisibility] = useState(false);
    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        url,
    );

    const [{ data: pickupPlaceData }] = useStoreQueryApi({
        pause: lastOrder?.transport.transportType.code === 'packetery' || !lastOrder?.pickupPlaceIdentifier,
        variables: { uuid: lastOrder?.pickupPlaceIdentifier ?? null },
    });

    const transportAndPaymentValidationMessages = useMemo(() => {
        const errors: Partial<TransportAndPaymentErrorsType> = {};

        if (!transport) {
            errors.transport = {
                name: 'transport',
                label: t('Choose transport'),
                errorMessage: t('Please select transport'),
            };
        } else {
            if (transport.isPersonalPickup && pickupPlace?.identifier === undefined) {
                errors.transport = {
                    name: 'transport',
                    label: t('Choose transport'),
                    errorMessage: t('Please select transport with a personal pickup place'),
                };
            }
            if (!payment) {
                errors.payment = {
                    name: 'payment',
                    label: t('Choose payment'),
                    errorMessage: t('Please select payment'),
                };
            }
            if (payment?.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' && !paymentGoPayBankSwift) {
                errors.goPaySwift = {
                    name: 'goPaySwift',
                    label: t('Choose your bank'),
                    errorMessage: t('Please select your bank'),
                };
            }
        }

        return errors;
    }, [transport, payment, paymentGoPayBankSwift, pickupPlace?.identifier, t]);

    const onSelectTransportAndPaymentHandler = () => {
        if (hasValidationErrors(transportAndPaymentValidationMessages)) {
            setErrorPopupVisibility(true);

            return;
        }

        router.push(contactInformationUrl);
    };

    const lastOrderPickupPlace: ListedStoreFragmentApi | null = useMemo(() => {
        if (!lastOrder?.pickupPlaceIdentifier) {
            return null;
        }

        if (packeteryPickupPoint?.identifier === lastOrder.pickupPlaceIdentifier) {
            return packeteryPickupPoint;
        }

        if (pickupPlaceData?.store) {
            return getGtmPickupPlaceFromStore(lastOrder.pickupPlaceIdentifier, pickupPlaceData.store);
        }

        return getGtmPickupPlaceFromLastOrder(lastOrder.pickupPlaceIdentifier, lastOrder);
    }, [lastOrder, packeteryPickupPoint, pickupPlaceData?.store]);

    return (
        <>
            {transports && (
                <TransportAndPaymentSelect
                    transports={transports}
                    lastOrderPickupPlace={lastOrderPickupPlace}
                    lastOrderTransportUuid={lastOrder?.transport.uuid ?? null}
                    lastOrderPaymentUuid={lastOrder?.payment.uuid ?? null}
                    changeTransportInCart={changeTransportInCart}
                    changePaymentInCart={changePaymentInCart}
                    isTransportSelectionLoading={isTransportSelectionLoading}
                />
            )}

            <OrderAction
                buttonBack={t('Back')}
                buttonNext={t('Contact information')}
                hasDisabledLook={
                    hasValidationErrors(transportAndPaymentValidationMessages) ||
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
                    fields={transportAndPaymentValidationMessages}
                    gtmMessageOrigin={GtmMessageOriginType.transport_and_payment_page}
                />
            )}
        </>
    );
};
