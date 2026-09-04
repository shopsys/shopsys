import { Image } from 'components/Basic/Image/Image';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { Select } from 'components/Forms/Select/Select';
import { TIDs } from 'cypress/tids';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { DeliveryOptionsProduct } from './deliveryOptionsPopupTypes';

type DeliveryOptionsVariantSelectProps = {
    products: DeliveryOptionsProduct[];
    selectedProduct: DeliveryOptionsProduct | null;
    onSelectProduct: (productUuid: string) => void;
};

export const DeliveryOptionsVariantSelect: FC<DeliveryOptionsVariantSelectProps> = ({
    products,
    selectedProduct,
    onSelectProduct,
}) => {
    const { t } = useTranslation();
    const options = products.map((product) => ({ label: product.fullName, value: product }));
    const activeOption = options.find((option) => option.value.uuid === selectedProduct?.uuid) ?? null;

    return (
        <Select
            activeOption={activeOption}
            ariaLabel={t('Choose a variant')}
            label={t('Choose a variant')}
            options={options}
            renderValue={({ value }) => <DeliveryOptionsVariantContent product={value} />}
            selectClassName="h-auto min-h-14"
            tid={TIDs.delivery_options_variant_select}
            renderOption={({ value }) => (
                <DeliveryOptionsVariantContent
                    product={value}
                    tid={TIDs.delivery_options_variant_option_ + value.uuid}
                />
            )}
            onSelectOption={({ value }) => onSelectProduct(value.uuid)}
        />
    );
};

const DeliveryOptionsVariantContent: FC<{ product: DeliveryOptionsProduct; tid?: string }> = ({ product, tid }) => {
    const formatPrice = useFormatPrice();

    return (
        <span className="flex min-w-0 flex-1 items-center gap-3" data-tid={tid}>
            <span className="relative h-10 w-10 shrink-0" data-tid={TIDs.delivery_options_variant_image}>
                <Image
                    fill
                    alt={product.mainImage?.name ?? product.fullName}
                    className="object-contain mix-blend-multiply"
                    sizes="40px"
                    src={product.mainImage?.url}
                />
            </span>

            <span className="flex min-w-0 flex-col">
                <span className="line-clamp-2 text-sm">{product.fullName}</span>

                <ProductAvailability
                    availability={product.availability}
                    availableStoresCount={null}
                    displayMode="compact"
                    isInquiryType={false}
                />
            </span>

            {isPriceVisible(product.price.priceWithVat) && (
                <span className="ml-auto shrink-0 pr-2 font-secondary font-semibold text-sm">
                    {formatPrice(product.price.priceWithVat)}
                </span>
            )}
        </span>
    );
};
