import { FC } from 'react';
import Link from 'next/link';
import ProductAction from './Action/ProductAction';
import ProductAvailableStoresCount from './Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from './Availability/ProductExposedStoresCount';
import ProductFlags from './Flags/ProductFlags';
import { ProductItemType } from './types';
import ProductPrice from './Price/ProductPrice';
import ShopsysImage from '../../basic/ShopsysImage/ShopsysImage';

const ProductItem: FC<ProductItemType> = (props) => {
    return (
        <div>
            <Link href={props.detailSlug}>
                <div>
                    <ShopsysImage image={props.image} alt={props.name} />
                    <div>{props.name}</div>
                    <ProductFlags flags={props.flags} />
                    <ProductPrice {...props.price} />
                    <div>{props.availability}</div>
                    <ProductAvailableStoresCount {...props} />
                    <ProductExposedStoresCount {...props} />
                </div>
            </Link>
            <ProductAction {...props} />
        </div>
    );
};

export default ProductItem;
