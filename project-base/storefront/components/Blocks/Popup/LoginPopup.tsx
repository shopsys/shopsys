import { LoginForm, LoginFormProps } from 'app/_components/LoginForm/LoginForm';
import { Popup } from 'components/Layout/Popup/Popup';

export const LoginPopup: FC<LoginFormProps> = ({ defaultEmail, shouldOverwriteCustomerUserCart, formHeading }) => {
    return (
        <Popup className="w-full max-w-md" contentClassName="overflow-y-auto">
            <LoginForm
                defaultEmail={defaultEmail}
                formContentWrapperClassName="!px-5"
                formHeading={formHeading}
                shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
            />
        </Popup>
    );
};
