import { MenuIconicItemLink, MenuIconicItemUserPopover } from './MenuIconicElements';
import { MenuIconicItemUserUnauthenticatedContent } from './MenuIconicItemUserUnauthenticatedContent';
import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState, MouseEvent as ReactMouseEvent } from 'react';
import { desktopFirstSizes } from 'utils/mediaQueries';
import { twMergeCustom } from 'utils/twMerge';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useDebounce } from 'utils/useDebounce';

const isBrowserPasswordManagerHovered = (e: ReactMouseEvent<HTMLDivElement, MouseEvent>) => e.relatedTarget === window;
export const MenuIconicItemUserUnauthenticated: FC = () => {
    const { t } = useTranslation();
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
                onMouseLeave={(e) => isDesktop && !isBrowserPasswordManagerHovered(e) && setIsHovered(false)}
            >
                <MenuIconicItemLink
                    className="cursor-pointer lg:w-[72px]"
                    tid={TIDs.layout_header_menuiconic_login_link_popup}
                    onClick={() => {
                        setIsClicked(!isClicked);
                        setIsClicked(!isHovered);
                    }}
                >
                    <UserIcon className="size-6" />
                    <span className="hidden lg:inline-block">{t('Login')}</span>
                </MenuIconicItemLink>

                <Drawer className="lg:hidden" isClicked={isClicked} setIsClicked={setIsClicked} title={t('My account')}>
                    <MenuIconicItemUserUnauthenticatedContent
                        isClicked={isClicked}
                        isHoveredDelayed={isHoveredDelayed}
                    />
                </Drawer>

                <MenuIconicItemUserPopover isAuthenticated={false} isHovered={isHoveredDelayed}>
                    <MenuIconicItemUserUnauthenticatedContent
                        isClicked={isClicked}
                        isHoveredDelayed={isHoveredDelayed}
                    />
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
