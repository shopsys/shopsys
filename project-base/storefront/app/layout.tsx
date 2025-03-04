import { Footer } from 'app/_components/Layout/Footer/Footer';
import { Header } from 'app/_components/Layout/Header/Header';
import { NotificationBars } from 'app/_components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import Providers from 'components/providers/Providers';
import { Metadata } from 'next';
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
    return (
        <Providers>
            <NotificationBars />

            <div className="flex min-h-dvh flex-col">
                <Header />

                <main className="flex-1">{children}</main>

                <Webline wrapperClassName="bg-backgroundAccentLess">
                    {/* <DeferredNewsletterForm /> */}
                    <Footer />
                </Webline>
            </div>
        </Providers>
    );
};

export default RootLayout;
