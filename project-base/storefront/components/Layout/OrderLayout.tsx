import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { Footer } from 'components/Layout/Footer/Footer';
import { AccessibilityNavigation } from 'components/Layout/Header/AccessibilityNavigation/AccessibilityNavigation';
import { Header } from 'components/Layout/Header/Header';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';
import { useOrderPagesAccess } from 'utils/cart/useOrderPagesAccess';

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
            <SeoMeta defaultTitle={t('Order')} />

            <div className="flex h-full min-h-screen flex-col">
                <AccessibilityNavigation simpleHeader />

                <NotificationBars />

                <header className="from-background-brand to-background-brand-less bg-linear-to-tr/srgb lg:pb-6">
                    <Header simpleHeader />
                </header>

                <main
                    aria-label={t('Order process main content')}
                    className="mt-4 mb-10 flex flex-col"
                    id="main-content"
                >
                    <SkeletonManager
                        isFetchingData={!canContentBeDisplayed || isFetchingData}
                        isPageLoading={isPageLoading}
                        pageTypeOverride={page}
                    >
                        <Webline>{children}</Webline>
                    </SkeletonManager>
                </main>

                <footer aria-label={t('Site information')} className="bg-background-accent-less mt-auto h-fit">
                    <Footer simpleFooter />
                </footer>
            </div>
        </>
    );
};
