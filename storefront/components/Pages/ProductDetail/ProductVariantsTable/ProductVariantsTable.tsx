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
import { Variant } from './Variant/Variant';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { ListedVariantType } from 'types/product';

type ProductVariantsTableProps = {
    variants: ListedVariantType[];
    isSellingDenied: boolean;
};

export const ProductVariantsTable: FC<ProductVariantsTableProps> = ({ isSellingDenied, variants }) => {
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
                    {variants.map((variant, index) => (
                        <Variant
                            key={variant.uuid}
                            variant={variant}
                            isSellingDenied={isSellingDenied}
                            gtmListName="variants"
                            listIndex={index}
                        />
                    ))}
                </VariantsTableBodyStyled>
            </VariantsTableStyled>
        </>
    );
};
