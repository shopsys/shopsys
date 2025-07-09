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
    const { changeTransport, resetTransportAndPayment, openPickupPlacePopup } = useTransportChangeInSelect(
        transports,
        lastOrderPickupPlace,
        changeTransportInCart,
        changePaymentInCart,
    );

    return (
        <>
            <PacketeryContainer />

            <div data-tid={TIDs.pages_order_transport}>
                <h2 className="h4 mb-3">{t('Choose transport')}</h2>

                <ul>
                    <fieldset>
                        <legend className="sr-only">{t('Choose transport type')}</legend>
                        <AnimatePresence initial={false}>
                            {!!transport && (
                                <AnimateCollapseDiv className="relative !block" keyName="transport-selected">
                                    <TransportListItem
                                        isActive
                                        changeTransport={changeTransport}
                                        openPickupPlacePopup={() => openPickupPlacePopup(transport.uuid)}
                                        pickupPlace={pickupPlace}
                                        transport={transport}
                                    />
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>

                        <AnimatePresence initial={false}>
                            {!transport && (
                                <AnimateCollapseDiv className="relative !block" keyName="transport-list">
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
                    </fieldset>
                </ul>

                <AnimatePresence initial={false}>
                    {!!transport && (
                        <AnimateCollapseDiv className="relative !flex flex-col" keyName="transport-reset">
                            <ResetButton
                                text={t('Change transport type')}
                                tid={TIDs.reset_transport_button}
                                onClick={resetTransportAndPayment}
                            />
                        </AnimateCollapseDiv>
                    )}
                </AnimatePresence>
            </div>

            <AnimatePresence initial={false}>
                {transport !== null && (
                    <AnimateCollapseDiv
                        className="relative mt-12 !flex flex-col"
                        keyName="payments-list"
                        tid={TIDs.pages_order_payment}
                    >
                        {isTransportSelectionLoading && (
                            <LoaderWithOverlay className="w-8" overlayClassName="rounded-xl" />
                        )}

                        <h2 className="h4 mb-3">{t('Choose payment')}</h2>

                        <fieldset>
                            <legend className="sr-only">{t('Choose payment type')}</legend>
                            <AnimatePresence initial={false}>
                                {!!payment && (
                                    <AnimateCollapseDiv className="relative !block" keyName="payment-selected">
                                        <PaymentListItem isActive changePayment={changePayment} payment={payment} />
                                    </AnimateCollapseDiv>
                                )}
                            </AnimatePresence>

                            <AnimatePresence initial={false}>
                                {!payment && (
                                    <AnimateCollapseDiv className="relative !block" keyName="payment-list">
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
                        </fieldset>

                        <AnimatePresence initial={false}>
                            {payment !== null && (
                                <AnimateCollapseDiv className="relative !flex flex-col" keyName="payment-reset">
                                    <ResetButton
                                        text={t('Change payment type')}
                                        tid={TIDs.reset_payment_button}
                                        onClick={resetPaymentAndGoPayBankSwift}
                                    />
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </>
    );
};

type ResetButtonProps = { text: string; onClick: () => void };

const ResetButton: FC<ResetButtonProps> = ({ text, onClick, tid }) => (
    <button
        className="bg-background-more hover:bg-background-most flex w-full cursor-pointer items-center rounded-xl px-5 py-3 text-sm"
        data-tid={tid}
        tabIndex={0}
        onClick={onClick}
    >
        <ArrowIcon className="mr-2 size-4" />
        {text}
    </button>
);
