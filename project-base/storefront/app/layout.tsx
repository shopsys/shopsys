import { DeferredUserConsent } from './_components/Blocks/UserConsent/DeferredUserConsent';
import { getInternationalizedStaticUrls } from './_utils/getInternationalizedStaticUrls';
import { Footer } from 'app/_components/Layout/Footer/Footer';
import { Header } from 'app/_components/Layout/Header/Header';
import { NotificationBars } from 'app/_components/Layout/NotificationBars/NotificationBars';
import Providers from 'components/providers/Providers';
import { Metadata } from 'next';
import { headers } from 'next/headers';
import 'nprogress/nprogress.css';
import 'react-loading-skeleton/dist/skeleton.css';
import 'react-toastify/dist/ReactToastify.css';
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
};

const RootLayout = async ({ children }: RootLayoutProps) => {
    const pathname = headers().get('x-pathname') ?? '/';
    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/user-consent']);
    const isConsentUpdatePage = consentUpdatePageUrl === pathname;

    return (
        <Providers>
            <NotificationBars />

            <div className="flex min-h-dvh flex-col">
                <Header />

                <main className="mt-4 mb-10 flex flex-1 flex-col gap-4">{children}</main>

                <Footer />

                <DeferredUserConsent isConsentUpdatePage={isConsentUpdatePage} />
            </div>
        </Providers>
    );
};

export default RootLayout;
