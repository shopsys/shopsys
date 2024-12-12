'use client';

import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';
import { MenuIconicItemUserUnauthenticatedContent } from './MenuIconicItemUserUnauthenticatedContent';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState, MouseEvent as ReactMouseEvent } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDebounce } from 'utils/useDebounce';

const isBrowserPasswordManagerHovered = (e: ReactMouseEvent<HTMLDivElement, MouseEvent>) => e.relatedTarget === window;

export const MenuIconicItemUserUnauthenticated: FC = () => {
    const { t } = useTranslation();
    const [isActive, setIsActive] = useState(false);
    const isActiveDelayed = useDebounce(isActive, 200);
    const isDesktop = useMediaMin('vl');

    return (
        <>
            <div
                className={twMergeCustom('group lg:relative lg:flex', isActive && 'z-aboveOverlay')}
                tid={TIDs.my_account_link}
                onMouseEnter={() => isDesktop && setIsActive(true)}
                onMouseLeave={(e) => isDesktop && !isBrowserPasswordManagerHovered(e) && setIsActive(false)}
            >
                <MenuIconicItemLink
                    className="cursor-pointer lg:w-[72px]"
                    tid={TIDs.layout_header_menuiconic_login_link_popup}
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
