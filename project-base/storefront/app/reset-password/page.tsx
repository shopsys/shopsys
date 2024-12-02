import { Breadcrumbs } from 'app/_components/Breadcrumbs/Breadcrumbs';
import { ResetPasswordForm } from 'app/_components/ResetPasswordForm/ResetPasswordForm';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { getServerT } from 'utils/getServerTranslation';

export default async function ResetPasswordPage() {
    const t = await getServerT();
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Reset password'), slug: '' }];

    return (
        <>
            <Breadcrumbs breadcrumbs={breadcrumbs} />

            <ResetPasswordForm formHeading={t('Reset password')} />
        </>
    );
}
