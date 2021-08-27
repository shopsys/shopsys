import { BreadcrumbsLinkStyled, BreadcrumbsSpanStyled, BreadcrumbsStyled } from './Breadcrumbs.style';
import { FC, Fragment } from 'react';
import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import Link from 'next/link';
import { useTranslation } from 'next-i18next';
import Webline from 'components/layout/Webline';

const Breadcrumbs: FC<BreadcrumbType> = (props) => {
    const { t } = useTranslation();

    if (
        props.breadcrumb === undefined ||
        props.breadcrumb === null ||
        (Array.isArray(props.breadcrumb) && props.breadcrumb.length === 0)
    ) {
        return null;
    }

    return (
        <Webline>
            <BreadcrumbsStyled>
                <Link href="/">
                    <BreadcrumbsLinkStyled>{t('Home page')}</BreadcrumbsLinkStyled>
                </Link>
                <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                {props.breadcrumb.slice(0, props.breadcrumb.length - 1).map((breadcrumb, index) => (
                    <Fragment key={index}>
                        <Link href={breadcrumb.slug}>
                            <BreadcrumbsLinkStyled>{breadcrumb.name}</BreadcrumbsLinkStyled>
                        </Link>
                        <span>/</span>
                    </Fragment>
                ))}
                <BreadcrumbsSpanStyled>{props.breadcrumb[props.breadcrumb.length - 1].name}</BreadcrumbsSpanStyled>
            </BreadcrumbsStyled>
        </Webline>
    );
};

export default Breadcrumbs;
