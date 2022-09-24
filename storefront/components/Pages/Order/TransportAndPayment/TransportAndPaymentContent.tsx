import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { useCurrentCart } from 'connectors/cart/Cart';
import { LastOrderFragmentApi, useStoreQueryApi } from 'graphql/generated';
import { hasValidationErrors } from 'helpers/errors/hasValidationErrors';
import { getGtmPickupPlaceFromLastOrder, getGtmPickupPlaceFromStore } from 'helpers/gtm/mappers';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getPacketeryCookie } from 'helpers/packetery';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'hooks/cart/useChangeTransportInCart';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useMemo, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

type TransportAndPaymentContentProps = {
    transports: TransportType[];
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
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const t = useTypedTranslationFunction();
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();
    const [isErrorPopupVisible, setErrorPopupVisibility] = useState(false);

    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        domainUrl,
    );

    const [{ data: pickupPlaceData }] = useStoreQueryApi({
        pause: lastOrder?.pickupPlaceIdentifier === undefined || lastOrder.pickupPlaceIdentifier === null,
        variables: { uuid: lastOrder?.pickupPlaceIdentifier ?? null },
    });

    const transportAndPaymentValidationMessages = useMemo(() => {
        const errors: Partial<TransportAndPaymentErrorsType> = {};

        if (transport === null) {
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
            if (payment === null) {
                errors.payment = {
                    name: 'payment',
                    label: t('Choose payment'),
                    errorMessage: t('Please select payment'),
                };
            }
            if (payment?.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' && paymentGoPayBankSwift === null) {
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

    const lastOrderPickupPlace: PickupPlaceType | null = useMemo(() => {
        if (lastOrder?.pickupPlaceIdentifier === undefined || lastOrder.pickupPlaceIdentifier === null) {
            return null;
        }

        const packeteryCookie = getPacketeryCookie();

        if (packeteryCookie?.identifier === lastOrder.pickupPlaceIdentifier) {
            return packeteryCookie;
        }

        if (pickupPlaceData?.store !== undefined && pickupPlaceData.store !== null) {
            return getGtmPickupPlaceFromStore(lastOrder.pickupPlaceIdentifier, pickupPlaceData.store);
        }

        return getGtmPickupPlaceFromLastOrder(lastOrder.pickupPlaceIdentifier, lastOrder);
    }, [lastOrder, pickupPlaceData?.store]);

    return (
        <>
            {transports.length > 0 && (
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
                isLoading={
                    (isTransportSelectionLoading || isPaymentSelectionLoading) && transport !== null && payment !== null
                }
                withGapTop={true}
                withGapBottom={true}
                buttonBackLink={cartUrl}
                nextStepClickHandler={onSelectTransportAndPaymentHandler}
            />
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={transportAndPaymentValidationMessages}
                origin="transport pay"
            />
        </>
    );
};
