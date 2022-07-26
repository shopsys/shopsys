import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import SimpleLayout from 'components/Layout/SimpleLayout';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';

type CookieConsentPageProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const CookieConsentPage: FC<CookieConsentPageProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();

    return (
        <SimpleLayout heading={t('Cookie consent')} breadcrumb={breadcrumbs}>
            <UserConsentForm />
        </SimpleLayout>
    );
};
