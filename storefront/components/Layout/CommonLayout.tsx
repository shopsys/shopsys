import { FC } from 'react';
import Footer from './Footer';
import Header from './Header';
import Navigation from './Header/Navigation';
import NewsletterForm from './Footer/NewsletterForm';
import Webline from './Webline';

/**
 * Basic page layout for common pages
 */
const CommonLayout: FC = (props) => {
    return (
        <>
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header />
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
