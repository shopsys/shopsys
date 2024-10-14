import { MenuIconicItemLink } from './MenuIconicElements';
import { MenuIconicItemUserAuthenticatedPopover } from './MenuIconicItemUserAuthenticatedPopover';
import { MenuMyAccountList } from './MenuMyAccountList';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { desktopFirstSizes } from 'utils/mediaQueries';
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
    const isDesktop = width > desktopFirstSizes.tablet;

    return (
        <>
            <div
                className={twMergeCustom('group lg:relative lg:flex', (isClicked || isHovered) && 'z-aboveOverlay')}
                tid={TIDs.my_account_link}
                onMouseEnter={() => isDesktop && setIsHovered(true)}
                onMouseLeave={() => isDesktop && setIsHovered(false)}
            >
                <MenuIconicItemLink
                    className="text-nowrap rounded-t transition-all max-lg:hidden"
                    href={customerUrl}
                    type="account"
                >
                    <div className="relative">
                        <UserIcon className="size-6" />
                        <div className="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-actionPrimaryBackground" />
                    </div>
                    {t('My account')}
                </MenuIconicItemLink>

                <div className="order-2 flex w-10 cursor-pointer items-center justify-center text-lg outline-none sm:w-12 lg:hidden">
                    <div
                        className="relative flex items-center justify-center text-textInverted transition-colors"
                        onClick={() => {
                            setIsClicked(!isClicked);
                            setIsClicked(!isHovered);
                        }}
                    >
                        <UserIcon className="size-6 text-textInverted" />
                        <div className="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-actionPrimaryBackground" />
                    </div>
                </div>

                <Drawer className="lg:hidden" isClicked={isClicked} setIsClicked={setIsClicked} title={t('My account')}>
                    <MenuMyAccountList />
                </Drawer>

                <MenuIconicItemUserAuthenticatedPopover isHovered={isHoveredDelayed}>
                    <MenuMyAccountList />
                </MenuIconicItemUserAuthenticatedPopover>
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
