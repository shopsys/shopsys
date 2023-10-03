import { BreadcrumbFragmentApi } from 'graphql/generated';
import { Footer } from './Footer/Footer';
import { NewsletterForm } from './Footer/NewsletterForm';
import { Header } from './Header/Header';
import { Navigation } from './Header/Navigation/Navigation';
import { NotificationBars } from './NotificationBars/NotificationBars';
import { Webline } from './Webline/Webline';
import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';
import { CanonicalQueryParameters } from 'helpers/seo/generateCanonicalUrl';

type CommonLayoutProps = {
    title?: string | null;
    description?: string | null;
    breadcrumbs?: BreadcrumbFragmentApi[];
    breadcrumbsType?: FriendlyPagesTypesKeys;
    canonicalQueryParams?: CanonicalQueryParameters;
};

export const CommonLayout: FC<CommonLayoutProps> = ({
    children,
    description,
    title,
    breadcrumbs,
    breadcrumbsType,
    canonicalQueryParams,
}) => (
    <>
        <SeoMeta defaultTitle={title} defaultDescription={description} canonicalQueryParams={canonicalQueryParams} />

        <NotificationBars />

        <Webline type="colored" className="relative mb-8">
            <Header />
            <Navigation />
        </Webline>

        <Adverts positionName="header" withGapBottom withWebline />

        {!!breadcrumbs && (
            <Webline className="mb-8">
                <Breadcrumbs breadcrumbs={breadcrumbs} type={breadcrumbsType} />
            </Webline>
        )}

        {children}

        <Adverts positionName="footer" withGapBottom withGapTop withWebline />

        <Webline type="light">
            <NewsletterForm />
        </Webline>

        <Webline type="dark">
            <Footer />
        </Webline>
    </>
);
