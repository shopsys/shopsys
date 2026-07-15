import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
import { Popup } from 'components/Layout/Popup/Popup';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const LoginPopup: FC<LoginFormProps> = ({
    defaultEmail,
    formName = 'popup-login-form',
    shouldOverwriteCustomerUserCart,
}) => {
    const { t } = useTranslation();
    const title = t('Log in and continue with order');

    return (
        <Popup className="w-full max-w-md" contentClassName="overflow-y-auto" isTitleHidden title={title}>
            <VerticalStack gap="xs">
                <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-background-most">
                    <UserIcon aria-hidden="true" className="size-7" focusable="false" />
                </div>

                <h4 className="text-center">{title}</h4>

                <LoginForm
                    defaultEmail={defaultEmail}
                    formName={formName}
                    formContentWrapperClassName="px-5!"
                    shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                />
            </VerticalStack>
        </Popup>
    );
};
