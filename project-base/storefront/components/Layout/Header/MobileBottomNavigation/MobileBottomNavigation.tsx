import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { HomeIcon } from 'components/Basic/Icon/HomeIcon';
import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { AccountLoginPopup } from 'components/Blocks/Popup/AccountLoginPopup';
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
import { useSessionStore } from 'store/useSessionStore';
import { MobileNavigationPanelEnum } from 'types/mobileNavigation';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useKeypress } from 'utils/useKeyPress';

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
    const updatePortalContent = useSessionStore((store) => store.updatePortalContent);
    const isLoginPopupOpen = useSessionStore((store) => store.portalContent?.type === AccountLoginPopup);
    const searchInputRef = useRef<HTMLInputElement>(null);
    const openedPanelsRef = useRef(new Set<MobileNavigationPanelEnum>());
    const [openPanel, setOpenPanel] = useState<MobileNavigationPanelEnum | null>(null);
    const [homeUrl] = getInternationalizedStaticUrls(['/'], url);

    const closeOpenPanel = () => setOpenPanel(null);

    const togglePanel = (panel: MobileNavigationPanelEnum) => {
        const shouldOpenPanel = openPanel !== panel;

        if (panel === MobileNavigationPanelEnum.Search) {
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
        if (openPanel === MobileNavigationPanelEnum.Search) {
            closeOpenPanel();
        }
    });

    return (
        <>
            <nav
                aria-label={t('Mobile bottom navigation', { ns: 'accessibility' })}
                className="pointer-events-auto relative z-10 vl:hidden bg-background-brand p-1 pb-[calc(0.25rem+env(safe-area-inset-bottom)+1px)] shadow-[0_-8px_24px_rgba(0,0,0,0.35)]"
            >
                <ul className="grid grid-cols-5">
                    <MobileBottomNavigationLink href={homeUrl} icon={HomeIcon} label={t('Home')} />

                    <MobileBottomNavigationButton
                        ariaHaspopup="dialog"
                        icon={UserIcon}
                        isExpanded={isUserLoggedIn ? openPanel === MobileNavigationPanelEnum.Account : isLoginPopupOpen}
                        label={t('Account')}
                        name="account-login"
                        onClick={(event) => {
                            if (isUserLoggedIn) {
                                togglePanel(MobileNavigationPanelEnum.Account);
                                return;
                            }

                            // Finish the drawer focus cleanup before the popup captures its return target.
                            flushSync(closeOpenPanel);
                            // Safari does not focus buttons on pointer clicks; preserve the popup's return target.
                            event.currentTarget.focus({ preventScroll: true });
                            updatePortalContent(
                                <AccountLoginPopup loginFormName="mobile-bottom-navigation-login-form" />,
                            );
                        }}
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
                        isExpanded={openPanel === MobileNavigationPanelEnum.Cart}
                        label={t('Cart')}
                        onClick={() => togglePanel(MobileNavigationPanelEnum.Cart)}
                    >
                        <CartCount>{cart?.items.length ?? 0}</CartCount>
                    </MobileBottomNavigationButton>

                    <MobileBottomNavigationButton
                        icon={SearchIcon}
                        isExpanded={openPanel === MobileNavigationPanelEnum.Search}
                        label={t('Search')}
                        onClick={() => togglePanel(MobileNavigationPanelEnum.Search)}
                    />

                    <MobileBottomNavigationButton
                        icon={MenuIcon}
                        isExpanded={openPanel === MobileNavigationPanelEnum.Catalog}
                        label={t('Menu')}
                        onClick={() => togglePanel(MobileNavigationPanelEnum.Catalog)}
                    />
                </ul>
            </nav>

            {openedPanelsRef.current.has(MobileNavigationPanelEnum.Catalog) && (
                <MobileMenu
                    isMenuOpened={openPanel === MobileNavigationPanelEnum.Catalog}
                    shouldRenderTrigger={false}
                    onMenuToggle={() => togglePanel(MobileNavigationPanelEnum.Catalog)}
                />
            )}

            {openedPanelsRef.current.has(MobileNavigationPanelEnum.Cart) && (
                <MobileBottomCartDrawer
                    isActive={openPanel === MobileNavigationPanelEnum.Cart}
                    setIsActive={(isActive) => setOpenPanel(isActive ? MobileNavigationPanelEnum.Cart : null)}
                    title={t('Cart')}
                />
            )}

            {openPanel === MobileNavigationPanelEnum.Search && (
                <MobileBottomSearchWithOverlay searchInputRef={searchInputRef} onClose={closeOpenPanel} />
            )}

            {isUserLoggedIn && openedPanelsRef.current.has(MobileNavigationPanelEnum.Account) && (
                <MobileBottomAccountDrawer
                    isActive={openPanel === MobileNavigationPanelEnum.Account}
                    setIsActive={(isActive) => setOpenPanel(isActive ? MobileNavigationPanelEnum.Account : null)}
                    title={t('My account')}
                />
            )}
        </>
    );
};
