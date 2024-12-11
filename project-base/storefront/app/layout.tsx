import { LogoutButton } from './_components/LogoutButton/LogoutButton';
import { getIsUserLoggedInQuery } from './_queries/getIsUserLoggedInQuery';
import { createQuery } from './_urql/urql-dto';
import imageLogo from '/public/images/logo.svg';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Navigation } from 'components/Layout/Header/Navigation/Navigation';
import Providers from 'components/providers/Providers';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import {
    NavigationQueryDocument,
    TypeNavigationQuery,
    TypeNavigationQueryVariables,
} from 'graphql/requests/navigation/queries/NavigationQuery.ssr';
import { headers } from 'next/headers';
import 'nprogress/nprogress.css';
import 'react-loading-skeleton/dist/skeleton.css';
import 'styles/globals.css';
import 'styles/user-text.css';
import { twJoin } from 'tailwind-merge';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { getDictionary } from 'utils/getDictionary';
import { getServerT } from 'utils/getServerTranslation';

export default async function RootLayout({ children }: { children: React.ReactNode }) {
    const domainConfig = getDomainConfig(headers().get('host')!);
    const { defaultLocale: lang } = domainConfig;
    const dictionary = await getDictionary(lang);
    const [t, navigationResponse, isUserLoggedIn] = await Promise.all([
        getServerT({ defaultLang: lang, defaultDictionary: dictionary }),
        createQuery<TypeNavigationQuery, TypeNavigationQueryVariables>(NavigationQueryDocument, {}),
        getIsUserLoggedInQuery(),
    ]);
    const { data: navigationData } = navigationResponse;
    const currentYear = new Date().getFullYear();

    return (
        <Providers>
            <nav className="flex bg-gradient-to-tr from-backgroundBrand to-backgroundBrandLess px-4">
                <div className="flex w-fit shrink-0 items-center justify-center">
                    <ExtendedNextLink
                        href="/"
                        className={twJoin(
                            'relative m-0 flex w-fit items-center p-5 pl-0 font-secondary text-sm font-bold vl:text-base',
                            'text-linkInverted no-underline',
                            'hover:text-linkInvertedHovered hover:no-underline group-hover:text-linkInvertedHovered group-hover:no-underline',
                            'active:text-linkInvertedHovered',
                            'disabled:text-linkInvertedDisabled',
                        )}
                    >
                        HP
                    </ExtendedNextLink>
                    <ExtendedNextLink
                        href="/app"
                        className={twJoin(
                            'relative m-0 flex w-fit items-center p-5 pl-0 font-secondary text-sm font-bold vl:text-base',
                            'text-linkInverted no-underline',
                            'hover:text-linkInvertedHovered hover:no-underline group-hover:text-linkInvertedHovered group-hover:no-underline',
                            'active:text-linkInvertedHovered',
                            'disabled:text-linkInvertedDisabled',
                        )}
                    >
                        APP
                    </ExtendedNextLink>
                    {isUserLoggedIn && <LogoutButton />}
                    {!isUserLoggedIn && (
                        <>
                            <ExtendedNextLink
                                href="/login"
                                className={twJoin(
                                    'relative m-0 flex w-fit items-center p-5 pl-0 font-secondary text-sm font-bold vl:text-base',
                                    'text-linkInverted no-underline',
                                    'hover:text-linkInvertedHovered hover:no-underline group-hover:text-linkInvertedHovered group-hover:no-underline',
                                    'active:text-linkInvertedHovered',
                                    'disabled:text-linkInvertedDisabled',
                                )}
                            >
                                {t('Login')}
                            </ExtendedNextLink>
                            <ExtendedNextLink
                                href="/registration"
                                className={twJoin(
                                    'relative m-0 flex w-fit items-center p-5 pl-0 font-secondary text-sm font-bold vl:text-base',
                                    'text-linkInverted no-underline',
                                    'hover:text-linkInvertedHovered hover:no-underline group-hover:text-linkInvertedHovered group-hover:no-underline',
                                    'active:text-linkInvertedHovered',
                                    'disabled:text-linkInvertedDisabled',
                                )}
                            >
                                {t('Registration')}
                            </ExtendedNextLink>
                        </>
                    )}
                    <ExtendedNextLink
                        href="/about"
                        className={twJoin(
                            'relative m-0 flex items-center p-5 pr-10 font-secondary text-sm font-bold vl:text-base',
                            'text-linkInverted no-underline',
                            'hover:text-linkInvertedHovered hover:no-underline group-hover:text-linkInvertedHovered group-hover:no-underline',
                            'active:text-linkInvertedHovered',
                            'disabled:text-linkInvertedDisabled',
                        )}
                    >
                        {t('About Shopsys')}
                    </ExtendedNextLink>
                </div>
                {navigationData && (
                    <Navigation navigation={navigationData.navigation} skeletonType={DEFAULT_SKELETON_TYPE} />
                )}
            </nav>
            <div>Language in layout is {lang}</div>
            {children}
            <div className="flex flex-col items-center text-center">
                <div className="flex items-center text-sm text-textDisabled">
                    {t('Copyright © {{ currentYear }}, Shopsys s.r.o. All rights reserved.', {
                        currentYear,
                    })}
                </div>
                <div className="flex items-center text-sm text-textDisabled">
                    {t('Customized E-shop by')}
                    <a className="ml-2 flex w-20" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                        <Image alt="footer logo" src={imageLogo} />
                    </a>
                </div>
            </div>
        </Providers>
    );
}

export const metadata = {
    title: 'Shopsys Platform App Router',
    description: 'Shopsys Platform App Router',
};
