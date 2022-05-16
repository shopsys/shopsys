import { CustomerListItemStyled, CustomerListStyled } from './Customer.style';
import Heading from 'components/Basic/Heading';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import Webline from 'components/Layout/Webline';
import { useAuth } from 'hooks/auth/UseAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

const Customer: FC = () => {
    const t = useTypedTranslationFunction();
    const [, [, logout]] = useAuth();
    const { url } = useShopsysSelector((state) => state.domain);
    const [customerUrl, customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/orders', '/customer/edit-profile'],
        url,
    );

    const logoutHandler = () => {
        logout();
    };

    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">{t('Customer')}</Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={[{ name: t('Customer'), slug: customerUrl }]} />
            </Webline>
            <Webline>
                <CustomerListStyled>
                    <NextLink href={customerOrdersUrl} passHref>
                        <CustomerListItemStyled>{t('My orders')}</CustomerListItemStyled>
                    </NextLink>
                    <NextLink href={customerEditProfileUrl}>
                        <CustomerListItemStyled>{t('Edit profile')}</CustomerListItemStyled>
                    </NextLink>
                    <CustomerListItemStyled onClick={logoutHandler}>{t('Logout')}</CustomerListItemStyled>
                </CustomerListStyled>
            </Webline>
        </>
    );
};

export default Customer;
