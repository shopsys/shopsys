'use client';

import { Button } from 'components/Forms/Button/Button';
import { ErrorPage, ErrorPageTextHeading, ErrorPageTextMain } from 'components/Pages/ErrorPage/ErrorPageElements';
import { useTranslation } from 'components/providers/TranslationProvider';
import { useEffect } from 'react';
import { Sentry } from 'utils/sentry';

type GlobalErrorPageProps = {
    error: Error & { digest?: string };
    reset: () => void;
};

const GlobalErrorPage = ({ error, reset }: GlobalErrorPageProps) => {
    const { lang } = useTranslation();
    useEffect(() => {
        // Report all global errors to Sentry as they are critical
        Sentry.captureException(error);
    }, [error]);

    const handleReset = () => {
        // Clear any potential state issues before reset
        if (typeof window !== 'undefined') {
            // Clear localStorage/sessionStorage if needed
            // localStorage.clear();
        }
        reset();
    };

    const handleGoHome = () => {
        window.location.href = '/';
    };

    return (
        <html lang={lang}>
            <body>
                <div className="flex min-h-screen items-center justify-center bg-gray-50">
                    <ErrorPage>
                        <div className="text-center">
                            <ErrorPageTextHeading>Application Error</ErrorPageTextHeading>
                            <ErrorPageTextMain>
                                Something went wrong with the application. Please try refreshing the page.
                            </ErrorPageTextMain>

                            {process.env.NODE_ENV === 'development' && (
                                <details className="mt-4 rounded border bg-red-50 p-4 text-left">
                                    <summary className="cursor-pointer font-medium text-red-800">
                                        Error Details (Development Only)
                                    </summary>
                                    <pre className="mt-2 text-sm whitespace-pre-wrap text-red-700">
                                        {error.message}
                                        {error.stack && `\n\n${error.stack}`}
                                    </pre>
                                </details>
                            )}

                            <div className="mt-6 flex justify-center gap-3">
                                <Button onClick={handleReset}>Try Again</Button>
                                <Button variant="secondary" onClick={handleGoHome}>
                                    Go Home
                                </Button>
                            </div>
                        </div>
                    </ErrorPage>
                </div>
            </body>
        </html>
    );
};

export default GlobalErrorPage;
