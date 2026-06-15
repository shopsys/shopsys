import { Drawer } from 'components/Basic/Drawer/Drawer';
import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { HomeIcon } from 'components/Basic/Icon/HomeIcon';
import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { CartCount } from 'components/Layout/Header/Cart/CartCount';
import { CartInHeaderList } from 'components/Layout/Header/Cart/CartInHeaderList';
import { MenuIconicItemUserUnauthenticatedContent } from 'components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticatedContent';
import {
    MobileBottomNavigationButton,
    MobileBottomNavigationLink,
} from 'components/Layout/Header/MobileBottomNavigation/MobileBottomNavigationItem';
import { MobileBottomSearchWithOverlay } from 'components/Layout/Header/MobileBottomNavigation/MobileBottomSearchWithOverlay';
import { MobileMenu } from 'components/Layout/Header/MobileMenu/MobileMenu';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRef, useState } from 'react';
import { flushSync } from 'react-dom';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useKeypress } from 'utils/useKeyPress';
import { useVisualViewportBottomOffset } from './useVisualViewportBottomOffset';

type OpenPanel = 'catalog' | 'cart' | 'search' | 'account' | null;

export const MobileBottomNavigation: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { cart } = useCurrentCart();
    const isUserLoggedIn = useIsUserLoggedIn();
    const searchInputRef = useRef<HTMLInputElement>(null);
    const [openPanel, setOpenPanel] = useState<OpenPanel>(null);
    const visualViewportBottomOffset = useVisualViewportBottomOffset();
    const [homeUrl] = getInternationalizedStaticUrls(['/'], url);

    const closeOpenPanel = () => setOpenPanel(null);

    const togglePanel = (panel: Exclude<OpenPanel, null>) => {
        const shouldOpenPanel = openPanel !== panel;

        if (panel === 'search') {
            flushSync(() => {
                setOpenPanel(shouldOpenPanel ? panel : null);
            });

            if (shouldOpenPanel) {
                searchInputRef.current?.focus({ preventScroll: true });
            }

            return;
        }

        setOpenPanel(shouldOpenPanel ? panel : null);
    };

    useKeypress('Escape', () => {
        if (openPanel === 'search') {
            closeOpenPanel();
        }
    });

    return (
        <>
            <nav
                aria-label={t('Mobile bottom navigation', { ns: 'accessibility' })}
                className="fixed right-0 -bottom-px left-0 z-overlay vl:hidden bg-brand-700 p-1 pb-[calc(0.25rem+env(safe-area-inset-bottom)+1px)]"
                style={visualViewportBottomOffset ? { bottom: visualViewportBottomOffset - 1 } : undefined}
            >
                <ul className="grid grid-cols-5">
                    <MobileBottomNavigationLink href={homeUrl} icon={HomeIcon} label={t('Home')} />

                    <MobileBottomNavigationButton
                        icon={UserIcon}
                        isExpanded={openPanel === 'account'}
                        label={isUserLoggedIn ? t('My account') : t('Account')}
                        onClick={() => togglePanel('account')}
                    >
                        {isUserLoggedIn && (
                            <span
                                aria-hidden="true"
                                className="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-green-400 shadow-sm"
                            />
                        )}
                    </MobileBottomNavigationButton>

                    <MobileBottomNavigationButton
                        icon={CartIcon}
                        isExpanded={openPanel === 'cart'}
                        label={t('Cart')}
                        onClick={() => togglePanel('cart')}
                    >
                        <CartCount>{cart?.items.length ?? 0}</CartCount>
                    </MobileBottomNavigationButton>

                    <MobileBottomNavigationButton
                        icon={SearchIcon}
                        isExpanded={openPanel === 'search'}
                        label={t('Search')}
                        onClick={() => togglePanel('search')}
                    />

                    <MobileBottomNavigationButton
                        icon={MenuIcon}
                        isExpanded={openPanel === 'catalog'}
                        label={t('Menu')}
                        onClick={() => togglePanel('catalog')}
                    />
                </ul>
            </nav>

            <MobileMenu
                isMenuOpened={openPanel === 'catalog'}
                shouldRenderTrigger={false}
                onMenuToggle={() => togglePanel('catalog')}
            />

            <Drawer
                isActive={openPanel === 'cart'}
                setIsActive={(isActive) => setOpenPanel(isActive ? 'cart' : null)}
                title={t('Cart')}
            >
                <CartInHeaderList />
            </Drawer>

            {openPanel === 'search' && (
                <MobileBottomSearchWithOverlay searchInputRef={searchInputRef} onClose={closeOpenPanel} />
            )}

            <Drawer
                isActive={openPanel === 'account'}
                setIsActive={(isActive) => setOpenPanel(isActive ? 'account' : null)}
                title={t('My account')}
            >
                {isUserLoggedIn ? (
                    <UserMenu />
                ) : (
                    <MenuIconicItemUserUnauthenticatedContent
                        loginFormName="mobile-bottom-navigation-login-form"
                        onMenuClose={closeOpenPanel}
                    />
                )}
            </Drawer>
        </>
    );
};
