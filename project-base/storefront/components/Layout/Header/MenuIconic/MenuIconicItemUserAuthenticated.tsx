import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { MenuIconicItemLink, MenuIconicItemIcon, MenuIconicSubItemLink } from './MenuIconicElements';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useAuth } from 'hooks/auth/useAuth';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { User } from 'components/Basic/Icon/IconsSvg';

export const MenuIconicItemUserAuthenticated: FC = ({ dataTestId }) => {
    const t = useTypedTranslationFunction();
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
                    href={customerUrl}
                    className="rounded-t p-3 group-hover:bg-white group-hover:text-dark max-lg:hidden"
                    dataTestId={dataTestId + '-my-account'}
                >
                    <MenuIconicItemIcon icon={<User />} className="group-hover:text-dark" />
                    {t('My account')}
                </MenuIconicItemLink>

                <ul className="pointer-events-none absolute top-full right-0 z-cart block min-w-[150px] origin-top-right scale-50 rounded rounded-tr-none bg-white opacity-0 shadow-lg transition-all group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100">
                    <li className="block" data-testid={dataTestId + '-sub-0'}>
                        <MenuIconicSubItemLink href={customerOrdersUrl}>{t('My orders')}</MenuIconicSubItemLink>
                    </li>
                    <li className="block border-t border-border">
                        <MenuIconicSubItemLink href={customerEditProfileUrl} dataTestId={dataTestId + '-sub-1'}>
                            {t('Edit profile')}
                        </MenuIconicSubItemLink>
                    </li>
                    <li className="block border-t border-border">
                        <MenuIconicSubItemLink onClick={logout} dataTestId={dataTestId + '-sub-2'}>
                            {t('Logout')}
                        </MenuIconicSubItemLink>
                    </li>
                </ul>
            </div>

            <div className="order-2 ml-1 flex h-9 w-9 cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <ExtendedNextLink href={customerUrl} type="static">
                    <div className="relative flex h-full w-full items-center justify-center text-white transition-colors">
                        <MenuIconicItemIcon icon={<User />} className="mr-0" />
                    </div>
                </ExtendedNextLink>
            </div>
        </>
    );
};
