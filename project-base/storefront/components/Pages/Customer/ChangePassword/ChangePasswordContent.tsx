import { ResetPassword } from 'components/Pages/Customer/ChangePassword/ResetPassword';
import { CurrentCustomerType } from 'types/customer';
import { ChangePassword } from './ChangePassword';

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
