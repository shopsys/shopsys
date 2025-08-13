import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { useLoginViaExchangeToken } from 'utils/auth/useLoginViaExchangeToken';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export const ExchangeTokenHandler: FC = () => {
    const router = useRouter();
    const loginViaExchangeToken = useLoginViaExchangeToken();
    const processedTokens = useRef(new Set<string>());

    useEffect(() => {
        const { exchangeToken } = router.query;

        if (exchangeToken && typeof exchangeToken === 'string' && !processedTokens.current.has(exchangeToken)) {
            // Mark this token as being processed to prevent multiple attempts
            processedTokens.current.add(exchangeToken);

            const handleExchangeToken = async () => {
                try {
                    const result = await loginViaExchangeToken(exchangeToken);

                    if (result.error) {
                        showErrorMessage('Login failed. The token may have expired.');
                        router.replace('/', undefined, { shallow: true });
                    }
                } catch {
                    showErrorMessage('Login failed. Please try again.');
                    router.replace('/', undefined, { shallow: true });
                }
            };

            handleExchangeToken();
        }
    }, [router.query, router.isReady, loginViaExchangeToken]);

    return null;
};
