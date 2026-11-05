import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { LoginPopupLayout } from './LoginPopupLayout';

export const LoginPopup: FC<LoginFormProps> = ({
    defaultEmail,
    formName = 'popup-login-form',
    shouldOverwriteCustomerUserCart,
}) => {
    const { t } = useTranslation();
    const title = t('Log in and continue with order');

    return (
        <LoginPopupLayout title={title}>
            <div className="px-6 vl:px-10 vl:py-12 pt-14 pb-8">
                <h3 className="mb-7 text-balance font-semibold text-2xl leading-8 tracking-tight">{title}</h3>
                <LoginForm
                    defaultEmail={defaultEmail}
                    formName={formName}
                    formContentWrapperClassName="gap-4"
                    isCompact
                    shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                />
            </div>
        </LoginPopupLayout>
    );
};
