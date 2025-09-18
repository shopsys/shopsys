import { DeferredUserConsent } from './_components/Blocks/UserConsent/DeferredUserConsent';
import { GoogleTagManager } from '@next/third-parties/google';
import { Footer } from 'app/_components/Layout/Footer/Footer';
import { Header } from 'app/_components/Layout/Header/Header';
import { NotificationBars } from 'app/_components/Layout/NotificationBars/NotificationBars';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import Providers from 'components/providers/Providers';
import { Metadata } from 'next';
import { headers } from 'next/headers';
import { Suspense } from 'react';
import 'styles/globals.css';

export const generateMetadata = async (): Promise<Metadata> => {
    // TODO: Dynamic metadata generation based on the route (refactor `useSeo`)
    return {
        title: 'Shopsys Platform App Router',
        description: 'Shopsys Platform App Router',
    };
};

type RootLayoutProps = {
    children: React.ReactNode;
    breadcrumbs: React.ReactNode;
};

const RootLayout = async ({ children, breadcrumbs }: RootLayoutProps) => {
    const domainConfig = getDomainConfig((await headers()).get('host')!);
    const { defaultLocale: lang } = domainConfig;

    return (
        <html lang={lang}>
            {domainConfig.gtmId && <GoogleTagManager gtmId={domainConfig.gtmId!} />}
            {/* suppressHydrationWarning for ignoring grammarly extension */}
            <body suppressHydrationWarning>
                <Providers>
                    <NotificationBars />

                    <div className="flex min-h-dvh flex-col">
                        <Header />

                        {breadcrumbs}

                        <main className="mt-4 mb-10 flex flex-1 flex-col gap-4">{children}</main>

                        <Footer />

                        <Suspense fallback={null}>
                            <DeferredUserConsent />
                        </Suspense>
                    </div>
                </Providers>
            </body>
        </html>
    );
};

export default RootLayout;
