import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { useRouter } from 'next/router';
import { useEffect, useEffectEvent } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ErrorPage } from './ErrorPage';

export const Error503Content: FC = () => {
    const { t } = useTranslation();
    const router = useRouter();

    const onRouteChangeComplete = useEffectEvent(() => {
        router.reload();
    });

    useEffect(() => {
        router.events.on('routeChangeComplete', onRouteChangeComplete);

        return () => {
            router.events.off('routeChangeComplete', onRouteChangeComplete);
        };
    }, [router.events]);

    return (
        <ErrorLayout>
            <ErrorPage
                heading={t('The page is currently under maintenance.')}
                statusCode="503"
                text={t('Please try again later or contact us.')}
            />
        </ErrorLayout>
    );
};
