import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const LoginContent: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <PageHero
                    description={t('Enter your email and password to log in to your account.')}
                    icon={UserIcon}
                    title={t('Log in to your account')}
                />

                <LoginForm />
            </VerticalStack>
        </Webline>
    );
};
