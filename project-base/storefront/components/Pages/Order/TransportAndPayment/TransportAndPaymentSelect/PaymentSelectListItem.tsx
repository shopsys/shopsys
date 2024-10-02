import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { usePaymentChangeInSelect } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { memo } from 'react';

type ChangePayment = ReturnType<typeof usePaymentChangeInSelect>['changePayment'];

type PaymentListItemProps = {
    payment: TypeSimplePaymentFragment;
    isActive?: boolean;
    changePayment: ChangePayment;
};

const PaymentListItemComp: FC<PaymentListItemProps> = ({ payment, isActive = false, changePayment }) => {
    return (
        <TransportAndPaymentListItem key={payment.uuid} isActive={isActive}>
            <Radiobutton
                checked={isActive}
                id={payment.uuid}
                name="payment"
                value={payment.uuid}
                label={
                    <TransportAndPaymentSelectItemLabel
                        description={payment.description}
                        image={payment.mainImage}
                        isSelected={isActive}
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
