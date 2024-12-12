import { LoginForm } from 'app/_components/Blocks/LoginForm/LoginForm';
import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.ssr';
import { getServerT } from 'utils/getServerTranslation';

export default async function LoginPage() {
    const t = await getServerT();
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Log in'), slug: '' }];

    return (
        <>
            <Breadcrumbs breadcrumbs={breadcrumbs} />

            <Webline>
                <LoginForm formHeading={t('Log in')} />
            </Webline>
        </>
    );
}
