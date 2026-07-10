import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { OrderLayout } from 'components/Layout/OrderLayout';
import { useTransportsQuery } from 'graphql/requests/transports/queries/TransportsQuery.generated';
import { TypeProductTypeEnum } from 'graphql/types';
import { useEffect, useEffectEvent } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getTransportAndPaymentValidationMessages } from 'utils/cart/getTransportAndPaymentValidationMessages';
import { useChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isEmailTransport } from 'utils/packetery';
import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import {
    useLoadTransportAndPaymentFromLastOrder,
    useTransportAndPaymentPageNavigation,
} from './transportAndPaymentUtils';

export const TransportAndPaymentContent: FC = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { cart, transport, pickupPlace, payment } = useCurrentCart();

    const { changeTransportInCart, isChangingTransportInCart } = useChangeTransportInCart();
    const { changePaymentInCart, isChangingPaymentInCart } = useChangePaymentInCart();
    const [{ data: transportsData, fetching: areTransportsFetching }] = useTransportsQuery({
        variables: { cartUuid },
        requestPolicy: 'network-only',
    });

    const cartItems = cart?.items ?? [];
    const electronicGiftVoucherQuantity = cartItems
        .filter((cartItem) => cartItem.product.productType === TypeProductTypeEnum.ElectronicGiftVoucher)
        .reduce((totalQuantity, cartItem) => totalQuantity + cartItem.quantity, 0);
    const hasElectronicGiftVouchers = electronicGiftVoucherQuantity > 0;
    const hasOnlyElectronicGiftVouchers =
        cartItems.length > 0 &&
        cartItems.every((cartItem) => cartItem.product.productType === TypeProductTypeEnum.ElectronicGiftVoucher);
    const isSingularElectronicGiftVoucher = electronicGiftVoucherQuantity === 1;

    const emailTransport = transportsData?.transports.find((transportItem) =>
        isEmailTransport(transportItem.transportTypeCode),
    );

    // the transport selection is replaced by the info box only when the email transport really exists,
    // otherwise the customer would be left with no transport to select and no explanation why
    const isEmailTransportPreselected = hasOnlyElectronicGiftVouchers && emailTransport !== undefined;

    const autoSelectEmailTransport = useEffectEvent(() => {
        if (emailTransport && transport?.uuid !== emailTransport.uuid) {
            changeTransportInCart(emailTransport.uuid, null);
        }
    });

    useEffect(() => {
        if (hasOnlyElectronicGiftVouchers && emailTransport) {
            autoSelectEmailTransport();
        }
    }, [hasOnlyElectronicGiftVouchers, emailTransport?.uuid, transport?.uuid]);

    const [isLoadingTransportAndPaymentFromLastOrder, lastOrderPickupPlace] = useLoadTransportAndPaymentFromLastOrder(
        changeTransportInCart,
        changePaymentInCart,
        isEmailTransportPreselected,
    );
    const validationMessages = getTransportAndPaymentValidationMessages(transport, pickupPlace, payment, t);
    const { goToPreviousStepFromTransportAndPaymentPage, goToNextStepFromTransportAndPaymentPage } =
        useTransportAndPaymentPageNavigation(validationMessages);

    const isDisabled = hasValidationErrors(validationMessages) || isChangingTransportInCart || isChangingPaymentInCart;

    return (
        <OrderLayout
            isFetchingData={isLoadingTransportAndPaymentFromLastOrder || areTransportsFetching}
            page="transport-and-payment"
        >
            <h1 className="sr-only">{t('Transport and payment')}</h1>

            <OrderContentWrapper
                activeStep={2}
                isTransportOrPaymentLoading={isChangingTransportInCart || isChangingPaymentInCart}
            >
                {!!transportsData?.transports.length && (
                    <TransportAndPaymentSelect
                        changePaymentInCart={changePaymentInCart}
                        changeTransportInCart={changeTransportInCart}
                        hasElectronicGiftVouchers={hasElectronicGiftVouchers}
                        isEmailTransportPreselected={isEmailTransportPreselected}
                        isSingularElectronicGiftVoucher={isSingularElectronicGiftVoucher}
                        isTransportSelectionLoading={isChangingTransportInCart || isChangingPaymentInCart}
                        lastOrderPickupPlace={lastOrderPickupPlace}
                        transports={transportsData.transports}
                    />
                )}

                <OrderAction
                    backStepClickHandler={goToPreviousStepFromTransportAndPaymentPage}
                    buttonBack={t('Back')}
                    buttonNext={t('Contact information')}
                    isDisabled={isDisabled}
                    nextStepClickHandler={goToNextStepFromTransportAndPaymentPage}
                    ariaLabelNextStep={t('Continue with order to {{ step }}', {
                        ns: 'accessibility',
                        step: t('Contact information'),
                    })}
                    shouldShowSpinnerOnNextStepButton={
                        (isChangingTransportInCart || isChangingPaymentInCart) && !!transport && !!payment
                    }
                />
            </OrderContentWrapper>
        </OrderLayout>
    );
};
