import { FOOTER_THEME, HEADER_THEME } from 'config/theme';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { Footer } from './Footer/Footer';
import { AccessibilityNavigation } from './Header/AccessibilityNavigation/AccessibilityNavigation';
import { Header } from './Header/Header';

export const ErrorLayout: FC = ({ children }) => {
    const { t } = useTranslation();

    return (
        <div className="flex h-full min-h-screen flex-col">
            <AccessibilityNavigation />

            <header data-header-theme={HEADER_THEME} className="bg-header-background">
                <Header simpleHeader />
            </header>

            <main
                aria-label={t('Error page content', { ns: 'accessibility' })}
                className="mt-4 mb-10 flex flex-col gap-4 focus-visible:outline-hidden"
                id="main-content"
                tabIndex={-1}
            >
                {children}
            </main>

            <footer
                data-footer-theme={FOOTER_THEME}
                aria-label={t('Site information', { ns: 'accessibility' })}
                className="mt-auto h-fit bg-footer-background-accent-less text-footer-foreground"
            >
                <Footer simpleFooter />
            </footer>
        </div>
    );
};
