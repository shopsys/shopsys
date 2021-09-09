import { ProductFlagsItemStyled, ProductFlagsStyled } from './ProductFlags.style';
import { FC } from 'react';
import { FlagType } from '../types';

type ProductFlagsProps = { flags: FlagType[] };

const ProductFlags: FC<ProductFlagsProps> = (props) => {
    if (props.flags.length === 0) {
        return null;
    }

    return (
        <ProductFlagsStyled>
            {props.flags.map(({ name, rgbColor }, key) => (
                <ProductFlagsItemStyled key={key} color={rgbColor}>
                    {name}
                </ProductFlagsItemStyled>
            ))}
        </ProductFlagsStyled>
    );
};

export default ProductFlags;
