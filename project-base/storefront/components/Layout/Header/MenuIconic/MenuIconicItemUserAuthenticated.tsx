import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDebounce } from 'utils/useDebounce';

export const MenuIconicItemUserAuthenticated: FC = () => {
    const { t } = useTranslation();
    const isUserMenuOpen = useSessionStore((s) => s.isUserMenuOpen);
    const setIsUserMenuOpen = useSessionStore((s) => s.setIsUserMenuOpen);
    const isActiveDelayed = useDebounce(isUserMenuOpen, 200);
    const isDesktop = useMediaMin('vl');

    return (
        <>
            <div
                aria-expanded={isUserMenuOpen}
                aria-haspopup="menu"
                aria-label={t('Show logged in user poup')}
                className={twMergeCustom('group outline-none lg:relative lg:flex', isUserMenuOpen && 'z-aboveOverlay')}
                data-tid={TIDs.my_account_link}
                role="button"
                tabIndex={0}
                onMouseEnter={() => isDesktop && setIsUserMenuOpen(true)}
                onMouseLeave={() => isDesktop && setIsUserMenuOpen(false)}
                onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                        setIsUserMenuOpen(!isUserMenuOpen);
                    }

                    if (e.key === 'Escape') {
                        setIsUserMenuOpen(false);
                    }
                }}
            >
                <MenuIconicItemLink
                    className="group-focus-visible:text-text-default cursor-pointer text-nowrap transition-all group-focus-visible:bg-orange-500"
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
                        <div className="bg-button-primary-bg-default absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full" />
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
