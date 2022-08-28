import {
    BreadcrumbsLinkStyled,
    BreadcrumbsSpanStyled,
    BreadcrumbsStyled,
    LeftArrowIconStyled,
} from './Breadcrumbs.style';
import { BreadcrumbsMetadata } from 'components/Basic/Head/BreadcrumbsMetadata/BreadcrumbsMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';

type BreadcrumbsProps = {
    breadcrumb: BreadcrumbItemType[];
};

const TEST_IDENTIFIER = 'layout-breadcrumbs';

export const Breadcrumbs: FC<BreadcrumbsProps> = ({ breadcrumb }) => {
    const t = useTypedTranslationFunction();

    if (breadcrumb.length === 0) {
        return null;
    }

    return (
        <Webline>
            <BreadcrumbsMetadata breadcrumbs={breadcrumb} />
            <BreadcrumbsStyled data-testid={TEST_IDENTIFIER}>
                <LeftArrowIconStyled iconType="icon" icon="Arrow" />
                <NextLink href="/" passHref>
                    <BreadcrumbsLinkStyled data-testid={TEST_IDENTIFIER + '-item-root'}>
                        {t('Home page')}
                    </BreadcrumbsLinkStyled>
                </NextLink>
                <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                {breadcrumb.slice(0, breadcrumb.length - 1).map((breadcrumb, index) => (
                    <Fragment key={index}>
                        <NextLink href={breadcrumb.slug} passHref>
                            <BreadcrumbsLinkStyled data-testid={TEST_IDENTIFIER + '-item-' + index}>
                                {breadcrumb.name}
                            </BreadcrumbsLinkStyled>
                        </NextLink>
                        <BreadcrumbsSpanStyled>/</BreadcrumbsSpanStyled>
                    </Fragment>
                ))}
                <BreadcrumbsSpanStyled data-testid={TEST_IDENTIFIER + '-item-last'}>
                    {breadcrumb[breadcrumb.length - 1].name}
                </BreadcrumbsSpanStyled>
            </BreadcrumbsStyled>
        </Webline>
    );
};
