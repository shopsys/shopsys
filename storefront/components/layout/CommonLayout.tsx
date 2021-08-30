import { FC } from 'react';
import Header from './Header';
import Navigation from './Header/Navigation';
import NewsletterForm from './Footer/NewsletterForm';
import Webline from './Webline';

const CommonLayout: FC = (props) => {
    return (
        <>
            <Webline type="colored" style={{ marginBottom: '32px' }}>
                <Header></Header>
                <Navigation></Navigation>
            </Webline>
            {props.children}
            <Webline type="light">
                <NewsletterForm />
            </Webline>
        </>
    );
};

export default CommonLayout;
