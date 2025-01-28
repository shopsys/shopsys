import { Footer } from './_components/Layout/Footer/Footer';
import Header from './_components/Layout/Header/Header';
import { Webline } from 'components/Layout/Webline/Webline';
import Providers from 'components/providers/Providers';
import 'nprogress/nprogress.css';
import 'react-loading-skeleton/dist/skeleton.css';
import 'styles/globals.css';
import 'styles/user-text.css';

type RootLayoutProps = {
    children: React.ReactNode;
};

export default function RootLayout({ children }: RootLayoutProps) {
    return (
        <Providers>
            <div className="flex min-h-dvh flex-col">
                <Header />

                <main className="flex-1">{children}</main>

                <Webline wrapperClassName="bg-backgroundAccentLess">
                    <Footer />
                </Webline>
            </div>
        </Providers>
    );
}

export const metadata = {
    title: 'Shopsys Platform App Router',
    description: 'Shopsys Platform App Router',
};
