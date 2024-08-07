import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useLogout } from 'utils/auth/useLogout';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const CustomerContent: FC = () => {
    const { t } = useTranslation();
    const logout = useLogout();
    const { url } = useDomainConfig();
    const [customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer/orders', '/customer/edit-profile'],
        url,
    );

    return (
        <>
            <Webline>
                <div className="text-center">
                    <h1>{t('Customer')}</h1>
                </div>
            </Webline>

            <Webline>
                <ul className="mb-8 flex flex-col flex-wrap gap-4 md:flex-row">
                    <CustomerListItem>
                        <ExtendedNextLink href={customerOrdersUrl} type="orders">
                            {t('My orders')}
                        </ExtendedNextLink>
                    </CustomerListItem>

                    <CustomerListItem>
                        <ExtendedNextLink href={customerEditProfileUrl}>{t('Edit profile')}</ExtendedNextLink>
                    </CustomerListItem>

                    <CustomerListItem>
                        <a tid={TIDs.customer_page_logout} onClick={logout}>
                            {t('Logout')}
                        </a>
                    </CustomerListItem>
                </ul>
            </Webline>
        </>
    );
};

const CustomerListItem: FC = ({ children }) => (
    <li
        className={twJoin(
            'block flex-1 cursor-pointer rounded text-lg transition [&_a]:block [&_a]:h-full [&_a]:w-full [&_a]:p-5 [&_a]:text-text [&_a]:no-underline hover:[&_a]:no-underline',
            'bg-backgroundMore',
            'hover:bg-backgroundMost ',
        )}
    >
        {children}
    </li>
);
