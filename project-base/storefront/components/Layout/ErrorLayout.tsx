import { Footer } from './Footer/Footer';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';

export const ErrorLayout: FC = ({ children }) => (
    <>
        <Webline className="relative mb-8" type="colored">
            <Header simpleHeader />
        </Webline>
        {children}
        <Webline type="dark">
            <Footer simpleFooter />
        </Webline>
    </>
);
