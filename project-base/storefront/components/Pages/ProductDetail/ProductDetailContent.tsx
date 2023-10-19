import { ProductDetailCode, ProductDetailHeading, ProductDetailPrefix } from './ProductDetaiElements';
import { ProductDetailAccessories } from './ProductDetailAccessories';
import { ProductDetailAddToCart } from './ProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { Webline } from 'components/Layout/Webline/Webline';
import { ProductDetailFragmentApi } from 'graphql/generated';
import { useGtmProductDetailViewEvent } from 'gtm/hooks/useGtmProductDetailViewEvent';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useComparison } from 'hooks/comparison/useComparison';
import { useWishlist } from 'hooks/useWishlist';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useRef } from 'react';

const ProductComparePopup = dynamic(() =>
    import('components/Blocks/Product/ButtonsAction/ProductComparePopup').then(
        (component) => component.ProductComparePopup,
    ),
);

type ProductDetailContentProps = {
    product: ProductDetailFragmentApi;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'pages-productdetail-';

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, fetching }) => {
    const { t } = useTranslation();
    const scrollTarget = useRef<HTMLUListElement>(null);
    const router = useRouter();
    const { isProductInComparison, toggleProductInComparison, isPopupCompareOpen, setIsPopupCompareOpen } =
        useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        <>
            <ProductMetadata product={product} />

            <Webline className="flex flex-col gap-8">
                <div className="flex flex-col flex-wrap gap-6 lg:flex-row">
                    <ProductDetailGallery
                        flags={product.flags}
                        images={product.images}
                        productName={product.name}
                        videoIds={product.productVideos}
                    />

                    <div className="flex flex-1 flex-col gap-4">
                        <div className="flex flex-col gap-1">
                            {product.namePrefix && (
                                <ProductDetailPrefix dataTestId={TEST_IDENTIFIER + 'prefix'}>
                                    {product.namePrefix}
                                </ProductDetailPrefix>
                            )}

                            <ProductDetailHeading dataTestId={TEST_IDENTIFIER + 'name'}>
                                {product.name} {product.nameSuffix}
                            </ProductDetailHeading>

                            <ProductDetailCode dataTestId={TEST_IDENTIFIER + 'code'}>
                                {t('Code')}: {product.catalogNumber}
                            </ProductDetailCode>
                        </div>

                        {product.shortDescription && (
                            <div data-testid={TEST_IDENTIFIER + 'short-description'}>{product.shortDescription}</div>
                        )}

                        <ProductDetailAddToCart product={product} />

                        <ProductDetailAvailability product={product} scrollTarget={scrollTarget} />

                        <div className="flex flex-col gap-y-2 gap-x-4 vl:flex-row">
                            <ProductCompareButton
                                isWithText
                                isProductInComparison={isProductInComparison(product.uuid)}
                                toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                            />
                            <ProductWishlistButton
                                isWithText
                                isProductInWishlist={isProductInWishlist(product.uuid)}
                                toggleProductInWishlist={() => toggleProductInWishlist(product.uuid)}
                            />
                        </div>
                    </div>
                </div>

                <ProductDetailTabs description={product.description} parameters={product.parameters} />

                <ProductDetailAvailabilityList ref={scrollTarget} storeAvailabilities={product.storeAvailabilities} />

                {!!product.accessories.length && <ProductDetailAccessories accessories={product.accessories} />}
            </Webline>
            {isPopupCompareOpen && <ProductComparePopup onCloseCallback={() => setIsPopupCompareOpen(false)} />}
        </>
    );
};
