import { Error500Content } from './Error500Content';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { FallbackProps } from 'react-error-boundary';

export const Error500ContentWithBoundary: FC<FallbackProps> = ({ resetErrorBoundary }) => {
    const router = useRouter();

    useEffect(() => {
        router.events.on('routeChangeComplete', resetErrorBoundary);

        return () => {
            router.events.off('routeChangeComplete', resetErrorBoundary);
        };
    }, [resetErrorBoundary, router.events]);

    return <Error500Content />;
};
