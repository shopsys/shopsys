import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { Footer } from 'components/Layout/Footer/Footer';
import { AccessibilityNavigation } from 'components/Layout/Header/AccessibilityNavigation/AccessibilityNavigation';
import { Header } from 'components/Layout/Header/Header';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import { FOOTER_THEME, HEADER_THEME } from 'config/theme';
import { useSessionStore } from 'store/useSessionStore';
import { useOrderPagesAccess } from 'utils/cart/useOrderPagesAccess';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type OrderLayoutProps = {
    page: 'transport-and-payment' | 'contact-information';
    isFetchingData?: boolean;
};

export const OrderLayout: FC<OrderLayoutProps> = ({ children, page, isFetchingData }) => {
    const { t } = useTranslation();
    const canContentBeDisplayed = useOrderPagesAccess(page);
    const isPageLoading = useSessionStore((s) => s.isPageLoading);

    return (
        <>
            <SeoMeta defaultMetaRobots="noindex" defaultTitle={t('Order')} />

            <div className="flex h-full min-h-screen flex-col">
                <AccessibilityNavigation simpleHeader />

                <NotificationBars />

                <header
                    data-header-theme={HEADER_THEME}
                    className="bg-header-background focus-visible:outline-hidden"
                    id="site-header"
                    tabIndex={-1}
                >
                    <Header simpleHeader />
                </header>

                <main
                    aria-label={t('Order process main content', { ns: 'accessibility' })}
                    className="mt-4 mb-10 flex flex-col focus-visible:outline-hidden"
                    id="main-content"
                    tabIndex={-1}
                >
                    <SkeletonManager
                        isFetchingData={!canContentBeDisplayed || isFetchingData}
                        isPageLoading={isPageLoading}
                        pageTypeOverride={page}
                    >
                        <Webline width="xl">{children}</Webline>
                    </SkeletonManager>
                </main>

                <footer
                    data-footer-theme={FOOTER_THEME}
                    aria-label={t('Site information', { ns: 'accessibility' })}
                    className="mt-auto h-fit bg-footer-background-order text-footer-foreground"
                >
                    <Footer simpleFooter />
                </footer>
            </div>
        </>
    );
};
