import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ClockIcon } from 'components/Basic/Icon/ClockIcon';
import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { OrderIcon } from 'components/Basic/Icon/OrderIcon';
import { Image } from 'components/Basic/Image/Image';
import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import logo from '/public/images/logo.svg';

type MenuIconicItemUserUnauthenticatedContentProps = {
    loginFormName: string;
    onMenuClose: () => void;
};

type RegistrationBenefitsProps = {
    idPrefix: string;
    registrationUrl: string;
    onMenuClose: () => void;
};

const RegistrationBenefits: FC<RegistrationBenefitsProps> = ({ idPrefix, registrationUrl, onMenuClose }) => {
    const { t } = useTranslation();
    const titleId = `${idPrefix}-registration-benefits-title`;
    const listId = `${idPrefix}-registration-benefits-list`;
    const registrationBenefits = [
        { Icon: ClockIcon, text: t('Faster checkout next time') },
        { Icon: OrderIcon, text: t('Order history and easy reordering') },
        { Icon: DocumentIcon, text: t('Easier complaint handling') },
    ];

    return (
        <section
            aria-labelledby={titleId}
            className="relative isolate vl:order-1 vl:flex hidden min-w-0 flex-col justify-center overflow-hidden bg-background-accent-less px-6 vl:px-10 py-10 vl:py-12 text-text-default"
        >
            <div aria-hidden="true" className="pointer-events-none absolute inset-0 -z-1 overflow-hidden">
                <Image
                    alt=""
                    className="absolute top-24 -right-16 h-auto w-[120%] max-w-none -rotate-12 opacity-[0.035] brightness-0"
                    src={logo}
                />
            </div>

            <h3 className="font-semibold text-2xl leading-8 tracking-tight" id={titleId}>
                {t('Why create an account?')}
            </h3>

            <ul className="mt-7 mb-9 flex flex-col gap-5" id={listId}>
                {registrationBenefits.map(({ Icon, text }) => (
                    <li key={text} className="flex items-center gap-3 text-md text-text-default leading-6">
                        <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-background-default/80 text-icon-accent">
                            <Icon aria-hidden="true" className="size-5" focusable="false" />
                        </span>
                        <span>{text}</span>
                    </li>
                ))}
            </ul>

            <LinkButton
                aria-describedby={listId}
                href={registrationUrl}
                className="w-full"
                size="xlarge"
                skeletonType="registration"
                tid={TIDs.login_popup_register_button}
                variant="secondary"
                onClick={onMenuClose}
            >
                {t('Create account')}
            </LinkButton>
        </section>
    );
};

export const MenuIconicItemUserUnauthenticatedContent: FC<MenuIconicItemUserUnauthenticatedContentProps> = ({
    loginFormName,
    onMenuClose,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);
    const loginFormEmail = useSessionStore((store) => store.loginFormEmail);
    const setLoginFormEmail = useSessionStore((store) => store.setLoginFormEmail);

    return (
        <div className="grid w-full vl:grid-cols-2 text-left">
            <div className="vl:order-2 w-full min-w-0 px-6 vl:px-10 vl:py-12 pt-14 pb-8">
                <h3 className="mb-6 vl:mb-7 text-balance font-semibold text-2xl leading-8 tracking-tight">
                    {t('Login')}
                </h3>

                <LoginForm
                    defaultEmail={loginFormEmail}
                    formName={loginFormName}
                    formContentWrapperClassName="gap-4"
                    isCompact
                    onEmailChange={(event) => setLoginFormEmail(event.currentTarget.value)}
                />
            </div>

            <p className="vl:hidden bg-background-accent-less px-6 py-6 text-center text-sm text-text-less">
                {t("Don't have an account?")}{' '}
                <ExtendedNextLink
                    className="inline-block font-semibold"
                    href={registrationUrl}
                    skeletonType="registration"
                    tid={TIDs.login_popup_register_link}
                    onClick={onMenuClose}
                >
                    {t('Register')}
                </ExtendedNextLink>
            </p>

            <RegistrationBenefits
                idPrefix={loginFormName}
                registrationUrl={registrationUrl}
                onMenuClose={onMenuClose}
            />
        </div>
    );
};
