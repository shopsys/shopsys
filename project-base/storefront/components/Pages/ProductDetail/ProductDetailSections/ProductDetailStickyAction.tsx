import { Image } from 'components/Basic/Image/Image';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { twMergeCustom } from 'utils/twMerge';
import { DeferredProductDetailAddToCart } from '../ProductDetailAddToCart/DeferredProductDetailAddToCart';

type ProductDetailStickyActionProps = {
    isVisible: boolean;
    placement: 'floating' | 'inline';
    product: TypeProductDetailFragment;
};

export const ProductDetailStickyAction = ({ isVisible, placement, product }: ProductDetailStickyActionProps) => {
    return (
        <div
            className={twMergeCustom(
                'flex min-w-0 items-center gap-1.5 vl:gap-3 xs:gap-2',
                placement === 'inline' && 'w-auto shrink-0',
                placement === 'floating' &&
                    'fixed right-4 bottom-[calc(5rem+env(safe-area-inset-bottom))] left-4 z-floatingAction mx-auto max-w-120 rounded-xl border border-border-less bg-background-default p-2 shadow-[0_8px_24px_rgba(0,0,0,0.24)]',
                !isVisible && 'hidden',
            )}
            data-tid={TIDs.product_detail_sticky_action}
        >
            <Image
                alt=""
                className="size-9 xs:size-10 shrink-0 object-contain"
                height={40}
                src={product.images[0]?.url}
                width={40}
            />

            <div className="min-w-0 vl:max-w-44 flex-1 vl:flex-none">
                <p className="truncate font-secondary font-semibold text-sm">{product.fullName}</p>

                <div className="vl:block hidden">
                    <ProductAvailability
                        availability={product.availability}
                        availableStoresCount={product.availableStoresCount}
                        className="max-w-full text-xs [&>span]:truncate"
                        isInquiryType={product.isInquiryType}
                    />
                </div>

                <div className="flex vl:hidden min-w-0 items-center gap-1.5">
                    <ProductPrice
                        className="shrink-0 [&>*:not(:last-child)]:hidden"
                        productPrice={product.price}
                        textPriceSize="base"
                    />

                    {!product.isInquiryType && (
                        <ProductAvailability
                            availability={product.availability}
                            availableStoresCount={null}
                            className="min-w-0 text-xs [&>span]:truncate"
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </div>
            </div>

            <ProductPrice className="vl:flex hidden shrink-0" productPrice={product.price} textPriceSize="base" />

            <div className="vl:w-40 w-36 shrink-0">
                <DeferredProductDetailAddToCart
                    buttonSize="large"
                    buttonTid={TIDs.product_detail_sticky_addtocart_button}
                    product={product}
                    shouldDisplayAdditionalServices={false}
                    spinboxId={`${product.uuid}-sticky-action`}
                />
            </div>
        </div>
    );
};
