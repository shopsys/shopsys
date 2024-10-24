import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
import { Popup } from 'components/Layout/Popup/Popup';

export const LoginPopup: FC<LoginFormProps> = ({ defaultEmail, shouldOverwriteCustomerUserCart }) => {
    return (
        <Popup className="w-full max-w-md" contentClassName="overflow-y-auto">
            <LoginForm
                defaultEmail={defaultEmail}
                formContentWrapperClassName="!px-5"
                shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
            />
        </Popup>
    );
};
