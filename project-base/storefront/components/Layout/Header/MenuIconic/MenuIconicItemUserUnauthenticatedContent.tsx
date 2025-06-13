import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useFocusTrap } from 'utils/useFocusTrap';

export const MenuIconicItemUserUnauthenticatedContent: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);
    const contentRef = useRef<HTMLDivElement>(null);

    useFocusTrap(contentRef);

    return (
        <div className="vl:flex-row vl:p-5 flex w-full flex-col gap-8 text-left" ref={contentRef}>
            <div
                className="bg-background-brand-less text-text-inverted vl:order-1 vl:w-1/2 vl:p-9 order-2 mb-auto rounded-xl p-5"
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
                    aria-label={t('Go to registration page')}
                    aria-labelledby="registration-form-description"
                    href={registrationUrl}
                    skeletonType="registration"
                    tid={TIDs.login_popup_register_button}
                    variant="transparent"
                >
                    {t('Register')}
                    <ArrowSecondaryIcon className="size-5 -rotate-90 p-1 md:size-6" />
                </LinkButton>
            </div>
            <div className="vl:order-2 vl:w-[364px] order-1 w-full">
                <LoginForm formHeading={t('Log in')} />
            </div>
        </div>
    );
};
