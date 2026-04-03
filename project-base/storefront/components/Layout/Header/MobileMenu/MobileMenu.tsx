import { Overlay } from 'components/Basic/Overlay/Overlay';
import { HamburgerMenu } from 'components/Layout/Header/HamburgerMenu/HamburgerMenu';
import { AnimatePresence, m } from 'framer-motion';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { MobileMenuContent } from './MobileMenuContent';

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
                        aria-label={t('Mobile navigation menu', { ns: 'accessibility' })}
                        exit={{ translateX: '-100%' }}
                        initial={{ translateX: '-100%' }}
                        role="navigation"
                        transition={{ duration: 0.2, type: 'tween' }}
                        className={twJoin(
                            'pointer-events-auto fixed top-0 left-0 z-maximum h-dvh w-[315px] overflow-y-auto overflow-x-hidden bg-background-default',
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
