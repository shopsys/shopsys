import { UserConsentForm } from 'components/Blocks/UserConsent/UserConsentForm';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useCallback } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';

type CookieConsentContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const CookieConsentContent: FC<CookieConsentContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const { push } = useRouter();

    const onSetCallback = useCallback(() => {
        showSuccessMessage(t('Your cookie preferences have been set.'));
        push('/');
    }, [push, t]);

    return (
        <SimpleLayout heading={t('Cookie consent')} breadcrumb={breadcrumbs}>
            <UserConsentForm onSetCallback={onSetCallback} />
        </SimpleLayout>
    );
};
