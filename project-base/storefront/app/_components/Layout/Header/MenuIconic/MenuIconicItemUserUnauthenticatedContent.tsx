'use client';

import { LoginForm } from 'app/_components/Blocks/LoginForm/LoginForm';
import { useInternationalizedStaticUrls } from 'app/_hooks/useInternationalizedStaticUrls';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { Button } from 'components/Forms/Button/Button';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TIDs } from 'cypress/tids';

export const MenuIconicItemUserUnauthenticatedContent: FC = () => {
    const { t } = useTranslation();

    const [registrationUrl] = useInternationalizedStaticUrls(['/registration']);

    return (
        <div className="vl:flex-row vl:p-5 flex w-full flex-col gap-8">
            <div className="bg-background-brand-less text-text-inverted vl:order-1 vl:w-1/2 vl:p-9 order-2 mb-auto rounded-xl p-5">
                <h4>{t('Benefits of registration')}</h4>
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

                <ExtendedNextLink
                    href={registrationUrl}
                    skeletonType="registration"
                    tid={TIDs.login_popup_register_button}
                >
                    <Button variant="transparent">
                        {t('Register')}
                        <ArrowSecondaryIcon className="size-5 -rotate-90 p-1 md:size-6" />
                    </Button>
                </ExtendedNextLink>
            </div>

            <div className="vl:order-2 vl:w-[364px] order-1 w-full">
                <LoginForm formHeading={t('Log in')} />
            </div>
        </div>
    );
};
