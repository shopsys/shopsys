import Footer from './Footer';
import NewsletterForm from './Footer/NewsletterForm';
import Header from './Header';
import Navigation from './Header/Navigation';
import NotificationBars from './NotificationBars';
import Webline from './Webline';
import Adverts from 'components/Blocks/Adverts';
import { FC } from 'react';

/**
 * Basic page layout for common pages
 */
const CommonLayout: FC = (props) => {
    return (
        <>
            <NotificationBars />
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header />
                <Navigation />
            </Webline>
            <Adverts positionName="header" withGapBottom withWebline />
            {props.children}
            <Adverts positionName="footer" withGapBottom withGapTop withWebline />
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
