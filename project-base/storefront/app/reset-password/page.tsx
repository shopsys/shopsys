import { ResetPasswordForm } from 'app/_components/Blocks/ResetPasswordForm/ResetPasswordForm';
import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';

export default async function ResetPasswordPage() {
    const t = await getTranslation();
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Reset password'), slug: '' }];

    return (
        <>
            <Breadcrumbs breadcrumbs={breadcrumbs} />

            <Webline>
                <ResetPasswordForm formHeading={t('Reset password')} />
            </Webline>
        </>
    );
}
