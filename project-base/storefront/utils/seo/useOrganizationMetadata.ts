import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';

export const useOrganizationMetadata = () => {
    const { url } = useDomainConfig();
    const [{ data }] = useSettingsQuery();
    const organization = data?.settings?.seo.organization;
    const address = {
        streetAddress: organization?.streetAddress || undefined,
        addressLocality: organization?.addressLocality || undefined,
        postalCode: organization?.postalCode || undefined,
        addressCountry: organization?.addressCountry || undefined,
    };

    return {
        '@type': 'Organization',
        url,
        name: organization?.name || undefined,
        vatID: organization?.vatId || undefined,
        identifier: organization?.companyNumber
            ? { '@type': 'PropertyValue', propertyID: 'IČO', value: organization.companyNumber }
            : undefined,
        description: organization?.description || undefined,
        address: Object.values(address).some(Boolean) ? { '@type': 'PostalAddress', ...address } : undefined,
        logo: organization?.logo || undefined,
        sameAs: organization?.sameAs.length ? organization.sameAs : undefined,
    };
};
