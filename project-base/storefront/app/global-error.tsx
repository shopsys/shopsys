'use client';

export default function ErrorPage({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
    return (
        <html>
            <body>
                <div className="flex items-center justify-center">
                    <h2>Something went wrong!</h2>
                    <p>{error.message}</p>
                    <button onClick={() => reset()}>Try again</button>
                </div>
            </body>
        </html>
    );
}
