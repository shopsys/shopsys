import {
    ProductDetail,
    ProductDetailCode,
    ProductDetailHeading,
    ProductDetailImage,
    ProductDetailInfo,
    ProductDetailPrefix,
} from './ProductDetaiElements';
import { ProductDetailAccessories } from './ProductDetailAccessories/ProductDetailAccessories';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductVariantsTable } from './ProductVariantsTable/ProductVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata/ProductMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useGtmProductDetailView } from 'hooks/gtm/useGtmProductDetailView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { MainVariantDetailType } from 'types/product';

type ProductDetailMainVariantContentProps = {
    product: MainVariantDetailType;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'pages-productdetail-';

export const ProductDetailMainVariantContent: FC<ProductDetailMainVariantContentProps> = ({ product, fetching }) => {
    const router = useRouter();
    useGtmProductDetailView(product, getUrlWithoutGetParameters(router.asPath), fetching);

    const t = useTypedTranslationFunction();

    return (
        <>
            <ProductMetadata product={product} />
            <Webline>
                <ProductDetail>
                    <ProductDetailImage data-testid={TEST_IDENTIFIER + 'gallery'}>
                        <ProductDetailGallery
                            images={product.images}
                            productName={product.name}
                            flags={product.flags}
                        />
                    </ProductDetailImage>
                    <ProductDetailInfo>
                        <ProductDetailPrefix dataTestId={TEST_IDENTIFIER + 'prefix'}>
                            {product.namePrefix}
                        </ProductDetailPrefix>
                        <ProductDetailHeading dataTestId={TEST_IDENTIFIER + 'name'}>
                            {product.name} {product.nameSuffix}
                        </ProductDetailHeading>
                        <ProductDetailCode dataTestId={TEST_IDENTIFIER + 'code'}>
                            {t('Code')}: {product.catalogNumber}
                        </ProductDetailCode>
                    </ProductDetailInfo>
                </ProductDetail>
            </Webline>
            <Webline testIdentifier={TEST_IDENTIFIER + 'variants'}>
                <ProductVariantsTable variants={product.variants} isSellingDenied={product.isSellingDenied} />
            </Webline>
            <Webline testIdentifier={TEST_IDENTIFIER + 'description'}>
                <ProductDetailTabs description={product.description} parameters={product.parameters} />
            </Webline>
            <Webline testIdentifier={TEST_IDENTIFIER + 'accessories'}>
                <ProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </>
    );
};
