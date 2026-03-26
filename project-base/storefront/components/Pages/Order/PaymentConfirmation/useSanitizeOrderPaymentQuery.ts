import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getLocalePrefix } from 'utils/domain/domainUtils';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

// orderEmail arrives as a URL param from the backend redirect, exposing PII in the address bar.
// Empty orderUrlHash can also linger from the same redirect. Both are removed in a single router.replace.
export const useSanitizeOrderPaymentQuery = (
    domainConfig: DomainConfigType,
    orderEmail: string | undefined,
    urlHash: string,
): void => {
    const router = useRouter();
    const wasSanitizationAttemptedRef = useRef(false);

    useEffect(() => {
        if (wasSanitizationAttemptedRef.current || !router.isReady) {
            return;
        }

        const hasEmailToRemove = !!orderEmail;
        const hasEmptyHashToRemove = urlHash === '' && router.query.orderUrlHash !== undefined;

        if (!hasEmailToRemove && !hasEmptyHashToRemove) {
            return;
        }

        wasSanitizationAttemptedRef.current = true;

        const [orderPaymentConfirmationUrl] = getInternationalizedStaticUrls(
            ['/order-payment-confirmation'],
            domainConfig.url,
        );
        const localePrefix = getLocalePrefix(domainConfig);
        const sanitizedQuery = { ...router.query };

        if (hasEmailToRemove) {
            delete sanitizedQuery.orderEmail;
        }

        if (hasEmptyHashToRemove || (hasEmailToRemove && sanitizedQuery.orderUrlHash === '')) {
            delete sanitizedQuery.orderUrlHash;
        }

        router.replace(
            {
                pathname: `${localePrefix}${orderPaymentConfirmationUrl}`,
                query: sanitizedQuery,
            },
            undefined,
            { shallow: true },
        );
    }, [domainConfig, orderEmail, router, urlHash]);
};
