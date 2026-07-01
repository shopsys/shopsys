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

export const MenuIconicItemUserUnauthenticatedContent: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);
    const contentRef = useRef<HTMLDivElement>(null);

    useFocusTrap(contentRef);

    return (
        <div className="flex w-full vl:flex-row flex-col gap-8 vl:p-5 text-left" ref={contentRef}>
            <div
                className="order-2 vl:order-1 mb-auto vl:w-1/2 rounded-xl bg-background-brand-less p-5 vl:p-9 text-text-inverted"
                id="registration-form-description"
            >
                <span className="h4">{t('Benefits of registration')}</span>
                <div className="my-4">
                    <p className="text-text-inverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('Faster checkout for purchases')}
                    </p>
                    <p className="text-text-inverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('Simplified complaint process')}
                    </p>
                    <p className="text-text-inverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('Order history for easy reordering')}
                    </p>
                </div>

                <LinkButton
                    aria-describedby="registration-form-description"
                    aria-label={t('Register. Go to registration page', { ns: 'accessibility' })}
                    href={registrationUrl}
                    skeletonType="registration"
                    tid={TIDs.login_popup_register_button}
                    variant="transparent"
                >
                    {t('Register')}
                    <ArrowSecondaryIcon className="size-5 -rotate-90 p-1 md:size-6" />
                </LinkButton>
            </div>
            <div className="order-1 vl:order-2 vl:w-91 w-full">
                <h3 className="h4 mb-5">{t('Log in to your account')}</h3>

                <LoginForm />
            </div>
        </div>
    );
};
