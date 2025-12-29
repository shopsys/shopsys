import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import {
    getTransportAndPaymentValidationMessages,
    useLoadTransportAndPaymentFromLastOrder,
    useTransportAndPaymentPageNavigation,
} from './transportAndPaymentUtils';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { OrderLayout } from 'components/Layout/OrderLayout';
import { useTransportsQuery } from 'graphql/requests/transports/queries/TransportsQuery.generated';
import { usePersistStore } from 'store/usePersistStore';
import { useChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const TransportAndPaymentContent: FC = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { transport, pickupPlace, payment } = useCurrentCart();

    const { changeTransportInCart, isChangingTransportInCart } = useChangeTransportInCart();
    const { changePaymentInCart, isChangingPaymentInOrder } = useChangePaymentInCart();
    const [{ data: transportsData, fetching: areTransportsFetching }] = useTransportsQuery({
        variables: { cartUuid },
        requestPolicy: 'network-only',
    });

    const [isLoadingTransportAndPaymentFromLastOrder, lastOrderPickupPlace] = useLoadTransportAndPaymentFromLastOrder(
        changeTransportInCart,
        changePaymentInCart,
    );
    const validationMessages = getTransportAndPaymentValidationMessages(transport, pickupPlace, payment, t);
    const { goToPreviousStepFromTransportAndPaymentPage, goToNextStepFromTransportAndPaymentPage } =
        useTransportAndPaymentPageNavigation(validationMessages);

    const isDisabled = hasValidationErrors(validationMessages) || isChangingTransportInCart || isChangingPaymentInOrder;

    return (
        <OrderLayout
            isFetchingData={isLoadingTransportAndPaymentFromLastOrder || areTransportsFetching}
            page="transport-and-payment"
        >
            <h1 className="sr-only">{t('Transport and payment')}</h1>

            <OrderContentWrapper
                activeStep={2}
                isTransportOrPaymentLoading={isChangingTransportInCart || isChangingPaymentInOrder}
            >
                {!!transportsData?.transports.length && (
                    <TransportAndPaymentSelect
                        changePaymentInCart={changePaymentInCart}
                        changeTransportInCart={changeTransportInCart}
                        isTransportSelectionLoading={isChangingTransportInCart}
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
                        (isChangingTransportInCart || isChangingPaymentInOrder) && !!transport && !!payment
                    }
                />
            </OrderContentWrapper>
        </OrderLayout>
    );
};
