import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { TIDs } from 'cypress/tids';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDebounce } from 'utils/useDebounce';
import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';

export const MenuIconicItemUserAuthenticated: FC = () => {
    const { t } = useTranslation();
    const isUserMenuOpen = useSessionStore((s) => s.isUserMenuOpen);
    const setIsUserMenuOpen = useSessionStore((s) => s.setIsUserMenuOpen);
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
                    if (isDesktop) {
                        setIsUserMenuOpen(true);
                    } else if (e.target === e.currentTarget) {
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
                        <div className="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-button-primary-bg-default" />
                    </div>
                    <span className="hidden lg:inline-block">{t('My account')}</span>
                </MenuIconicItemLink>

                <Drawer isActive={isUserMenuOpen} setIsActive={setIsUserMenuOpen} title={t('My account')}>
                    <UserMenu />
                </Drawer>

                <MenuIconicItemUserPopover isAuthenticated isHovered={isActiveDelayed}>
                    <UserMenu />
                </MenuIconicItemUserPopover>
            </div>

            <Overlay isActive={isActiveDelayed} onClick={() => setIsUserMenuOpen(false)} />
        </>
    );
};
