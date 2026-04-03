import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderDetailWithdrawalSection } from './OrderDetailWithdrawalSection';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    return (
        <>
            <OrderDetailWithdrawalSection order={order} />

            <OrderDetailBasicInfo order={order} />

            <OrderCustomerInfo order={order} />
        </>
    );
};
