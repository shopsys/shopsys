import { OrderDetailBasicInfo } from './OrderDetailBasicInfo';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';

type OrderDetailContentProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order }) => {
    const { t } = useTranslation();

    const orderHeading = `${t('Order number')} ${order.number}`;

    return (
        <VerticalStack gap="sm">
            <h1 data-tid={TIDs.order_detail_number_heading}>{orderHeading}</h1>

            <OrderDetailBasicInfo order={order} />

            <OrderCustomerInfo order={order} />
        </VerticalStack>
    );
};
