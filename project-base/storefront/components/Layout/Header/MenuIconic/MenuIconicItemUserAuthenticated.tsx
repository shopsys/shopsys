import { MenuIconicItemLink, MenuIconicSubItemLink } from './MenuIconicElements';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { UserIcon } from 'components/Basic/Icon/IconsSvg';
import { TIDs } from 'cypress/tids';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';

export const MenuIconicItemUserAuthenticated: FC = () => {
    const { t } = useTranslation();
    const { logout } = useAuth();
    const { url } = useDomainConfig();
    const [customerUrl, customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/orders', '/customer/edit-profile'],
        url,
    );

    return (
        <>
            <div className="group">
                <MenuIconicItemLink
                    className="rounded-t p-3 group-hover:bg-white group-hover:text-dark max-lg:hidden"
                    href={customerUrl}
                    tid={TIDs.my_account_link}
                >
                    <UserIcon className="w-4 text-white group-hover:text-dark" />
                    {t('My account')}
                </MenuIconicItemLink>

                <ul className="pointer-events-none absolute top-full right-0 z-cart block min-w-[150px] origin-top-right scale-50 rounded rounded-tr-none bg-white opacity-0 shadow-lg transition-all group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100">
                    <li className="block">
                        <MenuIconicSubItemLink href={customerOrdersUrl} type="orders">
                            {t('My orders')}
                        </MenuIconicSubItemLink>
                    </li>
                    <li className="block border-t border-border">
                        <MenuIconicSubItemLink href={customerEditProfileUrl}>{t('Edit profile')}</MenuIconicSubItemLink>
                    </li>
                    <li className="block border-t border-border">
                        <MenuIconicSubItemLink tid={TIDs.header_logout} onClick={logout}>
                            {t('Logout')}
                        </MenuIconicSubItemLink>
                    </li>
                </ul>
            </div>

            <div className="order-2 ml-1 flex h-9 w-9 cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <ExtendedNextLink href={customerUrl}>
                    <div className="relative flex h-full w-full items-center justify-center text-white transition-colors">
                        <UserIcon className="w-4 text-white" />
                    </div>
                </ExtendedNextLink>
            </div>
        </>
    );
};
