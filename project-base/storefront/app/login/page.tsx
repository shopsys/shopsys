import { LoginForm } from 'app/_components/Blocks/LoginForm/LoginForm';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Webline } from 'components/Layout/Webline/Webline';

export const dynamic = 'force-dynamic';

const LoginPage = async () => {
    const t = await getTranslation();

    return (
        <Webline>
            <LoginForm formHeading={t('Log in')} />
        </Webline>
    );
};

export default LoginPage;
