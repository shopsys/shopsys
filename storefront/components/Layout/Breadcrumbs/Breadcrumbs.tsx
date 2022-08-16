import {
    BreadcrumbsLinkStyled,
    BreadcrumbsSpanStyled,
    BreadcrumbsStyled,
    LeftArrowIconStyled,
} from './Breadcrumbs.style';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata/BreadcrumbsMetadata';
import Webline from 'components/Layout/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';

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
            <BreadcrumbsMetadata breadcrumbs={props.breadcrumb} />
            <BreadcrumbsStyled data-testid={testIdentifier}>
                <LeftArrowIconStyled iconType="icon" icon="Arrow" />
                <NextLink href="/" passHref>
                    <BreadcrumbsLinkStyled data-testid={testIdentifier + '-item-root'}>
                        {t('Home page')}
                    </BreadcrumbsLinkStyled>
                </NextLink>
                <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                {props.breadcrumb.slice(0, props.breadcrumb.length - 1).map((breadcrumb, index) => (
                    <Fragment key={index}>
                        <NextLink href={breadcrumb.slug} passHref>
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
