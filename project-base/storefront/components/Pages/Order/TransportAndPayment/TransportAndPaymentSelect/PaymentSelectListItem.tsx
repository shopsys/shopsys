import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { KeyboardEvent, MouseEvent } from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { TransportAndPaymentItemLabel } from './TransportAndPaymentItemLabel';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';

type ChangePayment = (
    updatedPaymentUuid: string | null,
    event: KeyboardEvent<HTMLInputElement> | MouseEvent<HTMLInputElement>,
) => void;

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
        <TransportAndPaymentListItem
            key={payment.uuid}
            className={twJoin(
                'group mb-3 rounded-xl border border-transparent py-0 transition last:mb-0 last:border-b',
                'bg-background-more',
                !isActive && 'hover:border-border-less hover:bg-background-default',
            )}
        >
            <Radiobutton
                aria-label={ariaLabel}
                checked={isActive}
                disabled={disabled}
                id={payment.uuid}
                name="payment"
                shouldUseFocusOnlyArrowKeys
                value={payment.uuid}
                label={
                    <TransportAndPaymentItemLabel
                        description={payment.description}
                        image={payment.mainImage}
                        isActive={isActive}
                        isImageOnWhiteBackground
                        name={payment.name}
                        price={payment.price}
                    />
                }
                labelWrapperClassName={twJoin(
                    'px-4 vl:px-5 py-4 peer-focus-visible:outline-2 peer-focus-visible:outline-input-border-active peer-focus-visible:outline-offset-2 [&>span:first-child]:hidden',
                )}
                onClick={changePayment}
            />
        </TransportAndPaymentListItem>
    );
};
