import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { Footer } from 'components/Layout/Footer/Footer';
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
                <NotificationBars />

                <header>
                    <Webline
                        className="relative"
                        wrapperClassName="bg-linear-to-tr/srgb from-background-brand to-background-brand-less lg:pb-6"
                    >
                        <Header simpleHeader />
                    </Webline>
                </header>

                <main className="mt-4 mb-10 flex flex-col">
                    <SkeletonManager
                        isFetchingData={!canContentBeDisplayed || isFetchingData}
                        isPageLoading={isPageLoading}
                        pageTypeOverride={page}
                    >
                        <Webline>{children}</Webline>
                    </SkeletonManager>
                </main>

                <footer className="mt-auto h-fit">
                    <Webline wrapperClassName="bg-background-accent-less">
                        <Footer simpleFooter />
                    </Webline>
                </footer>
            </div>
        </>
    );
};
