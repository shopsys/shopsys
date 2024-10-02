import { PaymentListItem } from './PaymentSelectListItem';
import { TransportListItem } from './TransportSelectListItem';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { PacketeryContainer } from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import {
    usePaymentChangeInSelect,
    useTransportChangeInSelect,
} from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';
import { ChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { ChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type TransportAndPaymentSelectProps = {
    transports: TypeTransportWithAvailablePaymentsFragment[];
    lastOrderPickupPlace: StoreOrPacketeryPoint | null;
    changeTransportInCart: ChangeTransportInCart;
    changePaymentInCart: ChangePaymentInCart;
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
    const { transport, pickupPlace, payment } = useCurrentCart();
    const { changePayment, resetPaymentAndGoPayBankSwift } = usePaymentChangeInSelect(changePaymentInCart);
    const { changeTransport, resetTransportAndPayment } = useTransportChangeInSelect(
        transports,
        lastOrderPickupPlace,
        changeTransportInCart,
        changePaymentInCart,
    );

    return (
        <>
            <PacketeryContainer />
            <div>
                <div tid={TIDs.pages_order_transport}>
                    <div className="h4 mb-3">{t('Choose transport')}</div>
                    <ul>
                        <AnimatePresence initial={false}>
                            {transport ? (
                                <AnimateCollapseDiv className="relative !block" keyName="transport-selected">
                                    <TransportListItem
                                        isActive
                                        changeTransport={changeTransport}
                                        pickupPlace={pickupPlace}
                                        transport={transport}
                                    />
                                </AnimateCollapseDiv>
                            ) : (
                                <AnimateCollapseDiv className="relative !block" keyName="transports-list">
                                    {transports.map((transportItem) => (
                                        <TransportListItem
                                            key={transportItem.uuid}
                                            changeTransport={changeTransport}
                                            pickupPlace={pickupPlace}
                                            transport={transportItem}
                                        />
                                    ))}
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>
                    </ul>
                    {!!transport && (
                        <ResetButton
                            text={t('Change transport type')}
                            tid={TIDs.reset_transport_button}
                            onClick={resetTransportAndPayment}
                        />
                    )}
                </div>
                <AnimatePresence initial={false}>
                    {transport !== null && (
                        <AnimateCollapseDiv
                            className="relative mt-12 !flex flex-col"
                            keyName="payments-list"
                            tid={TIDs.pages_order_payment}
                            variants={collapseExpandAnimation}
                        >
                            {isTransportSelectionLoading && <LoaderWithOverlay className="w-8" />}
                            <div className="h4 mb-3">{t('Choose payment')}</div>
                            <ul>
                                <AnimatePresence initial={false}>
                                    {payment ? (
                                        <AnimateCollapseDiv className="relative !block" keyName="payment-selected">
                                            <PaymentListItem isActive changePayment={changePayment} payment={payment} />
                                        </AnimateCollapseDiv>
                                    ) : (
                                        <AnimateCollapseDiv
                                            className="relative !block"
                                            keyName="transport-payments-list"
                                        >
                                            {transport.payments.map((paymentItem) => (
                                                <PaymentListItem
                                                    key={paymentItem.uuid}
                                                    changePayment={changePayment}
                                                    payment={paymentItem}
                                                />
                                            ))}
                                        </AnimateCollapseDiv>
                                    )}
                                </AnimatePresence>
                            </ul>
                            {payment !== null && (
                                <ResetButton
                                    text={t('Change payment type')}
                                    tid={TIDs.reset_payment_button}
                                    onClick={resetPaymentAndGoPayBankSwift}
                                />
                            )}
                        </AnimateCollapseDiv>
                    )}
                </AnimatePresence>
            </div>
        </>
    );
};

type ResetButtonProps = { text: string; onClick: () => void };

const ResetButton: FC<ResetButtonProps> = ({ text, onClick, tid }) => (
    <button className="flex w-full items-center bg-backgroundMost px-2 py-1 text-sm" tid={tid} onClick={onClick}>
        {text}
        <ArrowIcon className="ml-2" />
    </button>
);
