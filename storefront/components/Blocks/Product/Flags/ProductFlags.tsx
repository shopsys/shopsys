import { FC } from 'react';
import { FlagType } from 'components/Blocks/Product/types';
import { ProductFlagsItemStyled } from './ProductFlags.style';

type ProductFlagsProps = { flags: FlagType[] };

const ProductFlags: FC<ProductFlagsProps> = (props) => {
    if (props.flags.length === 0) {
        return null;
    }

    return (
        <>
            {props.flags.map(({ name, rgbColor }, key) => (
                <ProductFlagsItemStyled key={key} color={rgbColor}>
                    {name}
                </ProductFlagsItemStyled>
            ))}
        </>
    );
};

export default ProductFlags;
