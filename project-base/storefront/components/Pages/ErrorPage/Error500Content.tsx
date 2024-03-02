import { ErrorPage, ErrorPageButtonLink, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { isWithToastAndConsoleErrorDebugging } from 'helpers/errors/isWithErrorDebugging';
import useTranslation from 'next-translate/useTranslation';
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

type Error500ContentProps = {
    err?: string;
};

export const Error500Content: FC<Error500ContentProps> = ({ err }) => {
    const { t } = useTranslation();

    return (
        <ErrorLayout>
            <Webline>
                <ErrorPage isWithoutImage>
                    <ErrorPageTextHeading>{t('Something went wrong.')}</ErrorPageTextHeading>
                    <ErrorPageTextMain>{t('Please try again later or contact us.')}</ErrorPageTextMain>
                    <ErrorPageButtonLink href="/">{t('Back to shop')}</ErrorPageButtonLink>
                </ErrorPage>
                {isWithToastAndConsoleErrorDebugging && err && (
                    <div className="my-3 flex justify-center">
                        <div className="rounded bg-greyLighter">
                            <div className="p-3">
                                <p>{err}</p>
                            </div>
                        </div>
                    </div>
                )}
            </Webline>
        </ErrorLayout>
    );
};
