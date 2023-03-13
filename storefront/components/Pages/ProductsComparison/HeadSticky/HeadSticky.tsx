import {
    HeadStickyEmptyStyled,
    HeadStickyInStyled,
    HeadStickyItemCodeStyled,
    HeadStickyItemImageStyled,
    HeadStickyItemInfoStyled,
    HeadStickyItemNameStyled,
    HeadStickyItemStyled,
    HeadStickyStyled,
} from './HeadSticky.style';
import { Image } from 'components/Basic/Image/Image';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { useHandleCompareTable } from 'hooks/product/useHandleCompareTable';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

type HeadStickyProps = {
    productsCompare: ComparedProductFragmentApi[];
    tableMarginLeft: number;
};

export const HeadSticky: FC<HeadStickyProps> = (props) => {
    const { tableStickyHeadActive } = useHandleCompareTable(props.productsCompare.length);
    const t = useTypedTranslationFunction();

    return (
        <HeadStickyStyled className={tableStickyHeadActive ? 'isActive' : undefined}>
            <HeadStickyInStyled>
                <HeadStickyEmptyStyled />
                {props.productsCompare.map((product, index) => {
                    return (
                        <HeadStickyItemStyled
                            key={`headSticky-${product.uuid}`}
                            style={index === 0 ? { marginLeft: -props.tableMarginLeft } : undefined}
                        >
                            <HeadStickyItemImageStyled href={product.slug}>
                                <Image image={product.image} type="listVerySmall" alt={product.fullName} />
                            </HeadStickyItemImageStyled>
                            <HeadStickyItemInfoStyled>
                                <HeadStickyItemCodeStyled>
                                    {t('Code')}: {product.catalogNumber}
                                </HeadStickyItemCodeStyled>
                                <HeadStickyItemNameStyled href={product.slug}>
                                    {product.fullName}
                                </HeadStickyItemNameStyled>
                            </HeadStickyItemInfoStyled>
                        </HeadStickyItemStyled>
                    );
                })}
            </HeadStickyInStyled>
        </HeadStickyStyled>
    );
};
