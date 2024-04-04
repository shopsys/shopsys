import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { Footer } from './Footer/Footer';
import { NewsletterForm } from './Footer/NewsletterForm';
import { Header } from './Header/Header';
import { Navigation } from './Header/Navigation/Navigation';
import { NotificationBars } from './NotificationBars/NotificationBars';
import { Webline } from './Webline/Webline';
import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { HreflangLink } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { CanonicalQueryParameters } from 'utils/seo/generateCanonicalUrl';

type CommonLayoutProps = {
    title?: string | null;
    description?: string | null;
    breadcrumbs?: BreadcrumbFragment[];
    breadcrumbsType?: FriendlyPagesTypesKey;
    canonicalQueryParams?: CanonicalQueryParameters;
    hreflangLinks?: HreflangLink[];
    isFetchingData?: boolean;
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
}) => {
    const isPageLoading = useSessionStore((s) => s.isPageLoading);

    return (
        <>
            <SeoMeta
                canonicalQueryParams={canonicalQueryParams}
                defaultDescription={description}
                defaultHreflangLinks={hreflangLinks}
                defaultTitle={title}
            />

            <NotificationBars />

            <Webline className="relative mb-8" type="colored">
                <Header />
                <Navigation />
            </Webline>

            <Adverts withGapBottom withWebline positionName="header" />

            {!!breadcrumbs && !isPageLoading && !isFetchingData && (
                <Webline className="mb-8">
                    <Breadcrumbs breadcrumbs={breadcrumbs} type={breadcrumbsType} />
                </Webline>
            )}

            <SkeletonManager isFetchingData={isFetchingData} isPageLoading={isPageLoading}>
                {children}
            </SkeletonManager>

            <Adverts withGapBottom withGapTop withWebline positionName="footer" />

            <Webline type="light">
                <NewsletterForm />
            </Webline>

            <Webline type="dark">
                <Footer />
            </Webline>
        </>
    );
};
