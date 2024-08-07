import { Footer } from './Footer/Footer';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';

export const ErrorLayout: FC = ({ children }) => (
    <>
        <Webline
            className="relative mb-8"
            wrapperClassName="bg-gradient-to-tr from-backgroundBrand to-backgroundBrandLess"
        >
            <Header simpleHeader />
        </Webline>
        {children}
        <Webline wrapperClassName="bg-backgroundAccentLess">
            <Footer simpleFooter />
        </Webline>
    </>
);
