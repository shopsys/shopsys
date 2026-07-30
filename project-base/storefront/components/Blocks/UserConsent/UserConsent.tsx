import { useRouter } from 'next/router';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { UserConsentForm } from './UserConsentForm';
import { UserConsentPolicyLink } from './UserConsentPolicyLink';

export const UserConsent: FC<{ url: string }> = ({ url }) => {
    const { t } = useTranslation();
    const [isUserConsentVisible, setUserConsentVisibility] = useState(true);
    const userConsent = usePersistStore((store) => store.userConsent);
    const router = useRouter();
    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/user-consent'], url);
    const isConsentUpdatePage = router.asPath === consentUpdatePageUrl;

    const onSetCallback = () => {
        if (userConsent) {
            setUserConsentVisibility(false);
        }
    };

    if (userConsent || isConsentUpdatePage || !isUserConsentVisible) {
        return null;
    }

    return (
        <aside
            aria-label={t('User consent')}
            className="fixed right-3 bottom-[calc(0.75rem+env(safe-area-inset-bottom))] left-3 z-maximum flex max-h-[calc(100dvh-1.5rem)] origin-bottom-right translate-y-0 scale-100 flex-col gap-4 overflow-y-auto rounded-2xl border border-border-less bg-background-default p-4 opacity-100 shadow-[0_24px_64px_-28px_rgba(37,40,61,0.45),0_8px_24px_-16px_rgba(37,40,61,0.28)] motion-safe:starting:translate-y-3 motion-safe:starting:scale-[0.98] motion-safe:starting:opacity-0 motion-safe:transition-[opacity,translate,scale] motion-safe:duration-200 motion-safe:ease-out sm:right-5 sm:bottom-5 sm:left-auto sm:w-110 sm:p-5"
        >
            <div className="flex flex-col gap-1.5">
                <h2 className="mb-0 font-secondary font-semibold text-lg leading-tight">{t('User consent')}</h2>

                <p className="mb-0 text-sm text-text-less">
                    <UserConsentPolicyLink className="text-sm" />
                </p>
            </div>

            <UserConsentForm layout="compact" onSetCallback={onSetCallback} />
        </aside>
    );
};
