import { PickupPlacePopup } from './PickupPlacePopup';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Heading } from 'components/Basic/Heading/Heading';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { PacketeryContainer } from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import { useCurrentCart } from 'connectors/cart/Cart';
import {
    ListedStoreFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useGoPaySwiftsQueryApi,
} from 'graphql/generated';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'hooks/cart/useChangeTransportInCart';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { getPickupPlaceDetail, usePaymentChangeInSelect, useTransportChangeInSelect } from '../helpers';

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
            isActive={isActive}
            dataTestId={TEST_IDENTIFIER + 'transport-item' + (isActive ? '-active' : '')}
        >
            <Radiobutton
                name="transport"
                id={transportItem.uuid}
                value={transportItem.uuid}
                checked={isActive}
                dataTestId={TEST_IDENTIFIER + 'transport-item-input'}
                onChangeCallback={changeTransport}
                label={
                    <TransportAndPaymentSelectItemLabel
                        name={transportItem.name}
                        daysUntilDelivery={transportItem.daysUntilDelivery}
                        price={transportItem.price}
                        description={transportItem.description}
                        image={transportItem.mainImage}
                        pickupPlaceDetail={getPickupPlaceDetail(transport, pickupPlace, transportItem)}
                    />
                }
            />
        </TransportAndPaymentListItem>
    );

    const renderPaymentListItem = (paymentItem: SimplePaymentFragmentApi, isActive: boolean) => (
        <TransportAndPaymentListItem
            key={paymentItem.uuid}
            isActive={isActive}
            dataTestId={TEST_IDENTIFIER + 'payment-item' + (isActive ? '-active' : '')}
        >
            <Radiobutton
                name="payment"
                id={paymentItem.uuid}
                value={paymentItem.uuid}
                checked={isActive}
                dataTestId={TEST_IDENTIFIER + 'payment-item-input'}
                onChangeCallback={changePayment}
                label={
                    <TransportAndPaymentSelectItemLabel
                        name={paymentItem.name}
                        price={paymentItem.price}
                        description={paymentItem.description}
                        image={paymentItem.mainImage}
                    />
                }
            />
        </TransportAndPaymentListItem>
    );

    return (
        <>
            <PacketeryContainer />
            <div data-testid={TEST_IDENTIFIER + 'transport-and-payment'}>
                <div data-testid={TEST_IDENTIFIER + 'transport'}>
                    <Heading type="h3">{t('Choose transport')}</Heading>
                    <ul>
                        {transport
                            ? renderTransportListItem(transport, true)
                            : transports.map((transportItem) => renderTransportListItem(transportItem, false))}
                    </ul>
                    {!!transport && (
                        <ResetButton
                            onClick={resetTransportAndPayment}
                            dataTestId={TEST_IDENTIFIER + 'reset-transport'}
                            text={t('Change transport type')}
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

                        <Heading type="h3">{t('Choose payment')}</Heading>

                        <ul>
                            {payment
                                ? renderPaymentListItem(payment, true)
                                : transport.payments.map((paymentItem) => renderPaymentListItem(paymentItem, false))}
                        </ul>

                        {payment?.type === 'goPay' && payment.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' && (
                            <>
                                <Heading type="h3">{t('Choose your bank')}</Heading>
                                {getGoPaySwiftsResult.data?.GoPaySwifts.map((goPaySwift) => (
                                    <Radiobutton
                                        key={goPaySwift.swift}
                                        name="goPaySwift"
                                        id={goPaySwift.swift}
                                        value={goPaySwift.swift}
                                        onChangeCallback={changeGoPaySwift}
                                        checked={paymentGoPayBankSwift === goPaySwift.swift}
                                        label={goPaySwift.name}
                                    />
                                ))}
                            </>
                        )}
                        {payment !== null && (
                            <ResetButton
                                onClick={resetPaymentAndGoPayBankSwift}
                                dataTestId={TEST_IDENTIFIER + 'reset-payment'}
                                text={t('Change payment type')}
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
        onClick={onClick}
        data-testid={dataTestId}
        className="flex w-full items-center bg-whitesmoke px-2 py-1 text-sm"
    >
        {text}
        <ArrowIcon className="ml-2" />
    </button>
);
