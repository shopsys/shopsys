import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { TypeHreflangLink } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { OgTypeEnum } from 'types/seo';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { CanonicalQueryParameters } from 'utils/seo/generateCanonicalUrl';
import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { DeferredFooter } from './Footer/DeferredFooter';
import { DeferredNewsletterForm } from './Footer/NewsletterForm/DeferredNewsletterForm';
import { AccessibilityNavigation } from './Header/AccessibilityNavigation/AccessibilityNavigation';
import { Header } from './Header/Header';
import { DeferredNavigation } from './Header/Navigation/DeferredNavigation';
import { useDesktopFixedHeader } from './hooks/useDesktopFixedHeader';
import { NotificationBars } from './NotificationBars/NotificationBars';

const FixedHeader = dynamic(() => import('./Header/FixedHeader').then((component) => component.FixedHeader), {
    ssr: false,
});

const getCurrentHashTarget = (): HTMLElement | null => {
    const hash = window.location.hash.slice(1);

    if (!hash) {
        return null;
    }

    try {
        return document.getElementById(decodeURIComponent(hash));
    } catch {
        return document.getElementById(hash);
    }
};

export type CommonLayoutProps = {
    title?: string | null;
    description?: string | null;
    breadcrumbs?: TypeBreadcrumbFragment[];
    breadcrumbsType?: FriendlyPagesTypesKey;
    canonicalQueryParams?: CanonicalQueryParameters;
    hreflangLinks?: TypeHreflangLink[];
    isFetchingData?: boolean;
    pageTypeOverride?: PageType;
    ogType?: OgTypeEnum | undefined;
    ogImageUrlDefault?: string | undefined;
};

export const CommonLayout: FC<CommonLayoutProps> = ({
    children,
    description,
    title,
    breadcrumbs,
    breadcrumbsType,
    canonicalQueryParams,
    hreflangLinks,
    isFetchingData,
    pageTypeOverride,
    ogType,
    ogImageUrlDefault,
}) => {
    const { t } = useTranslation();
    const isPageLoading = useSessionStore((s) => s.isPageLoading);
    const setIsUserMenuOpen = useSessionStore((s) => s.setIsUserMenuOpen);
    const router = useRouter();
    const siteHeaderRef = useRef<HTMLElement>(null);
    const correctedHashUrlRef = useRef<string | null>(null);
    const { fixedHeaderHeight, fixedHeaderRef, isDesktop, isFixedHeaderVisible, showDesktopFixedHeader } =
        useDesktopFixedHeader(siteHeaderRef);
    const [{ data: navigationData, fetching: isNavigationFetching }] = useNavigationQuery();
    const isOriginalHeaderHidden = isDesktop && showDesktopFixedHeader;

    useEffect(() => {
        if (showDesktopFixedHeader) {
            setIsUserMenuOpen(false);
        }
    }, [setIsUserMenuOpen, showDesktopFixedHeader]);

    useEffect(() => {
        if (!isDesktop || fixedHeaderHeight === 0 || isPageLoading || isFetchingData) {
            return undefined;
        }

        const currentUrl = `${window.location.pathname}${window.location.search}${window.location.hash}`;
        const hashTarget = getCurrentHashTarget();

        if (!hashTarget) {
            correctedHashUrlRef.current = null;

            return undefined;
        }

        if (correctedHashUrlRef.current === currentUrl) {
            return undefined;
        }

        correctedHashUrlRef.current = currentUrl;
        const animationFrameId = window.requestAnimationFrame(() => hashTarget.scrollIntoView({ block: 'start' }));

        return () => window.cancelAnimationFrame(animationFrameId);
    }, [fixedHeaderHeight, isDesktop, isFetchingData, isPageLoading, router.asPath]);

    return (
        <>
            <SeoMeta
                canonicalQueryParams={canonicalQueryParams}
                defaultDescription={description}
                defaultHreflangLinks={hreflangLinks}
                defaultTitle={title}
                ogImageUrlDefault={ogImageUrlDefault}
                ogType={ogType}
            />

            <div className="flex h-full min-h-screen flex-col pb-[calc(4rem+env(safe-area-inset-bottom))] vl:pb-0">
                <AccessibilityNavigation />

                <NotificationBars />

                {isDesktop && (
                    <FixedHeader
                        fixedHeaderRef={fixedHeaderRef}
                        isVisible={isFixedHeaderVisible}
                        navigation={navigationData?.navigation}
                    />
                )}

                <header
                    aria-hidden={isOriginalHeaderHidden || undefined}
                    className="bg-background-brand focus-visible:outline-hidden"
                    id="site-header"
                    inert={isOriginalHeaderHidden || undefined}
                    ref={siteHeaderRef}
                    tabIndex={-1}
                >
                    <Header />
                    <DeferredNavigation
                        isDesktop={isDesktop}
                        isNavigationFetching={isNavigationFetching}
                        navigation={navigationData?.navigation}
                    />
                </header>

                <main
                    className="mt-4 mb-10 flex scroll-mt-fixed-header flex-col focus-visible:outline-hidden"
                    id="main-content"
                    tabIndex={-1}
                    aria-label={
                        title
                            ? t('Main content: {{pageTitle}}', { ns: 'accessibility', pageTitle: title })
                            : t('Main content', { ns: 'accessibility' })
                    }
                >
                    <Adverts withWebline className="mb-4" positionName="header" />

                    {!!breadcrumbs && !isPageLoading && !isFetchingData && (
                        <Breadcrumbs breadcrumbs={breadcrumbs} type={breadcrumbsType} />
                    )}

                    <SkeletonManager
                        isFetchingData={isFetchingData}
                        isPageLoading={isPageLoading}
                        pageTypeOverride={pageTypeOverride}
                    >
                        {children}
                    </SkeletonManager>

                    <Adverts withWebline className="mt-10" positionName="footer" />
                </main>

                <footer
                    aria-label={t('Site information and navigation', { ns: 'accessibility' })}
                    className="mt-auto h-fit bg-secondary-50"
                >
                    <DeferredNewsletterForm />

                    <DeferredFooter />
                </footer>
            </div>
        </>
    );
};
