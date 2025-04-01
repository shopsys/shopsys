import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';

type OrderStatusProps = {
    order: TypeListedOrderFragment | TypeOrderDetailFragment;
};

export const OrderStatus: FC<OrderStatusProps> = ({ order }) => {
    const { t } = useTranslation();

    return (
        <span>
            {order.status}
            {order.hasExternalPayment && (
                <>
                    {' '}
                    (
                    <span className={order.isPaid ? 'text-text-success' : 'text-text-error'}>
                        {order.isPaid ? t('Paid') : t('Not paid')}
                    </span>
                    )
                </>
            )}
        </span>
    );
};
