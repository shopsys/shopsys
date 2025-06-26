import { DeferredUserConsent } from './_components/Blocks/UserConsent/DeferredUserConsent';
import { Footer } from 'app/_components/Layout/Footer/Footer';
import { Header } from 'app/_components/Layout/Header/Header';
import { NotificationBars } from 'app/_components/Layout/NotificationBars/NotificationBars';
import Providers from 'components/providers/Providers';
import { Metadata } from 'next';
import 'nprogress/nprogress.css';
import { Suspense } from 'react';
import 'styles/theme.css';

type MetadataProps = {
    params: Promise<{ id: string }>;
    searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
};

export const generateMetadata = async (props: MetadataProps): Promise<Metadata> => {
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
    return (
        <Providers>
            <NotificationBars />

            <div className="flex min-h-dvh flex-col">
                <Header />

                {breadcrumbs}

                <main className="mt-4 mb-10 flex flex-1 flex-col gap-4">
                    <Suspense fallback={<div>Layout loading...</div>}>{children}</Suspense>
                </main>

                <Footer />

                <Suspense fallback={null}>
                    <DeferredUserConsent />
                </Suspense>
            </div>
        </Providers>
    );
};

export default RootLayout;
