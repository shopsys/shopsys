import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { usePaymentChangeInSelect } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';

type ChangePayment = ReturnType<typeof usePaymentChangeInSelect>['changePayment'];

type PaymentListItemProps = {
    payment: TypeSimplePaymentFragment;
    isActive?: boolean;
    disabled?: boolean;
    changePayment: ChangePayment;
};

export const PaymentListItem: FC<PaymentListItemProps> = ({ payment, isActive = false, disabled, changePayment }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    const ariaLabel = isPriceVisible(payment.price.priceWithVat)
        ? t('Choose payment {{ paymentName }} for {{ price }}', {
              ns: 'accessibility',
              paymentName: payment.name,
              price: formatPrice(payment.price.priceWithVat),
          })
        : t('Choose payment {{ paymentName }}', {
              ns: 'accessibility',
              paymentName: payment.name,
          });

    return (
        <TransportAndPaymentListItem key={payment.uuid}>
            <Radiobutton
                aria-label={ariaLabel}
                checked={isActive}
                disabled={disabled}
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
