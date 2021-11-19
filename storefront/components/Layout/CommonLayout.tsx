import Button from 'components/Forms/Button';
import { FC } from 'react';
import Footer from './Footer';
import Header from './Header';
import Navigation from './Header/Navigation';
import NewsletterForm from './Footer/NewsletterForm';
import { useAuth } from 'hooks/auth/UseAuth';
import Webline from './Webline';

/**
 * Basic page layout for common pages
 */
const CommonLayout: FC = (props) => {
    const [[, login], [, logout], [isUserLoggedIn]] = useAuth();

    return (
        <>
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header />
                <Button type="button" onClick={() => login({ email: 'no-reply@shopsys.com', password: 'user123' })}>
                    Login
                </Button>
                <Button type="button" variant="secondary" onClick={() => logout()}>
                    Logout
                </Button>
                <h1>{isUserLoggedIn ? 'logged' : 'logged out'}</h1>
                <Navigation />
            </Webline>
            {props.children}
            <Webline type="light">
                <NewsletterForm />
            </Webline>
            <Webline type="dark">
                <Footer />
            </Webline>
        </>
    );
};

/* @component */
export default CommonLayout;
