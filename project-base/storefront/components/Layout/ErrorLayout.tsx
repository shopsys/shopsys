import { Footer } from './Footer/Footer';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';

export const ErrorLayout: FC = ({ children }) => (
    <>
        <Webline type="colored" className="relative mb-8">
            <Header simpleHeader />
        </Webline>
        {children}
        <Webline type="dark">
            <Footer simpleFooter />
        </Webline>
    </>
);
