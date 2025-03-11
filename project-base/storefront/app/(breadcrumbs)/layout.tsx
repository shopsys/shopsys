import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { getInternationalizedStaticUrl } from 'app/_utils/getInternationalizedStaticUrls';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.ssr';
import { headers } from 'next/headers';
import { Translate } from 'types/translation';

const getBreadcrumbsSetting = (t: Translate): { [key: string]: TypeBreadcrumbFragment[] } => ({
    [getInternationalizedStaticUrl('/user-consent')]: [
        { __typename: 'Link', name: t('User consent'), slug: getInternationalizedStaticUrl('/user-consent') },
    ],
});

type BreadcrumbLayout = {
    children: React.ReactNode;
};

const BreadcrumbLayout = async ({ children }: BreadcrumbLayout) => {
    const pathname = headers().get('x-pathname') ?? '/';
    const t = await getTranslation();
    const breadcrumbsSetting = getBreadcrumbsSetting(t);

    if (!(pathname in breadcrumbsSetting)) {
        return <>{children}</>;
    }

    const breadcrumbs = breadcrumbsSetting[pathname];

    return (
        <>
            <Breadcrumbs breadcrumbs={breadcrumbs} />
            {children}
        </>
    );
};

export default BreadcrumbLayout;
