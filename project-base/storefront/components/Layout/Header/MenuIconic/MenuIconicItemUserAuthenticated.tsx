import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { TIDs } from 'cypress/tids';
import { useState } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDebounce } from 'utils/useDebounce';
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
    const isActiveDelayed = useDebounce(isUserMenuOpen, 200);
    const isDesktop = useMediaMin('vl');

    return (
        <>
            {/* biome-ignore lint/a11y/useSemanticElements: The wrapper manages hover state for the popover while containing nested navigation controls, so it cannot be replaced with a semantic button. */}
            <div
                aria-expanded={isUserMenuOpen}
                aria-haspopup="menu"
                aria-label={t('Show logged in user popup')}
                data-tid={TIDs.my_account_link}
                role="button"
                tabIndex={0}
                className={twMergeCustom(
                    'group outline-hidden lg:relative lg:flex',
                    isUserMenuOpen && 'z-aboveOverlay',
                )}
                onMouseEnter={() => isDesktop && setIsUserMenuOpen(true)}
                onMouseLeave={() => isDesktop && setIsUserMenuOpen(false)}
                onClick={(e) => {
                    if (!isDesktop && e.target === e.currentTarget) {
                        setIsUserMenuOpen(!isUserMenuOpen);
                    }
                }}
                onKeyDown={(e) => {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        setIsUserMenuOpen(false);

                        return;
                    }

                    if (e.target !== e.currentTarget) {
                        return;
                    }

                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        setIsUserMenuOpen(!isUserMenuOpen);
                    }
                }}
            >
                <MenuIconicItemLink
                    ariaExpanded={isUserMenuOpen}
                    ariaHaspopup="menu"
                    ariaLabel={t('Show logged in user popup')}
                    className="cursor-pointer text-nowrap transition-all group-focus-visible:bg-orange-500 group-focus-visible:text-text-default"
                    tabIndex={-1}
                    title={t('Go to my account page')}
                    onClick={() => !isDesktop && setIsUserMenuOpen(!isUserMenuOpen)}
                    onTouchEnd={(e) => {
                        e.preventDefault();
                        setIsUserMenuOpen(!isUserMenuOpen);
                    }}
                >
                    <div className="relative">
                        <UserIcon className="size-6" />
                        <div className="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-green-400 shadow-sm" />
                    </div>
                    {shouldShowLabel && <span className="hidden lg:inline-block">{t('Account')}</span>}
                </MenuIconicItemLink>

                <Drawer isActive={isUserMenuOpen} setIsActive={setIsUserMenuOpen} title={t('Account')}>
                    <RemoveScroll>
                        <UserMenu onMenuClose={() => setIsUserMenuOpen(false)} />
                    </RemoveScroll>
                </Drawer>

                <MenuIconicItemUserPopover
                    isAuthenticated
                    isHovered={isActiveDelayed}
                    topClassName={userPopoverTopClassName}
                >
                    <UserMenu onMenuClose={() => setIsUserMenuOpen(false)} />
                </MenuIconicItemUserPopover>
            </div>

            {isDesktop && <Overlay isActive={isActiveDelayed} onClick={() => setIsUserMenuOpen(false)} />}
        </>
    );
};
