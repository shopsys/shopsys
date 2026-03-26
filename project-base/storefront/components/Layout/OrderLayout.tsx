import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { Footer } from 'components/Layout/Footer/Footer';
import { AccessibilityNavigation } from 'components/Layout/Header/AccessibilityNavigation/AccessibilityNavigation';
import { Header } from 'components/Layout/Header/Header';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGoPayCheckoutRecovery } from 'components/Pages/Order/PaymentConfirmation/useGoPayCheckoutRecovery';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSessionStore } from 'store/useSessionStore';
import { useOrderPagesAccess } from 'utils/cart/useOrderPagesAccess';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type OrderLayoutProps = {
    page: 'transport-and-payment' | 'contact-information';
    isFetchingData?: boolean;
};

export const OrderLayout: FC<OrderLayoutProps> = ({ children, page, isFetchingData }) => {
    const { t } = useTranslation();
    const domainConfig = useDomainConfig();
    const canContentBeDisplayed = useOrderPagesAccess(page);
    const isPageLoading = useSessionStore((s) => s.isPageLoading);
    const isRecoveringGoPaySession = useGoPayCheckoutRecovery(domainConfig);

    return (
        <>
            <SeoMeta defaultTitle={t('Order')} />

            <div className="flex h-full min-h-screen flex-col">
                <AccessibilityNavigation simpleHeader />

                <NotificationBars />

                <header className="bg-linear-to-tr/srgb from-background-brand to-background-brand-less lg:pb-6">
                    <Header simpleHeader />
                </header>

                <main
                    aria-label={t('Order process main content', { ns: 'accessibility' })}
                    className="mt-4 mb-10 flex flex-col"
                    id="main-content"
                >
                    <SkeletonManager
                        isFetchingData={!canContentBeDisplayed || isFetchingData || isRecoveringGoPaySession}
                        isPageLoading={isPageLoading}
                        pageTypeOverride={page}
                    >
                        <Webline>{children}</Webline>
                    </SkeletonManager>
                </main>

                <footer
                    aria-label={t('Site information', { ns: 'accessibility' })}
                    className="mt-auto h-fit bg-background-accent-less"
                >
                    <Footer simpleFooter />
                </footer>
            </div>
        </>
    );
};
