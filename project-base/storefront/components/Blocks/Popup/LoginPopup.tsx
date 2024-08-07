import { Link } from 'components/Basic/Link/Link';
import { LoginForm, LoginFormProps } from 'components/Blocks/Login/LoginForm';
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
        <Popup className="w-11/12 max-w-3xl">
            <div className="h2 mb-3">{t('Login')}</div>
            <div className="flex w-full flex-col lg:flex-row">
                <div className="w-full border-b border-borderAccent lg:w-1/2 lg:border-b-0 lg:border-r lg:pr-5">
                    <LoginForm
                        defaultEmail={defaultEmail}
                        formContentWrapperClassName="!px-5"
                        shouldOverwriteCustomerUserCart={shouldOverwriteCustomerUserCart}
                    />
                </div>

                <div className="mt-7 w-full lg:mt-0 lg:w-1/2 lg:pl-5 flex flex-col">
                    <div className="mb-6 -mr-4 flex w-full justify-between rounded-l bg-backgroundAccentLess p-4">
                        <p className="text-lg text-textAccent lg:text-xl">
                            {t("Don't have an account yet? Register.")}
                        </p>
                    </div>

                    <p className="mb-8 hidden lg:block">
                        {t('Your addresses prefilled and you can check your order history.')}
                    </p>

                    <Link
                        isButton
                        buttonVariant="inverted"
                        href={registrationUrl}
                        tid={TIDs.login_popup_register_button}
                    >
                        {t('Register')}
                    </Link>
                </div>
            </div>
        </Popup>
    );
};
