import { MenuIconicItemLink, MenuIconicSubItemLink } from './MenuIconicElements';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowRightIcon } from 'components/Basic/Icon/ArrowRightIcon';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useLogout } from 'utils/auth/useLogout';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';

export const MenuIconicItemUserAuthenticated: FC = () => {
    const { t } = useTranslation();
    const logout = useLogout();
    const { url } = useDomainConfig();
    const [customerUrl, customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/orders', '/customer/edit-profile'],
        url,
    );
    const [isClicked, setIsClicked] = useState(false);
    const [isHovered, setIsHovered] = useState(false);

    const userMenuItemTwClass =
        'h-14 rounded-xl bg-backgroundAccentLess hover:bg-backgroundMost active:bg-backgroundMost';
    const userMenuItemIconTwClass = 'flex flex-row w-[44px] justify-start';

    return (
        <>
            <div
                className="group lg:relative z-aboveOverlay"
                tid={TIDs.my_account_link}
                onMouseEnter={() => setIsHovered(true)}
                onMouseLeave={() => setIsHovered(false)}
            >
                <MenuIconicItemLink className="rounded-t p-3 max-lg:hidden transition-all" href={customerUrl}>
                    <div className="relative">
                        <UserIcon className="w-5 lg:w-6 max-h-[22px]" isFull={false} />
                        <div className="w-[10px] h-[10px] absolute -right-1 -top-1 rounded-full bg-actionPrimaryBackground" />
                    </div>
                    {t('My account')}
                </MenuIconicItemLink>

                <div className="order-2 flex h-full w-12 cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                    <ExtendedNextLink
                        href={customerUrl}
                        onClick={(e) => {
                            e.preventDefault();
                            setIsClicked(!isClicked);
                            setIsClicked(!isHovered);
                        }}
                    >
                        <div className="relative flex h-full w-full items-center justify-center text-textInverted transition-colors">
                            <UserIcon className="w-6 lg:w-4 text-textInverted" isFull={false} />
                            <div className="w-[10px] h-[10px] absolute -right-1 -top-1 rounded-full bg-actionPrimaryBackground" />
                        </div>
                    </ExtendedNextLink>
                </div>

                <div
                    className={twMergeCustom(
                        'pointer-events-none absolute top-full -right-[100%] z-cart block min-w-[315px] origin-top-right scale-50 rounded-xl transition-all group-hover:pointer-events-auto p-4',
                        'bg-none scale-50 opacity-0',
                        'group-hover:bg-background group-hover:scale-100 group-hover:opacity-100',
                        isClicked &&
                            'scale-100 opacity-100 bg-background top-0 right-0 rounded-none h-dvh fixed z-aboveOverlay pointer-events-auto',
                    )}
                >
                    <div className="flex flex-row justify-between mb-10 lg:hidden">
                        <ExtendedNextLink href={customerUrl} onClick={() => setIsClicked(false)}>
                            <ArrowRightIcon className="rotate-180 text-borderAccent w-4" />
                        </ExtendedNextLink>
                        <span className="text-base">{t('My account')}</span>
                        <RemoveIcon
                            className="w-4 text-borderAccent cursor-pointer"
                            onClick={() => setIsClicked(false)}
                        />
                    </div>
                    <ul className="flex flex-col gap-[10px] max-h-[87dvh] overflow-auto">
                        <li className={userMenuItemTwClass}>
                            <MenuIconicSubItemLink
                                href={customerOrdersUrl}
                                tid={TIDs.header_my_orders_link}
                                type="orders"
                            >
                                <div className={userMenuItemIconTwClass}>
                                    <SearchListIcon className="w-6 h-6" />
                                </div>
                                {t('My orders')}
                            </MenuIconicSubItemLink>
                        </li>
                        <li className={userMenuItemTwClass}>
                            <MenuIconicSubItemLink href={customerEditProfileUrl} tid={TIDs.header_edit_profile_link}>
                                <div className={userMenuItemIconTwClass}>
                                    <EditIcon className="w-6 h-6" />
                                </div>
                                {t('Edit profile')}
                            </MenuIconicSubItemLink>
                        </li>
                        <li className={userMenuItemTwClass}>
                            <MenuIconicSubItemLink tid={TIDs.header_logout} onClick={logout}>
                                <div className={userMenuItemIconTwClass}>
                                    <ExitIcon className="w-6 h-6" />
                                </div>
                                {t('Logout')}
                            </MenuIconicSubItemLink>
                        </li>
                    </ul>
                </div>
            </div>

            <Overlay
                isActive={isClicked || isHovered}
                onClick={() => {
                    setIsClicked(false);
                    setIsHovered(false);
                }}
            />
        </>
    );
};
