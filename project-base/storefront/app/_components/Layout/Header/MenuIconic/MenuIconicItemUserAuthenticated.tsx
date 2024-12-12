'use client';

import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';
import { MenuIconicItemUserAuthenticatedContent } from './MenuIconicItemUserAuthenticatedContent';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { CurrentCustomerType } from 'types/customer';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDebounce } from 'utils/useDebounce';

type MenuIconicItemUserAuthenticatedProps = {
    currentCustomerUser: CurrentCustomerType;
};

export const MenuIconicItemUserAuthenticated: FC<MenuIconicItemUserAuthenticatedProps> = ({ currentCustomerUser }) => {
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
                onMouseLeave={() => isDesktop && setIsActive(false)}
            >
                <MenuIconicItemLink
                    className="cursor-pointer text-nowrap rounded-t transition-all"
                    type="account"
                    onClick={() => !isDesktop && setIsActive(!isActive)}
                    onTouchEnd={(e) => {
                        e.preventDefault();
                        setIsActive(!isActive);
                    }}
                >
                    <div className="relative">
                        <UserIcon className="size-6" />
                        <div className="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-actionPrimaryBackground" />
                    </div>
                    <span className="hidden lg:inline-block">{t('My account')}</span>
                </MenuIconicItemLink>

                <Drawer isActive={isActive} setIsActive={setIsActive} title={t('My account')}>
                    <MenuIconicItemUserAuthenticatedContent currentCustomerUser={currentCustomerUser} />
                </Drawer>

                <MenuIconicItemUserPopover isAuthenticated isHovered={isActiveDelayed}>
                    <MenuIconicItemUserAuthenticatedContent currentCustomerUser={currentCustomerUser} />
                </MenuIconicItemUserPopover>
            </div>

            <Overlay isActive={isActiveDelayed} onClick={() => setIsActive(false)} />
        </>
    );
};
