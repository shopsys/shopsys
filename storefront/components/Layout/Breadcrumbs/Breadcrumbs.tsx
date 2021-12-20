import {
    BreadcrumbsLinkStyled,
    BreadcrumbsSpanStyled,
    BreadcrumbsStyled,
    LeftArrowIconStyled,
} from './Breadcrumbs.style';
import { FC, Fragment } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';
import NextLink from 'next/link';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type BreadcrumbsProps = {
    breadcrumb: BreadcrumbItemType[];
};

const Breadcrumbs: FC<BreadcrumbsProps> = (props) => {
    const testIdentifier = 'layout-breadcrumbs';

    const t = useTypedTranslationFunction();

    if (props.breadcrumb.length === 0) {
        return null;
    }

    return (
        <Webline>
            <BreadcrumbsStyled data-testid={testIdentifier}>
                <LeftArrowIconStyled iconType="icon" icon="Arrow" />
                <NextLink href="/">
                    <BreadcrumbsLinkStyled data-testid={testIdentifier + '-item-root'}>
                        {t('Home page')}
                    </BreadcrumbsLinkStyled>
                </NextLink>
                <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                {props.breadcrumb.slice(0, props.breadcrumb.length - 1).map((breadcrumb, index) => (
                    <Fragment key={index}>
                        <NextLink href={breadcrumb.slug}>
                            <BreadcrumbsLinkStyled data-testid={testIdentifier + '-item-' + index}>
                                {breadcrumb.name}
                            </BreadcrumbsLinkStyled>
                        </NextLink>
                        <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                    </Fragment>
                ))}
                <BreadcrumbsSpanStyled data-testid={testIdentifier + '-item-last'}>
                    {props.breadcrumb[props.breadcrumb.length - 1].name}
                </BreadcrumbsSpanStyled>
            </BreadcrumbsStyled>
        </Webline>
    );
};

export default Breadcrumbs;
