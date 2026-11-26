import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const useCustomerBreadcrumbs = (items: TypeBreadcrumbFragment[] = []): TypeBreadcrumbFragment[] => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [dashboardUrl] = getInternationalizedStaticUrls(['/customer'], url);

    return [{ __typename: 'Link', name: t('Account overview'), slug: dashboardUrl }, ...items];
};
