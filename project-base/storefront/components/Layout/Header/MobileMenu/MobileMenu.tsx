import { MobileMenuContent } from './MobileMenuContent';
import { HamburgerMenu } from 'components/Layout/Header/HamburgerMenu/HamburgerMenu';
import { AnimatePresence, m } from 'framer-motion';
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

            <AnimatePresence initial={false}>
                {isMenuOpened && (
                    <m.div
                        animate={{ translateX: '0%' }}
                        exit={{ translateX: '100%' }}
                        initial={{ translateX: '100%' }}
                        transition={{ duration: 0.2, type: 'tween' }}
                        className={twJoin(
                            'fixed inset-0 z-maximum flex max-h-screen flex-col gap-5 overflow-y-auto bg-background p-8 shadow-md',
                        )}
                    >
                        <MobileMenuContent
                            navigationItems={navigationData.navigation}
                            onMenuToggleHandler={handleMenuToggle}
                        />
                    </m.div>
                )}
            </AnimatePresence>
        </>
    );
};
