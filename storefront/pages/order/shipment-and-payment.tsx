import CommonLayout from 'components/layout/CommonLayout';
import { FC } from 'react';
import OrderSteps from 'components/blocks/orderSteps';

const ShipmentAndPayment: FC = () => {
    return (
        <CommonLayout>
            <OrderSteps activeStep={2} />
            Shipment and payment - step 2
        </CommonLayout>
    );
};

export default ShipmentAndPayment;
