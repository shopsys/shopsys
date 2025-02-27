import { Footer } from './Footer/Footer';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';

export const ErrorLayout: FC = ({ children }) => (
    <div className="flex h-full min-h-screen flex-col">
        <header>
            <Webline
                className="relative mb-4"
                wrapperClassName="bg-linear-to-tr/srgb from-backgroundBrand to-backgroundBrandLess"
            >
                <Header simpleHeader />
            </Webline>
        </header>

        <main className="mt-4 mb-10 flex flex-col gap-4">{children}</main>

        <footer>
            <Webline wrapperClassName="bg-backgroundAccentLess">
                <Footer simpleFooter />
            </Webline>
        </footer>
    </div>
);
