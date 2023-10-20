import { PickupPlacePopup } from './PickupPlacePopup';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { PacketeryContainer } from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import {
    getPickupPlaceDetail,
    usePaymentChangeInSelect,
    useTransportChangeInSelect,
} from 'components/Pages/Order/TransportAndPayment/helpers';
import { useCurrentCart } from 'connectors/cart/Cart';
import {
    ListedStoreFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useGoPaySwiftsQueryApi,
} from 'graphql/generated';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'hooks/cart/useChangeTransportInCart';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';

type TransportAndPaymentSelectProps = {
    transports: TransportWithAvailablePaymentsAndStoresFragmentApi[];
    lastOrderPickupPlace: ListedStoreFragmentApi | null;
    changeTransportInCart: ChangeTransportHandler;
    changePaymentInCart: ChangePaymentHandler;
    isTransportSelectionLoading: boolean;
};

const TEST_IDENTIFIER = 'pages-order-';

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
    const [getGoPaySwiftsResult] = useGoPaySwiftsQueryApi({ variables: { currencyCode } });
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
        transportItem: TransportWithAvailablePaymentsAndStoresFragmentApi,
        isActive: boolean,
    ) => (
        <TransportAndPaymentListItem
            key={transportItem.uuid}
            dataTestId={TEST_IDENTIFIER + 'transport-item' + (isActive ? '-active' : '')}
            isActive={isActive}
        >
            <Radiobutton
                checked={isActive}
                dataTestId={TEST_IDENTIFIER + 'transport-item-input'}
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
                onChangeCallback={changeTransport}
            />
        </TransportAndPaymentListItem>
    );

    const renderPaymentListItem = (paymentItem: SimplePaymentFragmentApi, isActive: boolean) => (
        <TransportAndPaymentListItem
            key={paymentItem.uuid}
            dataTestId={TEST_IDENTIFIER + 'payment-item' + (isActive ? '-active' : '')}
            isActive={isActive}
        >
            <Radiobutton
                checked={isActive}
                dataTestId={TEST_IDENTIFIER + 'payment-item-input'}
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
                onChangeCallback={changePayment}
            />
        </TransportAndPaymentListItem>
    );

    return (
        <>
            <PacketeryContainer />
            <div data-testid={TEST_IDENTIFIER + 'transport-and-payment'}>
                <div data-testid={TEST_IDENTIFIER + 'transport'}>
                    <div className="h3 mb-3">{t('Choose transport')}</div>
                    <ul>
                        {transport
                            ? renderTransportListItem(transport, true)
                            : transports.map((transportItem) => renderTransportListItem(transportItem, false))}
                    </ul>
                    {!!transport && (
                        <ResetButton
                            dataTestId={TEST_IDENTIFIER + 'reset-transport'}
                            text={t('Change transport type')}
                            onClick={resetTransportAndPayment}
                        />
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
                    <div className="relative mt-12" data-testid={TEST_IDENTIFIER + 'payment'}>
                        {isTransportSelectionLoading && <LoaderWithOverlay className="w-8" />}

                        <div className="h3 mb-3">{t('Choose payment')}</div>

                        <ul>
                            {payment
                                ? renderPaymentListItem(payment, true)
                                : transport.payments.map((paymentItem) => renderPaymentListItem(paymentItem, false))}
                        </ul>

                        {payment?.type === 'goPay' && payment.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' && (
                            <>
                                <div className="h3 mb-3">{t('Choose your bank')}</div>
                                {getGoPaySwiftsResult.data?.GoPaySwifts.map((goPaySwift) => (
                                    <Radiobutton
                                        key={goPaySwift.swift}
                                        checked={paymentGoPayBankSwift === goPaySwift.swift}
                                        id={goPaySwift.swift}
                                        label={goPaySwift.name}
                                        name="goPaySwift"
                                        value={goPaySwift.swift}
                                        onChangeCallback={changeGoPaySwift}
                                    />
                                ))}
                            </>
                        )}
                        {payment !== null && (
                            <ResetButton
                                dataTestId={TEST_IDENTIFIER + 'reset-payment'}
                                text={t('Change payment type')}
                                onClick={resetPaymentAndGoPayBankSwift}
                            />
                        )}
                    </div>
                )}
            </div>
        </>
    );
};

type ResetButtonProps = { text: string; onClick: () => void };

const ResetButton: FC<ResetButtonProps> = ({ text, dataTestId, onClick }) => (
    <button
        className="flex w-full items-center bg-whitesmoke px-2 py-1 text-sm"
        data-testid={dataTestId}
        onClick={onClick}
    >
        {text}
        <ArrowIcon className="ml-2" />
    </button>
);
