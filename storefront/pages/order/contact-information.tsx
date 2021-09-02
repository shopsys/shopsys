import CommonLayout from 'components/layout/CommonLayout';
import { FC } from 'react';
import OrderSteps from 'components/blocks/orderSteps';

const ContactInformation: FC = (props) => {
    return (
        <CommonLayout>
            <OrderSteps activeStep={3} />
            Contact information - step 3
        </CommonLayout>
    );
};

export default ContactInformation;
