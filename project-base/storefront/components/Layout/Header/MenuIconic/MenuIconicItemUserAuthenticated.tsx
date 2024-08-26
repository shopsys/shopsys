import { MenuIconicItemLink, MenuIconicSubItemLink } from './MenuIconicElements';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useLogout } from 'utils/auth/useLogout';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const MenuIconicItemUserAuthenticated: FC = () => {
    const { t } = useTranslation();
    const logout = useLogout();
    const { url } = useDomainConfig();
    const [customerUrl, customerOrdersUrl, customerComplaintsUrl, customerEditProfileUrl] =
        getInternationalizedStaticUrls(
            ['/customer', '/customer/orders', '/customer/complaints', '/customer/edit-profile'],
            url,
        );

    return (
        <>
            <div className="group" tid={TIDs.my_account_link}>
                <MenuIconicItemLink
                    className="rounded-t p-3 group-hover:bg-background group-hover:text-linkHovered max-lg:hidden group-hover:underline hover:underline transition-all"
                    href={customerUrl}
                >
                    <div className="relative">
                        <UserIcon className="w-5 lg:w-6 max-h-[22px]" isFull={false} />
                        <div className="w-[10px] h-[10px] absolute -right-1 -top-1 rounded-full bg-actionPrimaryBackground" />
                    </div>
                    {t('My account')}
                </MenuIconicItemLink>

                <ul
                    className={twJoin(
                        'pointer-events-none absolute top-full right-0 z-cart block min-w-[150px] origin-top-right scale-50 rounded rounded-tr-none shadow-lg transition-all group-hover:pointer-events-auto',
                        'bg-none scale-50 opacity-0',
                        'group-hover:bg-background group-hover:scale-100 group-hover:opacity-100',
                    )}
                >
                    <li className="block">
                        <MenuIconicSubItemLink href={customerOrdersUrl} tid={TIDs.header_my_orders_link} type="orders">
                            {t('My orders')}
                        </MenuIconicSubItemLink>
                    </li>
                    <li className="block border-t border-borderAccent">
                        <MenuIconicSubItemLink href={customerComplaintsUrl} tid={TIDs.header_my_complaints_link}>
                            {t('My complaints')}
                        </MenuIconicSubItemLink>
                    </li>
                    <li className="block border-t border-borderAccent">
                        <MenuIconicSubItemLink href={customerEditProfileUrl} tid={TIDs.header_edit_profile_link}>
                            {t('Edit profile')}
                        </MenuIconicSubItemLink>
                    </li>
                    <li className="block border-t border-borderAccent">
                        <MenuIconicSubItemLink tid={TIDs.header_logout} onClick={logout}>
                            {t('Logout')}
                        </MenuIconicSubItemLink>
                    </li>
                </ul>
            </div>

            <div className="order-2 flex h-full w-12 cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <ExtendedNextLink href={customerUrl}>
                    <div className="relative flex h-full w-full items-center justify-center text-textInverted transition-colors">
                        <UserIcon className="w-6 lg:w-4 text-textInverted" />
                    </div>
                </ExtendedNextLink>
            </div>
        </>
    );
};
