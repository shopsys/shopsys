'use client';

import { ChangePassword } from './ChangePassword';
import { ResetPassword } from 'components/Pages/Customer/ChangePassword/ResetPassword';
import { CurrentCustomerType } from 'types/customer';

type ChangePasswordContentProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const ChangePasswordContent: FC<ChangePasswordContentProps> = ({ currentCustomerUser }) => {
    return currentCustomerUser.hasPasswordSet ? (
        <ChangePassword currentCustomerUser={currentCustomerUser} />
    ) : (
        <ResetPassword email={currentCustomerUser.email} />
    );
};
