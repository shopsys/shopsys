import { ErrorPageBody } from 'components/Pages/ErrorPage/ErrorPageBody';
import Head from 'next/head';
import { twMergeCustom } from 'utils/twMerge';

/**
 * Minimal, self-contained error page component.
 * No context providers, no translations, no layouts - just basic rendering.
 * This ensures the error page can ALWAYS render, even when the app is completely broken.
 *
 * Uses Tailwind classes (compiled at build time, always available).
 */

type MinimalErrorContentProps = {
    statusCode: number;
    err?: string;
    showDebugInfo?: boolean;
};

const formatErrorForDisplay = (err: string): string => {
    try {
        const parsed = JSON.parse(err);
        return JSON.stringify(parsed, null, 2);
    } catch {
        return err;
    }
};

export const MinimalErrorContent: FC<MinimalErrorContentProps> = ({ statusCode, err, showDebugInfo }) => {
    const is404 = statusCode === 404;
    const formattedError = err ? formatErrorForDisplay(err) : undefined;
    const shouldShowDebugInfo = showDebugInfo && process.env.NODE_ENV !== 'production';

    return (
        <>
            <Head>
                <title>{is404 ? 'Page Not Found' : 'Server Error'}</title>
            </Head>

            <div className="flex min-h-screen items-center justify-center bg-background-default">
                <div className="mx-auto w-full max-w-5xl px-5 py-10 text-center lg:py-20">
                    <ErrorPageBody
                        heading={is404 ? 'This page got lost.' : 'Something went wrong.'}
                        statusCode={statusCode}
                        text={
                            is404
                                ? 'The address may be wrong, but there is still plenty to discover.'
                                : 'Please try again later or contact us.'
                        }
                    >
                        <div className="flex flex-col items-center gap-4 lg:gap-8">
                            <a
                                href="/"
                                className={twMergeCustom(
                                    'inline-flex h-fit w-auto cursor-pointer items-center justify-center gap-2 rounded-button px-3 py-2.5 text-center font-bold font-secondary text-xs outline-2 -outline-offset-2 transition-all sm:px-4 sm:py-2 sm:text-sm',
                                    'bg-button-primary-bg-default text-button-primary-text-default outline-button-primary-border-default',
                                    'hover:bg-button-primary-bg-hovered hover:text-button-primary-text-hovered hover:no-underline hover:outline-button-primary-border-hovered',
                                    'active:bg-button-primary-bg-active active:text-button-primary-text-active active:outline-button-primary-border-active',
                                    'no-underline',
                                )}
                            >
                                Back to Shop
                            </a>

                            {shouldShowDebugInfo && formattedError && (
                                <div
                                    className={twMergeCustom(
                                        'mx-auto w-full max-w-2xl rounded-md border border-border-default bg-background-more p-4 text-left',
                                        'whitespace-pre-wrap break-all font-mono text-text-less text-xs',
                                    )}
                                >
                                    <p className="mb-2 font-semibold text-text-less text-xxs uppercase tracking-widest">
                                        Debug information
                                    </p>
                                    {formattedError}
                                </div>
                            )}
                        </div>
                    </ErrorPageBody>
                </div>
            </div>
        </>
    );
};
