import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeOrderItemFragment } from 'graphql/requests/orders/fragments/OrderItemFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type OrderItemProductsProps = {
    items: TypeOrderItemFragment[];
    orderLink: {
        pathname: string;
        query: {
            orderNumber: string;
        };
    };
};

export const OrderItemProducts = ({ items, orderLink }: OrderItemProductsProps) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-wrap vl:justify-start gap-3">
            {items.slice(0, 4).map((item) => {
                const product = item.product;
                const quantity = item.quantity;
                const isVisible = product?.isVisible;

                if (!product?.mainImage?.url) {
                    return null;
                }

                return (
                    <div
                        key={product.link}
                        data-tid={TIDs.order_list_product_image}
                        className={twJoin(
                            'relative size-16 rounded-xl border border-transparent bg-base-white p-2 transition-all',
                            isVisible && 'hover:border-border-less',
                        )}
                    >
                        {isVisible ? (
                            <ExtendedNextLink key={product.link} href={product.link} type="product">
                                <Image
                                    alt={product.mainImage.name ?? ''}
                                    className="size-12 object-contain mix-blend-multiply"
                                    height={96}
                                    src={product.mainImage.url}
                                    width={96}
                                />
                            </ExtendedNextLink>
                        ) : (
                            <Image
                                alt={product.mainImage.name ?? ''}
                                className="size-12 object-contain mix-blend-multiply"
                                height={96}
                                src={product.mainImage.url}
                                width={96}
                            />
                        )}

                        {quantity > 1 && (
                            <span className="absolute -top-2 -right-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-icon-accent-brand-less px-0.5 font-semibold text-text-inverted text-xs">
                                {quantity}
                            </span>
                        )}
                    </div>
                );
            })}

            {items.length > 4 && (
                <ExtendedNextLink
                    className="flex size-16 items-center justify-center rounded-xl bg-base-white p-2 no-underline"
                    href={orderLink}
                    type="orderDetail"
                >
                    {t('Next')}
                </ExtendedNextLink>
            )}
        </div>
    );
};
