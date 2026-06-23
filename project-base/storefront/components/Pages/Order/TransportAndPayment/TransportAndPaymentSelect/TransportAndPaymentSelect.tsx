import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { PacketeryContainer } from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import {
    getShouldDisplayTransportGroups,
    getTransportGroupChoices,
    getTransportsWithoutGroup,
    usePaymentChangeInSelect,
    useTransportChangeInSelect,
} from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { KeyboardEvent, MouseEvent, useEffect, useMemo, useState } from 'react';
import { ChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { ChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { PaymentListItem } from './PaymentSelectListItem';
import { TransportGroupListItem } from './TransportGroupListItem';
import { TransportListItem } from './TransportSelectListItem';

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
    const [selectedTransportGroupUuid, setSelectedTransportGroupUuid] = useState<string | null>(null);
    const [shouldFocusPaymentAfterTransportChange, setShouldFocusPaymentAfterTransportChange] = useState(false);
    const { changePayment, resetPaymentAndGoPayBankSwift } = usePaymentChangeInSelect(changePaymentInCart);
    const { changeTransport, resetTransportAndPayment, openPickupPlacePopup } = useTransportChangeInSelect(
        transports,
        lastOrderPickupPlace,
        changeTransportInCart,
        changePaymentInCart,
    );
    const transportGroupChoices = useMemo(() => getTransportGroupChoices(transports), [transports]);
    const shouldDisplayTransportGroups = getShouldDisplayTransportGroups(transportGroupChoices);
    const transportsWithoutGroup = useMemo(() => getTransportsWithoutGroup(transports), [transports]);
    const transportsToDisplay = shouldDisplayTransportGroups ? transportsWithoutGroup : transports;
    const shouldShowSelectedTransport = !!transport;
    const shouldShowTransportList = !transport;
    const shouldShowSelectedPayment = !!payment;
    const shouldShowPaymentList = !payment;

    const isKeyboardSelection = (event: KeyboardEvent<HTMLInputElement> | MouseEvent<HTMLInputElement>) =>
        event.detail === 0;

    const changeTransportByInputMethod = (
        updatedTransportUuid: string | null,
        event: KeyboardEvent<HTMLInputElement> | MouseEvent<HTMLInputElement>,
    ) => {
        setShouldFocusPaymentAfterTransportChange(isKeyboardSelection(event) && updatedTransportUuid !== null);
        changeTransport(updatedTransportUuid);
    };

    const changePaymentByInputMethod = (
        updatedPaymentUuid: string | null,
        _event: KeyboardEvent<HTMLInputElement> | MouseEvent<HTMLInputElement>,
    ) => {
        changePayment(updatedPaymentUuid);
    };

    useEffect(() => {
        if (!shouldFocusPaymentAfterTransportChange || !transport || isTransportSelectionLoading) {
            return;
        }

        const focusPaymentTimeout = window.setTimeout(() => {
            const firstPaymentInput = document.querySelector<HTMLInputElement>(
                `[data-tid="${TIDs.pages_order_payment}"] input[name="payment"]:not(:disabled)`,
            );

            firstPaymentInput?.focus();
            setShouldFocusPaymentAfterTransportChange(false);
        });

        return () => window.clearTimeout(focusPaymentTimeout);
    }, [isTransportSelectionLoading, shouldFocusPaymentAfterTransportChange, transport]);

    const toggleSelectedTransportGroup = (transportGroupUuid: string) =>
        setSelectedTransportGroupUuid((currentTransportGroupUuid) =>
            currentTransportGroupUuid === transportGroupUuid ? null : transportGroupUuid,
        );
    const resetSelectedTransportGroupAndPayment = async () => {
        setSelectedTransportGroupUuid(null);
        setShouldFocusPaymentAfterTransportChange(false);
        await resetTransportAndPayment();
    };

    return (
        <>
            <PacketeryContainer />

            <div data-tid={TIDs.pages_order_transport}>
                <div className="mb-3 flex items-center justify-between">
                    <h2 className="h4">{t('Choose transport')}</h2>

                    <AnimatePresence initial={false}>
                        {!!transport && transports.length > 1 && (
                            <AnimateCollapseDiv className="flex! relative flex-col" keyName="transport-reset">
                                <ResetButton
                                    disabled={isTransportSelectionLoading}
                                    text={t('Change transport type')}
                                    tid={TIDs.reset_transport_button}
                                    onClick={resetSelectedTransportGroupAndPayment}
                                />
                            </AnimateCollapseDiv>
                        )}
                    </AnimatePresence>
                </div>

                <fieldset>
                    <legend className="sr-only">{t('Choose transport type')}</legend>

                    <ul>
                        <AnimatePresence initial={false}>
                            {shouldShowSelectedTransport && (
                                <AnimateCollapseDiv
                                    className="block! relative"
                                    disableAnimation={transports.length === 1}
                                    keyName="transport-selected"
                                >
                                    <TransportListItem
                                        isActive
                                        changeTransport={changeTransportByInputMethod}
                                        disabled={isTransportSelectionLoading}
                                        openPickupPlacePopup={() => openPickupPlacePopup(transport.uuid)}
                                        pickupPlace={pickupPlace}
                                        transport={transport}
                                    />
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>

                        <AnimatePresence initial={false}>
                            {shouldShowTransportList && (
                                <AnimateCollapseDiv
                                    className="block! relative"
                                    disableAnimation={transports.length === 1}
                                    keyName="transport-list"
                                >
                                    {shouldDisplayTransportGroups &&
                                        transportGroupChoices.map(({ group, transports: groupTransports }) => {
                                            const isTransportGroupSelected = selectedTransportGroupUuid === group.uuid;

                                            return (
                                                <TransportGroupListItem
                                                    key={group.uuid}
                                                    changeTransport={changeTransportByInputMethod}
                                                    group={group}
                                                    isSelected={isTransportGroupSelected}
                                                    isTransportSelectionLoading={isTransportSelectionLoading}
                                                    pickupPlace={pickupPlace}
                                                    toggleSelectedTransportGroup={toggleSelectedTransportGroup}
                                                    transports={groupTransports}
                                                />
                                            );
                                        })}

                                    {transportsToDisplay.map((transportItem) => (
                                        <TransportListItem
                                            key={transportItem.uuid}
                                            changeTransport={changeTransportByInputMethod}
                                            disabled={
                                                isTransportSelectionLoading ||
                                                transportItem.productsBlockingSelectionInCart.length > 0
                                            }
                                            pickupPlace={pickupPlace}
                                            transport={transportItem}
                                        />
                                    ))}
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>
                    </ul>
                </fieldset>
            </div>

            <AnimatePresence initial={false}>
                {transport !== null && (
                    <AnimateCollapseDiv
                        className="flex! relative mt-12 flex-col"
                        keyName="payments-list"
                        tid={TIDs.pages_order_payment}
                    >
                        {isTransportSelectionLoading && (
                            <LoaderWithOverlay className="w-8" overlayClassName="rounded-xl" />
                        )}

                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="h4">{t('Choose payment')}</h2>

                            <AnimatePresence initial={false}>
                                {payment !== null && transport.payments.length > 1 && (
                                    <AnimateCollapseDiv className="flex! relative flex-col" keyName="payment-reset">
                                        <ResetButton
                                            disabled={isTransportSelectionLoading}
                                            text={t('Change payment type')}
                                            tid={TIDs.reset_payment_button}
                                            onClick={resetPaymentAndGoPayBankSwift}
                                        />
                                    </AnimateCollapseDiv>
                                )}
                            </AnimatePresence>
                        </div>

                        <fieldset>
                            <legend className="sr-only">{t('Choose payment type')}</legend>

                            <ul>
                                <AnimatePresence initial={false}>
                                    {shouldShowSelectedPayment && (
                                        <AnimateCollapseDiv
                                            className="block! relative"
                                            disableAnimation={transport.payments.length === 1}
                                            keyName="payment-selected"
                                        >
                                            <PaymentListItem
                                                isActive
                                                changePayment={changePaymentByInputMethod}
                                                disabled={isTransportSelectionLoading}
                                                payment={payment}
                                            />
                                        </AnimateCollapseDiv>
                                    )}
                                </AnimatePresence>

                                <AnimatePresence initial={false}>
                                    {shouldShowPaymentList && (
                                        <AnimateCollapseDiv
                                            className="block! relative"
                                            disableAnimation={transport.payments.length === 1}
                                            keyName="payment-list"
                                        >
                                            {transport.payments.map((paymentItem) => (
                                                <PaymentListItem
                                                    key={paymentItem.uuid}
                                                    changePayment={changePaymentByInputMethod}
                                                    disabled={isTransportSelectionLoading}
                                                    payment={paymentItem}
                                                />
                                            ))}
                                        </AnimateCollapseDiv>
                                    )}
                                </AnimatePresence>
                            </ul>
                        </fieldset>
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </>
    );
};

type ResetButtonProps = { text: string; onClick: () => void; tid: string; disabled?: boolean };

const ResetButton: FC<ResetButtonProps> = ({ text, onClick, tid, disabled }) => (
    <button
        className="cursor-pointer font-secondary font-semibold text-sm underline hover:no-underline"
        data-tid={tid}
        disabled={disabled}
        tabIndex={0}
        onClick={onClick}
    >
        {text}
    </button>
);
