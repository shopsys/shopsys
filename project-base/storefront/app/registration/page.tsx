import { RegistrationForm } from 'app/_components/Blocks/Registration/RegistrationForm';
import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { getCountriesQuery } from 'app/_queries/getCountries';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.ssr';
import { getServerT } from 'utils/getServerTranslation';

export default async function RegistrationPage() {
    const t = await getServerT();
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Registration'), slug: '' }];

    const { data: countriesData } = await getCountriesQuery();

    const mappedCountriesToSelectOptions =
        countriesData?.countries.map((country) => ({
            label: country.name,
            value: country.code,
        })) ?? [];

    return (
        <>
            <Breadcrumbs breadcrumbs={breadcrumbs} />

            <Webline>
                <RegistrationForm
                    countries={mappedCountriesToSelectOptions}
                    formHeading={t('New customer registration')}
                />
            </Webline>
        </>
    );
}
