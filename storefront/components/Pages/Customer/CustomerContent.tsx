import { CustomerListItemStyled, CustomerListStyled } from './CustomerContent.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/UseAuth';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';

type CustomerContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const CustomerContent: FC<CustomerContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [, [, logout]] = useAuth();
    const { url } = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer/orders', '/customer/edit-profile'],
        url,
    );

    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">{t('Customer')}</Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
            </Webline>
            <Webline>
                <CustomerListStyled>
                    <CustomerListItemStyled>
                        <NextLink href={customerOrdersUrl}>{t('My orders')}</NextLink>
                    </CustomerListItemStyled>
                    <CustomerListItemStyled>
                        <NextLink href={customerEditProfileUrl}>{t('Edit profile')}</NextLink>
                    </CustomerListItemStyled>
                    <CustomerListItemStyled>
                        <a onClick={logout}>{t('Logout')}</a>
                    </CustomerListItemStyled>
                </CustomerListStyled>
            </Webline>
        </>
    );
};
