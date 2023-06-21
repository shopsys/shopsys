import { Heading } from 'components/Basic/Heading/Heading';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import NextLink from 'next/link';

type CustomerContentProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const CustomerContent: FC<CustomerContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
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
                    <Heading type="h1">{t('Customer')}</Heading>
                </div>
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
            </Webline>
            <Webline>
                <ul className="mb-8 flex flex-col flex-wrap gap-4 md:flex-row">
                    <CustomerListItem>
                        <NextLink href={customerOrdersUrl}>{t('My orders')}</NextLink>
                    </CustomerListItem>
                    <CustomerListItem>
                        <NextLink href={customerEditProfileUrl}>{t('Edit profile')}</NextLink>
                    </CustomerListItem>
                    <CustomerListItem>
                        <a onClick={logout}>{t('Logout')}</a>
                    </CustomerListItem>
                </ul>
            </Webline>
        </>
    );
};

const CustomerListItem: FC = ({ children }) => (
    <li className="block flex-1 cursor-pointer rounded-xl bg-greyVeryLight text-lg text-dark transition hover:bg-greyLighter [&_a]:block [&_a]:h-full [&_a]:w-full [&_a]:p-5 [&_a]:no-underline hover:[&_a]:text-dark hover:[&_a]:no-underline">
        {children}
    </li>
);
