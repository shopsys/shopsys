import Footer from './Footer';
import Header from './Header';
import Webline from './Webline';
import { FC } from 'react';

const ErrorLayout: FC = ({ children }) => {
    return (
        <>
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header simpleHeader />
            </Webline>
            {children}
            <Webline type="dark">
                <Footer />
            </Webline>
        </>
    );
};

/* @component */
export default ErrorLayout;
