import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';

export const CustomerContent: FC = () => {
    const { t } = useTranslation();
    const { logout } = useAuth();
    const { url } = useDomainConfig();
    const [customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer/orders', '/customer/edit-profile'],
        url,
    );

    return (
        <>
            <Webline>
                <div className="text-center">
                    <h1 className="mb-3">{t('Customer')}</h1>
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
                        <a data-testid="customer-page-logout" onClick={logout}>
                            {t('Logout')}
                        </a>
                    </CustomerListItem>
                </ul>
            </Webline>
        </>
    );
};

const CustomerListItem: FC = ({ children }) => (
    <li className="block flex-1 cursor-pointer rounded bg-greyVeryLight text-lg text-dark transition hover:bg-greyLighter [&_a]:block [&_a]:h-full [&_a]:w-full [&_a]:p-5 [&_a]:no-underline hover:[&_a]:text-dark hover:[&_a]:no-underline">
        {children}
    </li>
);
