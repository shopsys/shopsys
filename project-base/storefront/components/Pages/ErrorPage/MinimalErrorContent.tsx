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

    return (
        <>
            <Head>
                <title>{is404 ? 'Page Not Found' : 'Error'}</title>
            </Head>
            <div className="flex min-h-screen items-center justify-center bg-background-body font-sans">
                <div className="max-w-lg p-8 text-center">
                    <div className="mb-4 font-bold text-8xl text-red-500">{statusCode}</div>
                    <h1 className="mb-2 font-semibold text-2xl">{is404 ? 'Page Not Found' : 'Something went wrong'}</h1>
                    <p className="mb-8 text-text-secondary">
                        {is404
                            ? "We couldn't find the page you're looking for."
                            : 'Please try again later or contact support.'}
                    </p>
                    <a
                        href="/"
                        className={twMergeCustom(
                            'bg-background-accent text-text-inverted hover:bg-background-accent-less',
                            'inline-block rounded-md px-6 py-3 font-semibold',
                        )}
                    >
                        Back to Home
                    </a>
                    {showDebugInfo && formattedError && (
                        <div
                            className={twMergeCustom(
                                'mt-8 rounded-md border border-border-primary bg-background-main p-4 text-left',
                                'whitespace-pre-wrap break-all font-mono text-xs',
                            )}
                        >
                            {formattedError}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
};
