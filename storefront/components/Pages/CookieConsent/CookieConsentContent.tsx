import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';

type CookieConsentContenteProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const CookieConsentContent: FC<CookieConsentContenteProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();

    return (
        <SimpleLayout heading={t('Cookie consent')} breadcrumb={breadcrumbs}>
            <UserConsentForm />
        </SimpleLayout>
    );
};
