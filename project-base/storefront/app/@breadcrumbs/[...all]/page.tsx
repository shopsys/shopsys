import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { dynamicBreadcrumbsSettings, staticBreadcrumbsSettings } from 'app/_config/breadcrumbsSettings';
import { getBreadcrumbs } from 'app/_utils/breadcrumbs';
import { getTranslation } from 'app/_utils/translation/getTranslation';

type BreadcrumbsParallelRouteProps = {
    params: {
        all: string[];
    };
};

const BreadcrumbsParallelRoute = async ({ params }: BreadcrumbsParallelRouteProps) => {
    const { all } = params;

    // Temporary check for development, should not be catching `_next` in production
    if (!all.length || all[0].startsWith('_next')) {
        return null;
    }

    const pathname = '/' + all.join('/');
    const t = await getTranslation();

    const breadcrumbs = await getBreadcrumbs(staticBreadcrumbsSettings, dynamicBreadcrumbsSettings, pathname, t);

    if (!breadcrumbs || !breadcrumbs.length) {
        return null;
    }

    // TODO: add gtm
    // const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    // useGtmPageViewEvent(gtmStaticPageViewEvent);

    return <Breadcrumbs breadcrumbs={breadcrumbs} className="mt-4" />;
};

export default BreadcrumbsParallelRoute;
