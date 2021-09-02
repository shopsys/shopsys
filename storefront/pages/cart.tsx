import CommonLayout from 'components/layout/CommonLayout';
import { FC } from 'react';
import OrderSteps from 'components/blocks/orderSteps';

const Cart: FC = () => {
    return (
        <CommonLayout>
            <OrderSteps activeStep={1} />
            Cart - step 1
        </CommonLayout>
    );
};

export default Cart;
