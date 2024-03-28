import { PickupPlacePopup } from './PickupPlacePopup';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { PacketeryContainer } from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import {
    getPickupPlaceDetail,
    usePaymentChangeInSelect,
    useTransportChangeInSelect,
} from 'components/Pages/Order/TransportAndPayment/utils';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { useGoPaySwiftsQuery } from 'graphql/requests/payments/queries/GoPaySwiftsQuery.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { ChangePaymentHandler } from 'utils/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';

type TransportAndPaymentSelectProps = {
    transports: TypeTransportWithAvailablePaymentsAndStoresFragment[];
    lastOrderPickupPlace: TypeListedStoreFragment | null;
    changeTransportInCart: ChangeTransportHandler;
    changePaymentInCart: ChangePaymentHandler;
    isTransportSelectionLoading: boolean;
};

export const TransportAndPaymentSelect: FC<TransportAndPaymentSelectProps> = ({
    transports,
    lastOrderPickupPlace,
    changeTransportInCart,
    changePaymentInCart,
    isTransportSelectionLoading,
}) => {
    const { t } = useTranslation();
    const { currencyCode } = useDomainConfig();
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();
    const [getGoPaySwiftsResult] = useGoPaySwiftsQuery({ variables: { currencyCode } });
    const { changePayment, changeGoPaySwift, resetPaymentAndGoPayBankSwift } =
        usePaymentChangeInSelect(changePaymentInCart);
    const {
        preSelectedTransport,
        changeTransport,
        changePickupPlace,
        closePickupPlacePopup,
        resetTransportAndPayment,
    } = useTransportChangeInSelect(transports, lastOrderPickupPlace, changeTransportInCart, changePaymentInCart);

    const renderTransportListItem = (
        transportItem: TypeTransportWithAvailablePaymentsAndStoresFragment,
        isActive: boolean,
    ) => (
        <TransportAndPaymentListItem key={transportItem.uuid} isActive={isActive}>
            <Radiobutton
                checked={isActive}
                id={transportItem.uuid}
                name="transport"
                value={transportItem.uuid}
                label={
                    <TransportAndPaymentSelectItemLabel
                        daysUntilDelivery={transportItem.daysUntilDelivery}
                        description={transportItem.description}
                        image={transportItem.mainImage}
                        name={transportItem.name}
                        pickupPlaceDetail={getPickupPlaceDetail(transport, pickupPlace, transportItem)}
                        price={transportItem.price}
                    />
                }
                onClick={changeTransport}
            />
        </TransportAndPaymentListItem>
    );

    const renderPaymentListItem = (paymentItem: TypeSimplePaymentFragment, isActive: boolean) => {
        const isGoPaySwiftPayment =
            paymentItem.uuid === payment?.uuid &&
            payment.type === 'goPay' &&
            payment.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT';

        return (
            <TransportAndPaymentListItem key={paymentItem.uuid} isActive={isActive}>
                <Radiobutton
                    checked={isActive}
                    id={paymentItem.uuid}
                    name="payment"
                    value={paymentItem.uuid}
                    label={
                        <TransportAndPaymentSelectItemLabel
                            description={paymentItem.description}
                            image={paymentItem.mainImage}
                            name={paymentItem.name}
                            price={paymentItem.price}
                        />
                    }
                    onClick={changePayment}
                />
                {isGoPaySwiftPayment && goPaySwiftSelect}
            </TransportAndPaymentListItem>
        );
    };

    const goPaySwiftSelect = (
        <div className="relative w-full">
            <b>{t('Choose your bank')}</b>
            {getGoPaySwiftsResult.data?.GoPaySwifts.map((goPaySwift) => (
                <Radiobutton
                    key={goPaySwift.swift}
                    checked={paymentGoPayBankSwift === goPaySwift.swift}
                    id={goPaySwift.swift}
                    label={goPaySwift.name}
                    name="goPaySwift"
                    value={goPaySwift.swift}
                    onChange={(event) => changeGoPaySwift(event.target.value)}
                />
            ))}
        </div>
    );

    return (
        <>
            <PacketeryContainer />
            <div>
                <div tid={TIDs.pages_order_transport}>
                    <div className="h3 mb-3">{t('Choose transport')}</div>
                    <ul>
                        {transport
                            ? renderTransportListItem(transport, true)
                            : transports.map((transportItem) => renderTransportListItem(transportItem, false))}
                    </ul>
                    {!!transport && (
                        <ResetButton text={t('Change transport type')} onClick={resetTransportAndPayment} />
                    )}
                    {!!preSelectedTransport && (
                        <PickupPlacePopup
                            transport={preSelectedTransport}
                            onChangePickupPlaceCallback={changePickupPlace}
                            onClosePickupPlacePopupCallback={closePickupPlacePopup}
                        />
                    )}
                </div>
                {transport !== null && preSelectedTransport === null && (
                    <div className="relative mt-12" tid={TIDs.pages_order_payment}>
                        {isTransportSelectionLoading && <LoaderWithOverlay className="w-8" />}

                        <div className="h3 mb-3">{t('Choose payment')}</div>

                        <ul>
                            {payment
                                ? renderPaymentListItem(payment, true)
                                : transport.payments.map((paymentItem) => renderPaymentListItem(paymentItem, false))}
                        </ul>
                        {payment !== null && (
                            <ResetButton text={t('Change payment type')} onClick={resetPaymentAndGoPayBankSwift} />
                        )}
                    </div>
                )}
            </div>
        </>
    );
};

type ResetButtonProps = { text: string; onClick: () => void };

const ResetButton: FC<ResetButtonProps> = ({ text, onClick }) => (
    <button className="flex w-full items-center bg-whitesmoke px-2 py-1 text-sm" onClick={onClick}>
        {text}
        <ArrowIcon className="ml-2" />
    </button>
);
