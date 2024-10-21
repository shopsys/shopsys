import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type MenuUnauthenticatedProps = {
    isHoveredDelayed: boolean;
    isClicked: boolean;
};

export const MenuIconicItemUserUnauthenticatedContent: FC<MenuUnauthenticatedProps> = ({
    isHoveredDelayed,
    isClicked,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);

    return (
        <div className="flex w-full flex-col gap-8 lg:flex-row lg:p-5">
            <div className="order-2 mb-auto rounded-xl bg-backgroundBrand p-5 text-textInverted lg:order-1 lg:w-1/2 lg:p-9">
                <h4>{t('Benefits of registration')}</h4>
                <div className="my-4">
                    <p className="text-textInverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('There may be an advantage here')}
                    </p>
                    <p className="text-textInverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('I would write something better')}
                    </p>
                    <p className="text-textInverted">
                        <CheckmarkIcon className="mr-2" />
                        {t('It is not good to wait')}
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
            <div className="order-1 w-full lg:order-2 lg:w-[364px]">
                <LoginForm formContentWrapperClassName={isHoveredDelayed || isClicked ? '-hidden' : 'hidden'} />
            </div>
        </div>
    );
};
