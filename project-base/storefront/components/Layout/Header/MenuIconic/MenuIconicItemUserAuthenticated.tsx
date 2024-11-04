import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';
import { MenuIconicItemUserAuthenticatedContent } from './MenuIconicItemUserAuthenticatedContent';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { mobileFirstSizes } from 'utils/mediaQueries';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useDebounce } from 'utils/useDebounce';

export const MenuIconicItemUserAuthenticated: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerUrl] = getInternationalizedStaticUrls(['/customer'], url);
    const [isClicked, setIsClicked] = useState(false);
    const [isHovered, setIsHovered] = useState(false);
    const isHoveredDelayed = useDebounce(isHovered, 200);

    const { width } = useGetWindowSize();
    const isDesktop = width > mobileFirstSizes.vl;

    return (
        <>
            <div
                className={twMergeCustom('group lg:relative lg:flex', (isClicked || isHovered) && 'z-aboveOverlay')}
                tid={TIDs.my_account_link}
                onMouseEnter={() => isDesktop && setIsHovered(true)}
                onMouseLeave={() => isDesktop && setIsHovered(false)}
            >
                <MenuIconicItemLink
                    className="cursor-pointer text-nowrap rounded-t transition-all"
                    href={isDesktop ? customerUrl : undefined}
                    type="account"
                    onClick={() => {
                        if (!isDesktop) {
                            setIsClicked(!isClicked);
                            setIsClicked(!isHovered);
                        }
                    }}
                >
                    <div className="relative">
                        <UserIcon className="size-6" />
                        <div className="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-actionPrimaryBackground" />
                    </div>
                    <span className="hidden lg:inline-block">{t('My account')}</span>
                </MenuIconicItemLink>

                <Drawer className="vl:hidden" isClicked={isClicked} setIsClicked={setIsClicked} title={t('My account')}>
                    <MenuIconicItemUserAuthenticatedContent />
                </Drawer>

                <MenuIconicItemUserPopover isAuthenticated isHovered={isHoveredDelayed}>
                    <MenuIconicItemUserAuthenticatedContent />
                </MenuIconicItemUserPopover>
            </div>

            <Overlay
                isActive={isClicked || isHoveredDelayed}
                onClick={() => {
                    setIsClicked(false);
                    setIsHovered(false);
                }}
            />
        </>
    );
};
