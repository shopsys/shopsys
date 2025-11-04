import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
import { Popup } from 'components/Layout/Popup/Popup';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const LoginPopup: FC<LoginFormProps> = ({ defaultEmail, shouldOverwriteCustomerUserCart, formHeading }) => {
    const { t } = useTranslation();

    return (
        <Popup className="w-full max-w-md" contentClassName="overflow-y-auto" title={t('Login')}>
            <LoginForm
                defaultEmail={defaultEmail}
                formHeading={formHeading}
                formWrapperClassName="!p-5 w-full"
                shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
            />
        </Popup>
    );
};
