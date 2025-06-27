import { MobileMenuContent } from './MobileMenuContent';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { HamburgerMenu } from 'components/Layout/Header/HamburgerMenu/HamburgerMenu';
import { AnimatePresence, m } from 'framer-motion';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';

export type MenuItem = {
    name: string;
    link: string;
    parentItem?: string;
    children?: MenuItem[];
};

export const MobileMenu: FC = () => {
    const { t } = useTranslation();
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
                        aria-label={t('Mobile navigation menu')}
                        exit={{ translateX: '-100%' }}
                        initial={{ translateX: '-100%' }}
                        role="navigation"
                        transition={{ duration: 0.2, type: 'tween' }}
                        className={twJoin(
                            'z-maximum bg-background-default pointer-events-auto fixed top-0 left-0 h-dvh w-[315px] overflow-x-hidden overflow-y-auto',
                        )}
                    >
                        <MobileMenuContent
                            navigationItems={navigationData.navigation}
                            onMenuToggleHandler={handleMenuToggle}
                        />
                    </m.div>
                )}
            </AnimatePresence>

            {isMenuOpened && <Overlay isActive={isMenuOpened} onClick={handleMenuToggle} />}
        </>
    );
};
