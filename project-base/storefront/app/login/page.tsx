import { Breadcrumbs } from 'app/_components/Breadcrumbs/Breadcrumbs';
import { LoginForm } from 'app/_components/LoginForm/LoginForm';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.ssr';
import { getServerT } from 'utils/getServerTranslation';

export default async function LoginPage() {
    const t = await getServerT();
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Log in'), slug: '' }];

    return (
        <>
            <Breadcrumbs breadcrumbs={breadcrumbs} />

            <LoginForm formHeading={t('Log in')} />
        </>
    );
}
