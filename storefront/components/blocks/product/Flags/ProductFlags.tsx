import { FC } from 'react';
import { FlagType } from '../types';

type ProductFlagsProps = { flags: FlagType[] };

const ProductFlags: FC<ProductFlagsProps> = (props) => {
    if (props.flags.length === 0) {
        return null;
    }

    return (
        <ul>
            {props.flags.map(({ name, rgbColor }, key) => (
                <li key={key}>
                    {name} - {rgbColor}
                </li>
            ))}
        </ul>
    );
};

export default ProductFlags;
