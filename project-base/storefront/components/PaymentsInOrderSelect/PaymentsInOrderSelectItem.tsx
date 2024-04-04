import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { TransportAndPaymentListItem } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentSelectItemLabel';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { useGoPaySwiftsQuery } from 'graphql/requests/payments/queries/GoPaySwiftsQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { Dispatch, SetStateAction } from 'react';

type PaymentsInOrderSelectItemProps = {
    payment: TypeSimplePaymentFragment;
    selectedPaymentForChange: TypeSimplePaymentFragment | undefined;
    setSelectedPaymentForChange: Dispatch<SetStateAction<TypeSimplePaymentFragment | undefined>>;
    selectedPaymentSwiftForChange?: string | null;
    setSelectedPaymentSwiftForChange?: Dispatch<SetStateAction<string | undefined | null>>;
};

export const PaymentsInOrderSelectItem: FC<PaymentsInOrderSelectItemProps> = ({
    payment,
    selectedPaymentForChange,
    setSelectedPaymentForChange,
    selectedPaymentSwiftForChange,
    setSelectedPaymentSwiftForChange,
}) => {
    const { currencyCode } = useDomainConfig();
    const [getGoPaySwiftsResult] = useGoPaySwiftsQuery({ variables: { currencyCode } });
    const { t } = useTranslation();

    const isBankSelectVisible =
        payment.uuid === selectedPaymentForChange?.uuid &&
        payment.type === 'goPay' &&
        payment.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' &&
        setSelectedPaymentSwiftForChange;

    return (
        <TransportAndPaymentListItem
            key={payment.uuid}
            className="order-none flex w-auto flex-col"
            isActive={selectedPaymentForChange?.uuid === payment.uuid}
        >
            <Radiobutton
                checked={payment.uuid === selectedPaymentForChange?.uuid}
                id={payment.uuid}
                name="payment"
                value={payment.uuid}
                label={
                    <TransportAndPaymentSelectItemLabel
                        description={payment.description}
                        image={payment.mainImage}
                        name={payment.name}
                        price={payment.price}
                    />
                }
                onChange={() => {
                    setSelectedPaymentForChange(payment);
                    setSelectedPaymentSwiftForChange?.(undefined);
                }}
            />
            {isBankSelectVisible && (
                <div className="relative flex w-full flex-col gap-1">
                    <div className="mb-1 font-bold">{t('Choose your bank')}</div>
                    {getGoPaySwiftsResult.data?.GoPaySwifts.map((goPaySwift) => (
                        <Radiobutton
                            key={goPaySwift.swift}
                            checked={selectedPaymentSwiftForChange === goPaySwift.swift}
                            id={goPaySwift.swift}
                            label={goPaySwift.name}
                            name="goPaySwift"
                            value={goPaySwift.swift}
                            onChange={(event) => setSelectedPaymentSwiftForChange(event.target.value)}
                        />
                    ))}
                </div>
            )}
        </TransportAndPaymentListItem>
    );
};
