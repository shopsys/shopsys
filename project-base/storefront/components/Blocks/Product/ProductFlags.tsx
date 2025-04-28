import { ProductVisibleItemsConfigType } from './ProductsList/ProductListItem';
import { Flag } from 'components/Basic/Flag/Flag';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

type ProductFlagsProps = {
    flags: TypeSimpleFlagFragment[];
    percentageDiscount: number | null;
    variant: 'list' | 'detail' | 'comparison' | 'bestsellers';
    visibleItemsConfig?: ProductVisibleItemsConfigType;
};

export const ProductFlags: FC<ProductFlagsProps> = ({
    flags,
    percentageDiscount,
    variant,
    visibleItemsConfig = { flags: true, discount: false },
}) => {
    const { t } = useTranslation();

    if (
        (!visibleItemsConfig.flags && !visibleItemsConfig.discount) ||
        (!flags.length && !percentageDiscount) ||
        (!visibleItemsConfig.flags && !percentageDiscount) ||
        (!visibleItemsConfig.discount && !flags.length)
    ) {
        return null;
    }

    const variantTwClass = {
        list: 'top-5 right-2.5 sm:right-5 items-end z-above',
        detail: 'top-3 right-0 items-end',
        comparison: 'top-3 left-0',
        bestsellers: 'flex-row relative flex-wrap mb-1 gap-2',
    };

    return (
        <div className={twMergeCustom('absolute flex flex-col items-start gap-1', variantTwClass[variant])}>
            {visibleItemsConfig.flags &&
                flags.length > 0 &&
                flags.map(({ name, rgbColor }, index) => {
                    return (
                        <Flag key={index} rgbBgColor={rgbColor}>
                            {name}
                        </Flag>
                    );
                })}

            {visibleItemsConfig.discount &&
                !!percentageDiscount &&
                percentageDiscount > 0 &&
                percentageDiscount < 100 && (
                    <Flag type="discount">
                        -{percentageDiscount}% {t('disount')}
                    </Flag>
                )}
        </div>
    );
};
