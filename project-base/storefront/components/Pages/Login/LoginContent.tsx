import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const LoginContent: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [registrationUrl] = getInternationalizedStaticUrls(['/registration'], url);

    return (
        <Webline width="sm">
            <VerticalStack gap="sm">
                <PageHero
                    description={
                        <>
                            {t('Do not have an account?')}{' '}
                            <ExtendedNextLink href={registrationUrl} skeletonType="registration">
                                {t('Register')}
                            </ExtendedNextLink>
                        </>
                    }
                    icon={UserIcon}
                    title={t('Log in to your account')}
                />

                <LoginForm />
            </VerticalStack>
        </Webline>
    );
};
