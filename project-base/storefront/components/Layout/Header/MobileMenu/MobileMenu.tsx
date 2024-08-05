import { MobileMenuContent } from './MobileMenuContent';
import { HamburgerMenu } from 'components/Layout/Header/HamburgerMenu/HamburgerMenu';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';

export type MenuItem = {
    name: string;
    link: string;
    parentItem?: string;
    children?: MenuItem[];
};

export const MobileMenu: FC = () => {
    const [{ data: navigationData }] = useNavigationQuery();
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    useEffect(() => {
        if (isMenuOpened) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }
    }, [isMenuOpened]);

    if (!navigationData?.navigation.length) {
        return null;
    }

    const handleMenuToggle = () => setIsMenuOpened((currentIsMenuOpened) => !currentIsMenuOpened);

    return (
        <>
            <HamburgerMenu onClick={handleMenuToggle} />

            <div
                className={twJoin(
                    'fixed p-8 gap-5 flex flex-col max-h-screen overflow-y-auto inset-0 z-maximum bg-background shadow-md transition-all',
                    isMenuOpened ? 'translate-x-0' : 'translate-x-full',
                )}
            >
                {isMenuOpened && (
                    <MobileMenuContent
                        navigationItems={navigationData.navigation}
                        onMenuToggleHandler={handleMenuToggle}
                    />
                )}
            </div>
        </>
    );
};
