import { RegistrationForm } from 'app/_components/Blocks/Registration/RegistrationForm';
import { getCountriesQuery } from 'app/_queries/getCountriesQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Webline } from 'components/Layout/Webline/Webline';

const RegistrationPage = async () => {
    const t = await getTranslation();

    const { data: countriesData } = await getCountriesQuery();

    const mappedCountriesToSelectOptions =
        countriesData?.countries.map((country) => ({
            label: country.name,
            value: country.code,
        })) ?? [];

    return (
        <Webline>
            <RegistrationForm countries={mappedCountriesToSelectOptions} formHeading={t('New customer registration')} />
        </Webline>
    );
};

export default RegistrationPage;
