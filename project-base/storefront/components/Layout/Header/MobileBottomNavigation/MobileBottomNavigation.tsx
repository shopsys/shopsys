import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { HomeIcon } from 'components/Basic/Icon/HomeIcon';
import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { CartCount } from 'components/Layout/Header/Cart/CartCount';
import {
    MobileBottomNavigationButton,
    MobileBottomNavigationLink,
} from 'components/Layout/Header/MobileBottomNavigation/MobileBottomNavigationItem';
import { MobileBottomSearchWithOverlay } from 'components/Layout/Header/MobileBottomNavigation/MobileBottomSearchWithOverlay';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import { flushSync } from 'react-dom';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useKeypress } from 'utils/useKeyPress';

type OpenPanel = 'catalog' | 'cart' | 'search' | 'account' | null;
type Panel = Exclude<OpenPanel, null>;

const MobileBottomAccountDrawer = dynamic(
    () => import('./MobileBottomAccountDrawer').then((component) => component.MobileBottomAccountDrawer),
    { ssr: false },
);

const MobileBottomCartDrawer = dynamic(
    () => import('./MobileBottomCartDrawer').then((component) => component.MobileBottomCartDrawer),
    { ssr: false },
);

const MobileMenu = dynamic(
    () => import('components/Layout/Header/MobileMenu/MobileMenu').then((component) => component.MobileMenu),
    { ssr: false },
);

export const MobileBottomNavigation: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { cart } = useCurrentCart();
    const isUserLoggedIn = useIsUserLoggedIn();
    const searchInputRef = useRef<HTMLInputElement>(null);
    const openedPanelsRef = useRef(new Set<Panel>());
    const [openPanel, setOpenPanel] = useState<OpenPanel>(null);
    const [homeUrl] = getInternationalizedStaticUrls(['/'], url);

    const closeOpenPanel = () => setOpenPanel(null);

    const togglePanel = (panel: Panel) => {
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

        if (shouldOpenPanel) {
            openedPanelsRef.current.add(panel);
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

            {openedPanelsRef.current.has('catalog') && (
                <MobileMenu
                    isMenuOpened={openPanel === 'catalog'}
                    shouldRenderTrigger={false}
                    onMenuToggle={() => togglePanel('catalog')}
                />
            )}

            {openedPanelsRef.current.has('cart') && (
                <MobileBottomCartDrawer
                    isActive={openPanel === 'cart'}
                    setIsActive={(isActive) => setOpenPanel(isActive ? 'cart' : null)}
                    title={t('Cart')}
                />
            )}

            {openPanel === 'search' && (
                <MobileBottomSearchWithOverlay searchInputRef={searchInputRef} onClose={closeOpenPanel} />
            )}

            {openedPanelsRef.current.has('account') && (
                <MobileBottomAccountDrawer
                    isActive={openPanel === 'account'}
                    isUserLoggedIn={isUserLoggedIn}
                    setIsActive={(isActive) => setOpenPanel(isActive ? 'account' : null)}
                    title={t('My account')}
                    onClose={closeOpenPanel}
                />
            )}
        </>
    );
};
