import { Footer } from './Footer/Footer';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';
import { FC } from 'react';

export const ErrorLayout: FC = ({ children }) => {
    return (
        <>
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header simpleHeader />
            </Webline>
            {children}
            <Webline type="dark">
                <Footer simpleFooter />
            </Webline>
        </>
    );
};
