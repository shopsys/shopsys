import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { TIDs } from 'cypress/tids';
import { MouseEvent as ReactMouseEvent, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDebounce } from 'utils/useDebounce';
import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';
import { MenuIconicItemUserUnauthenticatedContent } from './MenuIconicItemUserUnauthenticatedContent';

const isBrowserPasswordManagerHovered = (e: ReactMouseEvent<HTMLDivElement, MouseEvent>) => e.relatedTarget === window;

export const MenuIconicItemUserUnauthenticated: FC = () => {
    const { t } = useTranslation();
    const [isActive, setIsActive] = useState(false);
    const isActiveDelayed = useDebounce(isActive, 200);
    const isDesktop = useMediaMin('vl');

    return (
        <>
            {/* biome-ignore lint/a11y/useSemanticElements: The wrapper manages hover state for the popover while containing nested navigation controls, so it cannot be replaced with a semantic button. */}
            <div
                aria-expanded={isActive}
                aria-haspopup="menu"
                aria-label={t('Show registration and login popup', { ns: 'accessibility' })}
                data-tid={TIDs.my_account_link}
                role="button"
                tabIndex={0}
                className={twMergeCustom(
                    'group min-size-12 rounded-md outline-hidden lg:relative lg:flex',
                    isActive && 'z-aboveOverlay',
                )}
                onMouseEnter={() => isDesktop && setIsActive(true)}
                onMouseLeave={(e) => isDesktop && !isBrowserPasswordManagerHovered(e) && setIsActive(false)}
                onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                        setIsActive(true);
                    }

                    if (e.key === 'Escape') {
                        setIsActive(false);
                    }
                }}
            >
                <MenuIconicItemLink
                    ariaExpanded={isActive}
                    ariaHaspopup="menu"
                    ariaLabel={t('Show registration and login popup', { ns: 'accessibility' })}
                    className="cursor-pointer text-nowrap transition-all group-focus-visible:bg-orange-500 group-focus-visible:text-text-default lg:w-[72px]"
                    tabIndex={-1}
                    tid={TIDs.layout_header_menuiconic_login_link_popup}
                    title={t('Login form')}
                    onClick={() => !isDesktop && setIsActive(!isActive)}
                    onTouchEnd={(e) => {
                        e.preventDefault();
                        setIsActive(!isActive);
                    }}
                >
                    <UserIcon className="size-6" />
                    <span className="hidden lg:inline-block">{t('Login')}</span>
                </MenuIconicItemLink>

                <Drawer isActive={isActive} setIsActive={setIsActive} title={t('My account')}>
                    <MenuIconicItemUserUnauthenticatedContent />
                </Drawer>

                <MenuIconicItemUserPopover isAuthenticated={false} isHovered={isActiveDelayed}>
                    <MenuIconicItemUserUnauthenticatedContent />
                </MenuIconicItemUserPopover>
            </div>

            <Overlay isActive={isActiveDelayed} onClick={() => setIsActive(false)} />
        </>
    );
};
