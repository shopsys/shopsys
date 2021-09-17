import { FC } from 'react';
import Header from './Header';
import Navigation from './Header/Navigation';
import NewsletterForm from './Footer/NewsletterForm';
import { ServerSidePropsType } from 'helpers/InitServerSideProps';
import Webline from './Webline';

/**
 * Basic page layout for common pages
 */
const CommonLayout: FC<ServerSidePropsType> = (props) => {
    return (
        <>
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header {...props}></Header>
                <Navigation></Navigation>
            </Webline>
            {props.children}
            <Webline type="light">
                <NewsletterForm />
            </Webline>
        </>
    );
};

/* @component */
export default CommonLayout;
