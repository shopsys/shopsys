import { ListedOrderFragmentApi, OrderDetailFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';

type OrderStatusProps = {
    order: ListedOrderFragmentApi | OrderDetailFragmentApi;
};

export const OrderStatus: FC<OrderStatusProps> = ({ order }) => {
    const { t } = useTranslation();

    return (
        <>
            {order.status}
            {order.payment.type === PaymentTypeEnum.GoPay && (
                <>
                    {' '}
                    (
                    <span className={order.isPaid ? 'text-greenDark' : 'text-red'}>
                        {order.isPaid ? t('Paid') : t('Not paid')}
                    </span>
                    )
                </>
            )}
        </>
    );
};
