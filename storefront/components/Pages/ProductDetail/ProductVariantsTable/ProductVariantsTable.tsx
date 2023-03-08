import { ProductVariantsTableRow } from './ProductVariantsTableRow';
import { Variant } from './Variant/Variant';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { ListedVariantType } from 'types/product';
import { twMergeCustom } from 'utils/twMerge';

type ProductVariantsTableProps = {
    variants: ListedVariantType[];
    isSellingDenied: boolean;
};

export const ProductVariantsTable: FC<ProductVariantsTableProps> = ({ isSellingDenied, variants }) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <table className="mb-5 w-full">
                <thead className="max-lg:hidden">
                    <ProductVariantsTableRow>
                        <Cell className="max-lg:w-10 lg:w-24" />
                        <Cell>{t('Name')}</Cell>
                        <Cell>{t('Availability')}</Cell>
                        <Cell className="lg:text-right">{t('Price with VAT')}</Cell>
                        <Cell className="lg:w-60" />
                    </ProductVariantsTableRow>
                </thead>
                <tbody className="max-lg:ml-0 max-lg:flex max-lg:flex-wrap md:-ml-1">
                    {variants.map((variant, index) => (
                        <Variant
                            key={variant.uuid}
                            variant={variant}
                            isSellingDenied={isSellingDenied}
                            gtmListName="variants"
                            listIndex={index}
                        />
                    ))}
                </tbody>
            </table>
        </>
    );
};

const Cell: FC = ({ className, children }) => (
    <th
        className={twMergeCustom(
            'text-left align-middle max-lg:block max-lg:pl-14 lg:border-b lg:border-greyLighter lg:p-1 lg:text-xs',
            className,
        )}
    >
        {children}
    </th>
);
