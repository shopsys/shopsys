import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { GiftVouchersExceedPayableAmountWarning } from 'components/Pages/Order/GiftVouchersExceedPayableAmountWarning';
import { PacketeryContainer } from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import {
    getShouldDisplayTransportGroups,
    getTransportGroupChoices,
    getTransportsWithoutGroup,
    usePaymentChangeInSelect,
    useTransportChangeInSelect,
} from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { TypePaymentTypeEnum } from 'graphql/types';
import { KeyboardEvent, MouseEvent, useEffect, useEffectEvent, useMemo, useRef, useState } from 'react';
import { ChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { ChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getIsPaymentWithPaymentGate } from 'utils/mappers/payment';
import { isEmailTransport } from 'utils/packetery';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { EmailGiftVoucherInfo } from './EmailGiftVoucherInfo';
import { NoAvailablePaymentInfo } from './NoAvailablePaymentInfo';
import { PaidByGiftVoucherInfo } from './PaidByGiftVoucherInfo';
import { PaymentListItem } from './PaymentSelectListItem';
import { TransportGroupListItem } from './TransportGroupListItem';
import { TransportListItem } from './TransportSelectListItem';

type TransportAndPaymentSelectProps = {
    transports: TypeTransportWithAvailablePaymentsFragment[];
    lastOrderPickupPlace: StoreOrPacketeryPoint | null;
    changeTransportInCart: ChangeTransportInCart;
    changePaymentInCart: ChangePaymentInCart;
    isChangingTransportInCart: boolean;
    isTransportSelectionLoading: boolean;
    hasElectronicGiftVouchers: boolean;
    isEmailTransportPreselected: boolean;
    isSingularElectronicGiftVoucher: boolean;
    isNothingLeftToPay: boolean;
    giftVouchersExceedPayableAmount: boolean;
    cartContainsGiftVoucherProducts: boolean;
};

export const TransportAndPaymentSelect: FC<TransportAndPaymentSelectProps> = ({
    transports,
    lastOrderPickupPlace,
    changeTransportInCart,
    changePaymentInCart,
    isChangingTransportInCart,
    isTransportSelectionLoading,
    hasElectronicGiftVouchers,
    isEmailTransportPreselected,
    isSingularElectronicGiftVoucher,
    isNothingLeftToPay,
    giftVouchersExceedPayableAmount,
    cartContainsGiftVoucherProducts,
}) => {
    const { t } = useTranslation();
    const { canSeePrices } = useAuthorization();
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
    const emailTransportDescription =
        transports.find((transportItem) => isEmailTransport(transportItem.transportTypeCode))?.description ?? null;
    // the gift voucher payment is never selected by the customer, it is preselected automatically once there is nothing left to pay
    // customers who cannot see prices cannot authorize an online payment, so gateway payments are not offered to them
    const selectablePayments = useMemo(
        () =>
            transport?.payments.filter(
                (paymentItem) =>
                    paymentItem.type !== TypePaymentTypeEnum.GiftVoucher &&
                    (canSeePrices || !getIsPaymentWithPaymentGate(paymentItem.type)),
            ) ?? [],
        [transport, canSeePrices],
    );
    const giftVoucherPayment = transport?.payments.find(
        (paymentItem) => paymentItem.type === TypePaymentTypeEnum.GiftVoucher,
    );
    const onlySelectablePayment = selectablePayments.length === 1 ? selectablePayments[0] : undefined;
    const paymentToAutoSelect = isNothingLeftToPay ? giftVoucherPayment : onlySelectablePayment;
    const automaticPaymentSelectionAttemptRef = useRef<string | null>(null);
    const paymentHeadingRef = useRef<HTMLHeadingElement>(null);
    const isPaidByGiftVoucher = isNothingLeftToPay && payment?.type === TypePaymentTypeEnum.GiftVoucher;
    const shouldShowGiftVoucherWarning =
        transport !== null && !isChangingTransportInCart && giftVouchersExceedPayableAmount;
    const shouldShowPaidByGiftVoucherInfo = transport !== null && !isTransportSelectionLoading && isPaidByGiftVoucher;
    const shouldShowSelectedTransport = !!transport;
    const shouldShowTransportList = !transport;
    const shouldShowSelectedPayment = !!payment;
    const shouldShowPaymentList = !payment;
    const hasNoSelectablePayment = transport !== null && !isNothingLeftToPay && selectablePayments.length === 0;

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
        automaticPaymentSelectionAttemptRef.current = null;
        changePayment(updatedPaymentUuid);
    };

    const autoSelectPayment = useEffectEvent(async () => {
        if (!transport || !paymentToAutoSelect || payment?.uuid === paymentToAutoSelect.uuid) {
            return;
        }

        const selectionKey = `${transport.uuid}:${paymentToAutoSelect.uuid}`;

        if (automaticPaymentSelectionAttemptRef.current === selectionKey) {
            return;
        }

        automaticPaymentSelectionAttemptRef.current = selectionKey;
        const changedCart = await changePaymentInCart(paymentToAutoSelect.uuid, null);

        if (changedCart && automaticPaymentSelectionAttemptRef.current === selectionKey) {
            automaticPaymentSelectionAttemptRef.current = null;
        }
    });

    useEffect(() => {
        if (!transport || !paymentToAutoSelect) {
            automaticPaymentSelectionAttemptRef.current = null;

            return;
        }

        if (!isTransportSelectionLoading) {
            void autoSelectPayment();
        }
    }, [isTransportSelectionLoading, payment?.uuid, paymentToAutoSelect?.uuid, transport?.uuid]);

    useEffect(() => {
        if (!shouldFocusPaymentAfterTransportChange || !transport || isTransportSelectionLoading) {
            return;
        }

        const focusPaymentTimeout = window.setTimeout(() => {
            const firstPaymentInput = document.querySelector<HTMLInputElement>(
                `[data-tid="${TIDs.pages_order_payment}"] input[name="payment"]:not(:disabled)`,
            );

            if (firstPaymentInput) {
                firstPaymentInput.focus();
            } else {
                paymentHeadingRef.current?.focus();
            }

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
                {isEmailTransportPreselected ? (
                    <EmailGiftVoucherInfo
                        description={emailTransportDescription}
                        isSingular={isSingularElectronicGiftVoucher}
                    />
                ) : (
                    <>
                        {hasElectronicGiftVouchers && (
                            <div className="mb-3">
                                <EmailGiftVoucherInfo
                                    description={emailTransportDescription}
                                    isSingular={isSingularElectronicGiftVoucher}
                                />
                            </div>
                        )}
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
                                                    const isTransportGroupSelected =
                                                        selectedTransportGroupUuid === group.uuid;

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
                    </>
                )}
            </div>

            <AnimatePresence initial={false}>
                {(shouldShowGiftVoucherWarning || shouldShowPaidByGiftVoucherInfo) && (
                    <AnimateCollapseDiv className="flex! relative flex-col" keyName="gift-voucher-payment-info">
                        <div className="mt-12">
                            {shouldShowGiftVoucherWarning ? (
                                <GiftVouchersExceedPayableAmountWarning
                                    cartContainsGiftVoucherProducts={cartContainsGiftVoucherProducts}
                                />
                            ) : (
                                <PaidByGiftVoucherInfo />
                            )}
                        </div>
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>

            <AnimatePresence initial={false}>
                {transport !== null && !isNothingLeftToPay && (
                    <AnimateCollapseDiv
                        className="flex! relative mt-12 flex-col"
                        keyName="payments-list"
                        tid={TIDs.pages_order_payment}
                    >
                        {isTransportSelectionLoading && (
                            <LoaderWithOverlay className="w-8" overlayClassName="rounded-xl" />
                        )}

                        {hasNoSelectablePayment && <NoAvailablePaymentInfo />}

                        {!hasNoSelectablePayment && (
                            <>
                                <div className="mb-3 flex items-center justify-between">
                                    <h2 ref={paymentHeadingRef} className="h4" tabIndex={-1}>
                                        {onlySelectablePayment ? t('Payment') : t('Choose payment')}
                                    </h2>

                                    <AnimatePresence initial={false}>
                                        {payment !== null && selectablePayments.length > 1 && (
                                            <AnimateCollapseDiv
                                                className="flex! relative flex-col"
                                                keyName="payment-reset"
                                            >
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
                                    <legend className="sr-only">
                                        {onlySelectablePayment ? t('Payment') : t('Choose payment type')}
                                    </legend>

                                    <ul>
                                        <AnimatePresence initial={false}>
                                            {shouldShowSelectedPayment && (
                                                <AnimateCollapseDiv
                                                    className="block! relative"
                                                    disableAnimation={selectablePayments.length === 1}
                                                    keyName="payment-selected"
                                                >
                                                    <PaymentListItem
                                                        isActive
                                                        changePayment={changePaymentByInputMethod}
                                                        disabled={isTransportSelectionLoading}
                                                        isSelectable={!onlySelectablePayment}
                                                        payment={payment}
                                                    />
                                                </AnimateCollapseDiv>
                                            )}
                                        </AnimatePresence>

                                        <AnimatePresence initial={false}>
                                            {shouldShowPaymentList && (
                                                <AnimateCollapseDiv
                                                    className="block! relative"
                                                    disableAnimation={selectablePayments.length === 1}
                                                    keyName="payment-list"
                                                >
                                                    {selectablePayments.map((paymentItem) => (
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
                            </>
                        )}
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
