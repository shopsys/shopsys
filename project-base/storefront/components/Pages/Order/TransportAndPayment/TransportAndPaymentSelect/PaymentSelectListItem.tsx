import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { usePaymentChangeInSelect } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { memo } from 'react';

type ChangePayment = ReturnType<typeof usePaymentChangeInSelect>['changePayment'];

type PaymentListItemProps = {
    payment: TypeSimplePaymentFragment;
    isActive?: boolean;
    changePayment: ChangePayment;
};

const PaymentListItemComp: FC<PaymentListItemProps> = ({ payment, isActive = false, changePayment }) => {
    const { t } = useTranslation();

    return (
        <TransportAndPaymentListItem key={payment.uuid}>
            <Radiobutton
                aria-label={t('Choose payment {{ paymentName }}', { paymentName: payment.name })}
                checked={isActive}
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
                onClick={changePayment}
            />
        </TransportAndPaymentListItem>
    );
};

export const PaymentListItem = memo(PaymentListItemComp);
