import { ProductFlagsItemStyled } from './ProductFlags.style';
import { FC } from 'react';
import { SimpleFlagType } from 'types/flag';

type ProductFlagsProps = { flags: SimpleFlagType[] };

const ProductFlags: FC<ProductFlagsProps> = (props) => {
    const testIdentifier = 'blocks-product-flags-';

    if (props.flags.length === 0) {
        return null;
    }

    return (
        <>
            {props.flags.map(({ name, rgbColor }, key) => (
                <ProductFlagsItemStyled key={key} color={rgbColor} data-testid={testIdentifier + key}>
                    {name}
                </ProductFlagsItemStyled>
            ))}
        </>
    );
};

export default ProductFlags;
