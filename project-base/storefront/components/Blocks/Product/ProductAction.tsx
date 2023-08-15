import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/dist/client/router';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';

type ProductActionProps = {
    product: ListedProductFragmentApi;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'blocks-product-action';

export const ProductAction: FC<ProductActionProps> = ({ product, gtmProductListName, gtmMessageOrigin, listIndex }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();

    if (product.isMainVariant) {
        return (
            <ProductActionWrapper>
                <Button
                    onClick={() =>
                        router.push(
                            {
                                pathname: '/products/[productSlug]',
                            },
                            {
                                pathname: product.slug,
                            },
                        )
                    }
                    name="choose-variant"
                    dataTestId={TEST_IDENTIFIER + '-choose-variant'}
                    className="!w-full"
                >
                    {t('Choose variant')}
                </Button>
            </ProductActionWrapper>
        );
    }

    if (product.isSellingDenied) {
        return (
            <div data-testid={TEST_IDENTIFIER}>
                <ProductActionWrapper>
                    <p className="p-1">{t('This item can no longer be purchased')}</p>
                </ProductActionWrapper>
            </div>
        );
    }

    return (
        <div data-testid={TEST_IDENTIFIER}>
            <ProductActionWrapper>
                <AddToCart
                    className="w-full"
                    productUuid={product.uuid}
                    minQuantity={1}
                    maxQuantity={product.stockQuantity}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    listIndex={listIndex}
                />
            </ProductActionWrapper>
        </div>
    );
};

const ProductActionWrapper: FC = ({ children }) => (
    <div className="flex flex-wrap justify-center gap-2 rounded bg-greyVeryLight p-2">{children}</div>
);
