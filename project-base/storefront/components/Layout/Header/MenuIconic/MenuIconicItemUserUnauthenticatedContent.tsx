import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useFocusTrap } from 'utils/useFocusTrap';

type MenuIconicItemUserUnauthenticatedContentProps = {
    loginFormName: string;
    onMenuClose: () => void;
};

type RegistrationBenefitsProps = {
    registrationUrl: string;
    onMenuClose: () => void;
};

const RegistrationBenefits: FC<RegistrationBenefitsProps> = ({ registrationUrl, onMenuClose }) => {
    const { t } = useTranslation();
    const registrationBenefits = [
        t('Faster checkout for purchases'),
        t('Simplified complaint process'),
        t('Order history for easy reordering'),
    ];

    return (
        <section
            aria-labelledby="registration-benefits-title"
            className="order-2 vl:order-1 mb-auto vl:w-1/2 rounded-xl bg-background-brand-less p-5 vl:p-9 text-text-inverted"
        >
            <h3 className="h4" id="registration-benefits-title">
                {t('Benefits of registration')}
            </h3>

            <ul className="my-4 flex flex-col gap-1" id="registration-benefits-list">
                {registrationBenefits.map((registrationBenefit) => (
                    <li key={registrationBenefit} className="flex items-start gap-2 text-text-inverted">
                        <CheckmarkIcon aria-hidden="true" className="mt-0.5 size-5 shrink-0" focusable="false" />
                        <span>{registrationBenefit}</span>
                    </li>
                ))}
            </ul>

            <LinkButton
                aria-describedby="registration-benefits-list"
                aria-label={t('Register. Go to registration page', { ns: 'accessibility' })}
                href={registrationUrl}
                size="large"
                skeletonType="registration"
                tid={TIDs.login_popup_register_button}
                variant="inverted"
                onClick={onMenuClose}
            >
                {t('Register')}
                <ArrowSecondaryIcon aria-hidden="true" className="size-5 -rotate-90 p-1 md:size-6" focusable="false" />
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
    const contentRef = useRef<HTMLDivElement>(null);

    useFocusTrap(contentRef);

    return (
        <div className="flex w-full vl:flex-row flex-col gap-8 vl:p-5 text-left" ref={contentRef}>
            <RegistrationBenefits registrationUrl={registrationUrl} onMenuClose={onMenuClose} />

            <div className="order-1 vl:order-2 vl:w-91 w-full">
                <h3 className="h4 mb-5">{t('Log in to your account')}</h3>

                <LoginForm formName={loginFormName} />
            </div>
        </div>
    );
};
