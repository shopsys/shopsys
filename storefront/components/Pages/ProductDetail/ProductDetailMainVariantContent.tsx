import { ProductDetailAccessories } from './ProductDetailAccessories/ProductDetailAccessories';
import {
    ProductDetailCodeStyled,
    ProductDetailHeadingStyled,
    ProductDetailImageStyled,
    ProductDetailInfoStyled,
    ProductDetailPrefixStyled,
    ProductDetailStyled,
} from './ProductDetailContent.style';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductVariantsTable } from './ProductVariantsTable/ProductVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata/ProductMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useGtmProductDetailView } from 'hooks/gtm/useGtmProductDetailView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC } from 'react';
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
                <ProductDetailStyled>
                    <ProductDetailImageStyled data-testid={TEST_IDENTIFIER + 'gallery'}>
                        <ProductDetailGallery
                            images={product.images}
                            productName={product.name}
                            flags={product.flags}
                        />
                    </ProductDetailImageStyled>
                    <ProductDetailInfoStyled>
                        <ProductDetailPrefixStyled data-testid={TEST_IDENTIFIER + 'prefix'}>
                            {product.namePrefix}
                        </ProductDetailPrefixStyled>
                        <ProductDetailHeadingStyled data-testid={TEST_IDENTIFIER + 'name'}>
                            {product.name} {product.nameSuffix}
                        </ProductDetailHeadingStyled>
                        <ProductDetailCodeStyled data-testid={TEST_IDENTIFIER + 'code'}>
                            {t('Code')}: {product.catalogNumber}
                        </ProductDetailCodeStyled>
                    </ProductDetailInfoStyled>
                </ProductDetailStyled>
            </Webline>
            <Webline data-testid={TEST_IDENTIFIER + 'variants'}>
                <ProductVariantsTable variants={product.variants} isSellingDenied={product.isSellingDenied} />
            </Webline>
            <Webline data-testid={TEST_IDENTIFIER + 'description'}>
                <ProductDetailTabs description={product.description} parameters={product.parameters} />
            </Webline>
            <Webline data-testid={TEST_IDENTIFIER + 'accessories'}>
                <ProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </>
    );
};
