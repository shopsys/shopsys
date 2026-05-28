import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeHreflangLink } from 'graphql/types';
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
import { NotificationBars } from './NotificationBars/NotificationBars';

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

            <div className="flex h-full min-h-screen flex-col">
                <AccessibilityNavigation />

                <NotificationBars />

                <header className="bg-background-brand" id="site-header" tabIndex={-1}>
                    <Header />
                    <DeferredNavigation />
                </header>

                <main
                    className="mt-4 mb-10 flex flex-col"
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
                    className="mt-auto h-fit"
                >
                    <DeferredNewsletterForm />

                    <DeferredFooter />
                </footer>
            </div>
        </>
    );
};
