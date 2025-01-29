import { Footer } from './Footer/Footer';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';

export const ErrorLayout: FC = ({ children }) => (
    <div className="flex h-full min-h-screen flex-col">
        <Webline
            className="relative mb-4"
            wrapperClassName="bg-gradient-to-tr from-backgroundBrand to-backgroundBrandLess"
        >
            <Header simpleHeader />
        </Webline>
        {children}
        <Webline wrapperClassName="bg-backgroundAccentLess">
            <Footer simpleFooter />
        </Webline>
    </div>
);
