import { ProductDetailRelatedProductsTabProps } from './ProductDetailRelatedProductsTab';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';

export const ProductDetailRelatedProductsTabPlaceholder: FC<ProductDetailRelatedProductsTabProps> = ({
    relatedProducts,
}) =>
    relatedProducts.map((product) => (
        <ExtendedNextLink key={product.uuid} href={product.slug}>
            {product.fullName}
            <ProductPrice productPrice={product.price} />
        </ExtendedNextLink>
    ));
