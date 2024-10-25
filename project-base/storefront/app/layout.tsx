import type { Metadata } from 'next';
import { ReactNode } from 'react';

export const metadata: Metadata = {
    title: 'Home',
    description: 'Welcome to Next.js',
};

export default function RootLayout({ children }: { children: ReactNode }) {
    console.log('🐳 server');
    return (
        <html lang="en">
            {/* suppressHydrationWarning for ignoring grammarly extension */}
            <body suppressHydrationWarning>
                nav
                {children}
                footer
            </body>
        </html>
    );
}
