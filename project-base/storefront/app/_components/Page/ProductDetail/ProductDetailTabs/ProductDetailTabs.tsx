import { ProductDetailDescriptionTab } from './ProductDetailDesctiptionTab';
import { ProductDetailParametersTab } from './ProductDetailParametersTab';
import { ProductDetailRelatedProductsTab } from './ProductDetailRelatedProductsTab';
import { ProductDetailTabsContent } from './ProductDetailTabsContent';
import { ProductDetailTabsFiles } from './ProductDetailTabsFiles';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.ssr';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.ssr';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';

export type ProductDetailTabsProps = {
    description: string | null;
    parameters: TypeParameterFragment[];
    relatedProducts: TypeListedProductFragment[];
    files: TypeFileFragment[];
};

export const ProductDetailTabs: FC<ProductDetailTabsProps> = ({ description, files, parameters, relatedProducts }) => {
    return (
        <ProductDetailTabsContent
            description={<ProductDetailDescriptionTab description={description} />}
            files={<ProductDetailTabsFiles files={files} />}
            parameters={<ProductDetailParametersTab parameters={parameters} />}
            relatedProducts={<ProductDetailRelatedProductsTab relatedProducts={relatedProducts} />}
        />
    );
};
