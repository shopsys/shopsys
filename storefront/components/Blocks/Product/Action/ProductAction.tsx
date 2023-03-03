import { AddToCart } from 'components/Blocks/Product/AddToCart/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/dist/client/router';
import { GtmListNameType } from 'types/gtm';

type ProductActionProps = {
    product: ListedProductFragmentApi;
    gtmListName: GtmListNameType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'blocks-product-action';

export const ProductAction: FC<ProductActionProps> = ({ product, gtmListName, listIndex }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();

    if (product.isMainVariant) {
        return (
            <ProductActionWrapper>
                <Button
                    onClick={() => router.push(product.slug)}
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
            <div className="px-2 pb-3" data-testid={TEST_IDENTIFIER}>
                <ProductActionWrapper>
                    <p className="p-1">{t('This item can no longer be purchased')}</p>
                </ProductActionWrapper>
            </div>
        );
    }

    return (
        <div className="px-2 pb-3" data-testid={TEST_IDENTIFIER}>
            <ProductActionWrapper>
                <AddToCart
                    productUuid={product.uuid}
                    minQuantity={1}
                    maxQuantity={product.stockQuantity}
                    gtmListName={gtmListName}
                    listIndex={listIndex}
                />
            </ProductActionWrapper>
        </div>
    );
};

const ProductActionWrapper: FC = ({ children }) => (
    <div className="flex flex-nowrap justify-between rounded-xl bg-greyVeryLight p-2">{children}</div>
);
