import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';

type OrderStatusProps = {
    order: TypeListedOrderFragment | TypeOrderDetailFragment;
};

export const OrderStatus: FC<OrderStatusProps> = ({ order }) => {
    const { t } = useTranslation();

    return (
        <span>
            {order.status}
            {order.payment.type === PaymentTypeEnum.GoPay && (
                <>
                    {' '}
                    (
                    <span className={order.isPaid ? 'text-textSuccess' : 'text-textError'}>
                        {order.isPaid ? t('Paid') : t('Not paid')}
                    </span>
                    )
                </>
            )}
        </span>
    );
};
