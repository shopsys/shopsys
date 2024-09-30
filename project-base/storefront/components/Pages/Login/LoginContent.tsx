import { LoginForm } from 'components/Blocks/Login/LoginForm';
import { Webline } from 'components/Layout/Webline/Webline';

export const LoginContent: FC = () => {
    return (
        <Webline className="flex flex-col items-center">
            <LoginForm />
        </Webline>
    );
};
