'use client';

import { LoginForm } from 'app/_components/Blocks/LoginForm/LoginForm';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const MenuIconicItemUserUnauthenticatedContent: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);

    return (
        <div className="flex w-full flex-col gap-8 vl:flex-row vl:p-5">
            <div className="order-2 mb-auto rounded-xl bg-backgroundBrandLess p-5 text-textInverted vl:order-1 vl:w-1/2 vl:p-9">
                <h4>{t('Benefits of registration')}</h4>
                <div className="my-4">
                    <p className="text-textInverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('Faster checkout for purchases')}
                    </p>
                    <p className="text-textInverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('Simplified complaint process')}
                    </p>
                    <p className="text-textInverted">
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

            <div className="order-1 w-full vl:order-2 vl:w-[364px]">
                <LoginForm formHeading={t('Log in')} />
            </div>
        </div>
    );
};
