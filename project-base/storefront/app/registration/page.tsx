import { RegistrationForm } from 'app/_components/Blocks/Registration/RegistrationForm';
import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { getCountriesQuery } from 'app/_queries/getCountries';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.ssr';

const RegistrationPage = async () => {
    const t = await getTranslation();
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
};

export default RegistrationPage;
