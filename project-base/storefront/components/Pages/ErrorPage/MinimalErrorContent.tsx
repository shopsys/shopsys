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
            <div className="bg-background-body flex min-h-screen items-center justify-center font-sans">
                <div className="max-w-lg p-8 text-center">
                    <div className="mb-4 text-8xl font-bold text-red-500">{statusCode}</div>
                    <h1 className="mb-2 text-2xl font-semibold">{is404 ? 'Page Not Found' : 'Something went wrong'}</h1>
                    <p className="text-text-secondary mb-8">
                        {is404
                            ? "We couldn't find the page you're looking for."
                            : 'Please try again later or contact support.'}
                    </p>
                    <a
                        href="/"
                        className={twMergeCustom(
                            'bg-background-accent text-text-inverted hover:bg-background-accent-less',
                            'inline-block rounded-md px-6 py-3 font-medium',
                        )}
                    >
                        Back to Home
                    </a>
                    {showDebugInfo && formattedError && (
                        <div
                            className={twMergeCustom(
                                'border-border-primary bg-background-main mt-8 rounded-md border p-4 text-left',
                                'font-mono text-xs break-all whitespace-pre-wrap',
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
