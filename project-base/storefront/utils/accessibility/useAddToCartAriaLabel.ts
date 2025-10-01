import { RefObject, useState } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type UseAddToCartAriaLabelProps = {
    spinboxRef: RefObject<HTMLInputElement | null>;
    productName: string;
    priceWithVat: string;
    unitName: string;
};

export const useAddToCartAriaLabel = ({
    spinboxRef,
    productName,
    priceWithVat,
    unitName,
}: UseAddToCartAriaLabelProps) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const [ariaLabel, setAriaLabel] = useState<string | undefined>(undefined);

    const onFocusHandler = () => {
        if (!spinboxRef.current) {
            return;
        }

        const price = formatPrice(spinboxRef.current.valueAsNumber * mapPriceForCalculations(priceWithVat));
        const priceText = isPriceVisible(price) ? price : undefined;

        const ariaLabel =
            t('Add to cart {{ productName }}, quantity {{ quantity }} {{ unit }}', {
                ns: 'accessibility',
                productName: productName,
                quantity: spinboxRef.current.valueAsNumber,
                unit: unitName,
            }) + (priceText ? t(' for {{ price }}', { ns: 'accessibility', price: priceText }) : '');

        setAriaLabel(ariaLabel);
    };

    return { ariaLabel, onFocusHandler };
};
