import { Breadcrumbs } from './Breadcrumbs/Breadcrumbs';
import { Footer } from './Footer/Footer';
import { NewsletterForm } from './Footer/NewsletterForm';
import { Header } from './Header/Header';
import { Navigation } from './Header/Navigation/Navigation';
import { NotificationBars } from './NotificationBars/NotificationBars';
import { Webline } from './Webline/Webline';
import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { CanonicalQueryParameters } from 'helpers/seo/generateCanonicalUrl';
import { FriendlyPagesTypesKeys } from 'types/friendlyUrl';

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
        <SeoMeta canonicalQueryParams={canonicalQueryParams} defaultDescription={description} defaultTitle={title} />

        <NotificationBars />

        <Webline className="relative mb-8" type="colored">
            <Header />
            <Navigation />
        </Webline>

        <Adverts withGapBottom withWebline positionName="header" />

        {!!breadcrumbs && (
            <Webline className="mb-8">
                <Breadcrumbs breadcrumbs={breadcrumbs} type={breadcrumbsType} />
            </Webline>
        )}

        {children}

        <Adverts withGapBottom withGapTop withWebline positionName="footer" />

        <Webline type="light">
            <NewsletterForm />
        </Webline>

        <Webline type="dark">
            <Footer />
        </Webline>
    </>
);
