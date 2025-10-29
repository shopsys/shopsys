import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    return (
        <>
            <OrderDetailBasicInfo order={order} />

            <OrderCustomerInfo order={order} />
        </>
    );
};
