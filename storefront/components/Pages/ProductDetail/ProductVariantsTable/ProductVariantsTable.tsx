import {
    TableHeaderActionCellStyled,
    TableHeaderCellStyled,
    TableHeaderImageCellStyled,
    TableHeaderPriceCellStyled,
    VariantsTableBodyStyled,
    VariantsTableHeaderStyled,
    VariantsTableRowStyled,
    VariantsTableStyled,
} from './ProductVariantsTable.style';
import Variant from './Variant';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { ListedVariantType } from 'types/product';

type ProductVariantsTableProps = {
    variants: ListedVariantType[];
    isSellingDenied: boolean;
};

const ProductVariantsTable: FC<ProductVariantsTableProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <VariantsTableStyled>
                <VariantsTableHeaderStyled>
                    <VariantsTableRowStyled>
                        <TableHeaderImageCellStyled></TableHeaderImageCellStyled>
                        <TableHeaderCellStyled>{t('Name')}</TableHeaderCellStyled>
                        <TableHeaderCellStyled>{t('Availability')}</TableHeaderCellStyled>
                        <TableHeaderPriceCellStyled>{t('Price with VAT')}</TableHeaderPriceCellStyled>
                        <TableHeaderActionCellStyled></TableHeaderActionCellStyled>
                    </VariantsTableRowStyled>
                </VariantsTableHeaderStyled>
                <VariantsTableBodyStyled>
                    {props.variants.map((variant) => (
                        <Variant key={variant.uuid} variant={variant} isSellingDenied={props.isSellingDenied} />
                    ))}
                </VariantsTableBodyStyled>
            </VariantsTableStyled>
        </>
    );
};

export default ProductVariantsTable;
