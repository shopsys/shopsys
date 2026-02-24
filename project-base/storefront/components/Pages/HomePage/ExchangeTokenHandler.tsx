import { useRouter } from 'next/router';
import { useEffect, useEffectEvent, useRef } from 'react';
import { useLoginViaExchangeToken } from 'utils/auth/useLoginViaExchangeToken';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export const ExchangeTokenHandler: FC = () => {
    const router = useRouter();
    const loginViaExchangeToken = useLoginViaExchangeToken();
    const processedTokens = useRef(new Set<string>());

    const onExchangeToken = useEffectEvent(async (token: string) => {
        try {
            const result = await loginViaExchangeToken(token);

            if (result.error) {
                showErrorMessage('Login failed. The token may have expired.');
                router.replace('/', undefined, { shallow: true });
            }
        } catch {
            showErrorMessage('Login failed. Please try again.');
            router.replace('/', undefined, { shallow: true });
        }
    });

    useEffect(() => {
        const { exchangeToken } = router.query;

        if (exchangeToken && typeof exchangeToken === 'string' && !processedTokens.current.has(exchangeToken)) {
            processedTokens.current.add(exchangeToken);
            onExchangeToken(exchangeToken);
        }
    }, [router.query, router.isReady]);

    return null;
};
