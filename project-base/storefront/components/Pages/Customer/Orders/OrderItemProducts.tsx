import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CustomerRecordProductImage, CustomerRecordRowInfo } from 'components/Pages/Customer/CustomerRecordElements';
import { TIDs } from 'cypress/tids';
import { TypeOrderItemFragment } from 'graphql/requests/orders/fragments/OrderItemFragment.generated';
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
        <CustomerRecordRowInfo title={t('Products')}>
            {items.slice(0, 4).map((item) => {
                const product = item.product;

                if (!product?.mainImage?.url) {
                    return null;
                }

                return (
                    <CustomerRecordProductImage
                        key={product.link}
                        image={product.mainImage.url}
                        imageAlt={product.mainImage.name ?? ''}
                        isVisible={product.isVisible}
                        link={product.link}
                        quantity={item.quantity}
                        tid={TIDs.order_list_product_image}
                        tooltipLabel={product.name}
                    />
                );
            })}

            {items.length > 4 && (
                <ExtendedNextLink
                    className="flex size-16 items-center justify-center rounded-xl border border-transparent bg-base-white p-2 no-underline transition-all hover:border-border-less"
                    href={orderLink}
                    type="orderDetail"
                >
                    {t('Next')}
                </ExtendedNextLink>
            )}
        </CustomerRecordRowInfo>
    );
};
