import { Footer } from './Footer/Footer';
import { AccessibilityNavigation } from './Header/AccessibilityNavigation/AccessibilityNavigation';
import { Header } from './Header/Header';
import { Webline } from './Webline/Webline';
import useTranslation from 'next-translate/useTranslation';

export const ErrorLayout: FC = ({ children }) => {
    const { t } = useTranslation();

    return (
        <div className="flex h-full min-h-screen flex-col">
            <AccessibilityNavigation />

            <header className="from-background-brand to-background-brand-less bg-linear-to-tr/srgb">
                <Header simpleHeader />
            </header>

            <main aria-label={t('Error page content')} className="mt-4 mb-10 flex flex-col gap-4" id="main-content">
                {children}
            </main>

            <footer aria-label={t('Site information')} className="mt-auto h-fit">
                <Webline wrapperClassName="bg-background-accent-less">
                    <Footer simpleFooter />
                </Webline>
            </footer>
        </div>
    );
};
