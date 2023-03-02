import { Footer } from './Footer/Footer';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';

export const ErrorLayout: FC = ({ children }) => (
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
