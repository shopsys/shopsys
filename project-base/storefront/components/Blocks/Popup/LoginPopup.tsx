import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
import { Popup } from 'components/Layout/Popup/Popup';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const LoginPopup: FC<LoginFormProps> = ({ defaultEmail, shouldOverwriteCustomerUserCart, formHeading }) => {
    const { t } = useTranslation();

    return (
        <Popup className="w-full max-w-md" contentClassName="overflow-y-auto" title={formHeading ?? t('Login')}>
            <VerticalStack gap="sm">
                <div className="bg-background-most mx-auto flex size-14 items-center justify-center rounded-full">
                    <UserIcon aria-hidden="true" className="size-7" focusable="false" />
                </div>

                <LoginForm
                    defaultEmail={defaultEmail}
                    formContentWrapperClassName="px-5!"
                    shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                />
            </VerticalStack>
        </Popup>
    );
};
