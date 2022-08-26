import { ProductFlagsItemStyled } from './ProductFlags.style';
import { FC } from 'react';
import { SimpleFlagType } from 'types/flag';

type ProductFlagsProps = { flags: SimpleFlagType[] };

const TEST_IDENTIFIER = 'blocks-product-flags-';

export const ProductFlags: FC<ProductFlagsProps> = ({ flags }) => {
    if (flags.length === 0) {
        return null;
    }

    return (
        <>
            {flags.map(({ name, rgbColor }, key) => (
                <ProductFlagsItemStyled key={key} color={rgbColor} data-testid={TEST_IDENTIFIER + key}>
                    {name}
                </ProductFlagsItemStyled>
            ))}
        </>
    );
};
