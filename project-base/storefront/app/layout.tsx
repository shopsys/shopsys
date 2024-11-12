import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TranslationProvider } from 'components/providers/TranslationProvider';
import { headers } from 'next/headers';
import 'nprogress/nprogress.css';
import 'react-loading-skeleton/dist/skeleton.css';
import 'react-toastify/dist/ReactToastify.css';
import 'styles/globals.css';
import 'styles/user-text.css';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { getDictionary } from 'utils/getDictionary';
import { getServerT } from 'utils/getServerTranslation';

export default async function RootLayout({ children }: { children: React.ReactNode }) {
    const domainConfig = getDomainConfig(headers().get('host')!);
    const { defaultLocale: lang } = domainConfig;
    const dictionary = await getDictionary(lang);
    const t = await getServerT({ defaultLang: lang, defaultDictionary: dictionary });

    return (
        <DomainConfigProvider domainConfig={domainConfig}>
            <TranslationProvider dictionary={dictionary} lang={lang}>
                <html lang={lang}>
                    {/* suppressHydrationWarning for ignoring grammarly extension */}
                    <body suppressHydrationWarning>
                        <nav>Navigation</nav>
                        <div>Language in layout is {lang}</div>
                        {children}
                        <footer className="mt-5">
                            {t('Copyright © {{ currentYear }}, Shopsys s.r.o. All rights reserved.', {
                                currentYear: new Date().getFullYear(),
                            })}
                        </footer>
                    </body>
                </html>
            </TranslationProvider>
        </DomainConfigProvider>
    );
}

export const metadata = {
    title: 'Shopsys Platform App Router',
    description: 'Shopsys Platform App Router',
};
