import {
    BreadcrumbsLinkStyled,
    BreadcrumbsSpanStyled,
    BreadcrumbsStyled,
    LeftArrowIconStyled,
} from './Breadcrumbs.style';
import { FC, Fragment } from 'react';
import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import NextLink from 'next/link';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Breadcrumbs: FC<BreadcrumbType> = (props) => {
    const t = useTypedTranslationFunction();

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
                <LeftArrowIconStyled iconType="icon" icon="Arrow" />
                <NextLink href="/">
                    <BreadcrumbsLinkStyled>{t('Home page')}</BreadcrumbsLinkStyled>
                </NextLink>
                <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                {props.breadcrumb.slice(0, props.breadcrumb.length - 1).map((breadcrumb, index) => (
                    <Fragment key={index}>
                        <NextLink href={breadcrumb.slug}>
                            <BreadcrumbsLinkStyled>{breadcrumb.name}</BreadcrumbsLinkStyled>
                        </NextLink>
                        <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                    </Fragment>
                ))}
                <BreadcrumbsSpanStyled>{props.breadcrumb[props.breadcrumb.length - 1].name}</BreadcrumbsSpanStyled>
            </BreadcrumbsStyled>
        </Webline>
    );
};

export default Breadcrumbs;
