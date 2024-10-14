import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const LoginPopup: FC<LoginFormProps> = ({ defaultEmail, shouldOverwriteCustomerUserCart }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);

    return (
        <Popup className="w-11/12 max-w-3xl" contentClassName="overflow-y-auto">
            <div className="h2 mb-3">{t('Login')}</div>
            <div className="flex w-full flex-col lg:flex-row">
                <div className="w-full border-b border-borderAccent lg:w-1/2 lg:border-b-0 lg:border-r lg:pr-5">
                    <LoginForm
                        defaultEmail={defaultEmail}
                        formContentWrapperClassName="!px-5"
                        shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                    />
                </div>

                <div className="mt-7 flex w-full flex-col lg:mt-0 lg:w-1/2 lg:pl-5">
                    <div className="-mr-4 mb-6 flex w-full justify-between rounded-l bg-backgroundAccentLess p-4">
                        <p className="text-lg text-textAccent lg:text-xl">
                            {t("Don't have an account yet? Register.")}
                        </p>
                    </div>

                    <p className="mb-8 hidden lg:block">
                        {t('Your addresses prefilled and you can check your order history.')}
                    </p>

                    <ExtendedNextLink
                        href={registrationUrl}
                        skeletonType="registration"
                        tid={TIDs.login_popup_register_button}
                    >
                        <Button variant="inverted">{t('Register')}</Button>
                    </ExtendedNextLink>
                </div>
            </div>
        </Popup>
    );
};
