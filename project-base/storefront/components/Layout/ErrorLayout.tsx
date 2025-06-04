import { Footer } from './Footer/Footer';
import { AccessibilityNavigation } from './Header/AccessibilityNavigation/AccessibilityNavigation';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';

export const ErrorLayout: FC = ({ children }) => (
    <div className="flex h-full min-h-screen flex-col">
        <AccessibilityNavigation />

        <header className="from-background-brand to-background-brand-less bg-linear-to-tr/srgb">
            <Header simpleHeader />
        </header>

        <main className="mt-4 mb-10 flex flex-col gap-4">{children}</main>

        <footer className="mt-auto h-fit">
            <Webline wrapperClassName="bg-background-accent-less">
                <Footer simpleFooter />
            </Webline>
        </footer>
    </div>
);
