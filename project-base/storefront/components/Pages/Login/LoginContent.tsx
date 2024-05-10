import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';

export const LoginContent: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline className="flex flex-col items-center">
            <h1 className="max-w-3xl w-full">{t('Login')}</h1>
            <LoginForm />
        </Webline>
    );
};
