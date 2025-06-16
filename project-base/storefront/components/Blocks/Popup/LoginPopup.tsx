import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
import { Popup } from 'components/Layout/Popup/Popup';
import useTranslation from 'next-translate/useTranslation';

export const LoginPopup: FC<LoginFormProps> = ({ defaultEmail, shouldOverwriteCustomerUserCart, formHeading }) => {
    const { t } = useTranslation();

    return (
        <Popup className="w-full max-w-md" contentClassName="overflow-y-auto" title={t('Login')}>
            <LoginForm
                defaultEmail={defaultEmail}
                formContentWrapperClassName="!px-5"
                formHeading={formHeading}
                shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
            />
        </Popup>
    );
};
