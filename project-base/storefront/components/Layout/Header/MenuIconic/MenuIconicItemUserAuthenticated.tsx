import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { TIDs } from 'cypress/tids';
import { useEffect, useId, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import useClickClosePopup from 'utils/ui/useClickClosePopup';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useKeypress } from 'utils/useKeyPress';
import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';

type MenuIconicItemUserAuthenticatedProps = {
    shouldShowLabel?: boolean;
    shouldUseLocalUserMenuState?: boolean;
    userPopoverTopClassName?: string;
};

export const MenuIconicItemUserAuthenticated: FC<MenuIconicItemUserAuthenticatedProps> = ({
    shouldShowLabel = true,
    shouldUseLocalUserMenuState,
    userPopoverTopClassName,
}) => {
    const { t } = useTranslation();
    const [isLocalUserMenuOpen, setIsLocalUserMenuOpen] = useState(false);
    const isGlobalUserMenuOpen = useSessionStore((s) => s.isUserMenuOpen);
    const setIsGlobalUserMenuOpen = useSessionStore((s) => s.setIsUserMenuOpen);
    const isUserMenuOpen = shouldUseLocalUserMenuState ? isLocalUserMenuOpen : isGlobalUserMenuOpen;
    const setIsUserMenuOpen = shouldUseLocalUserMenuState ? setIsLocalUserMenuOpen : setIsGlobalUserMenuOpen;
    const isDesktop = useMediaMin('vl');
    const menuRef = useRef<HTMLDivElement>(null);
    const panelId = useId();
    const closeUserMenu = () => setIsUserMenuOpen(false);

    useClickClosePopup([menuRef], () => {
        if (isDesktop && isUserMenuOpen) {
            closeUserMenu();
        }
    });

    useKeypress('Escape', () => {
        if (isDesktop && isUserMenuOpen) {
            closeUserMenu();
            menuRef.current?.querySelector('button')?.focus({ preventScroll: true });
        }
    });

    useEffect(() => {
        const menu = menuRef.current;
        if (!isDesktop || !isUserMenuOpen || !menu) {
            return undefined;
        }

        const handleFocusOut = (event: FocusEvent) => {
            // Clicking static panel content can focus the surrounding skip-navigation header.
            if (
                event.relatedTarget instanceof Node &&
                !menu.contains(event.relatedTarget) &&
                !event.relatedTarget.contains(menu)
            ) {
                setIsUserMenuOpen(false);
            }
        };

        menu.addEventListener('focusout', handleFocusOut);
        return () => menu.removeEventListener('focusout', handleFocusOut);
    }, [isDesktop, isUserMenuOpen, setIsUserMenuOpen]);

    return (
        <>
            <div className={twJoin('relative', isDesktop && isUserMenuOpen && 'z-aboveOverlay')} ref={menuRef}>
                <MenuIconicItemLink
                    ariaControls={isDesktop && isUserMenuOpen ? panelId : undefined}
                    ariaExpanded={isUserMenuOpen}
                    ariaHaspopup={isDesktop ? undefined : 'dialog'}
                    ariaLabel={t('Show logged in user popup')}
                    className="min-size-12 cursor-pointer text-nowrap focus-visible:outline-2 focus-visible:outline-offset-2"
                    tid={TIDs.my_account_link}
                    title={t('My account')}
                    onClick={(event) => {
                        event.currentTarget.focus({ preventScroll: true });
                        setIsUserMenuOpen(!isUserMenuOpen);
                    }}
                >
                    <div className="relative">
                        <UserIcon className="size-6" />
                        <div className="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-green-400 shadow-sm" />
                    </div>
                    {shouldShowLabel && <span className="hidden lg:inline-block">{t('Account')}</span>}
                </MenuIconicItemLink>

                <Drawer isActive={!isDesktop && isUserMenuOpen} setIsActive={setIsUserMenuOpen} title={t('Account')}>
                    <UserMenu hideFocusTrap onMenuClose={closeUserMenu} />
                </Drawer>

                <MenuIconicItemUserPopover id={panelId} isOpen={isUserMenuOpen} topClassName={userPopoverTopClassName}>
                    <UserMenu hideFocusTrap onMenuClose={closeUserMenu} />
                </MenuIconicItemUserPopover>
            </div>
            {isDesktop && (
                <Overlay shouldDisablePointerEventsOnExit isActive={isUserMenuOpen} onClick={closeUserMenu} />
            )}
        </>
    );
};
