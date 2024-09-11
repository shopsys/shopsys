import { ProductFlag } from './ProductFlag';
import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { twMergeCustom } from 'utils/twMerge';

type ProductFlagsProps = { flags: TypeSimpleFlagFragment[]; variant: 'list' | 'detail' | 'comparison' | 'bestsellers' };

export const ProductFlags: FC<ProductFlagsProps> = ({ flags, variant }) => {
    if (!flags.length) {
        return null;
    }

    const variantTwClass = {
        list: 'top-5 right-2.5 sm:right-5 items-end z-above',
        detail: 'top-3 left-4',
        comparison: 'top-3 left-0',
        bestsellers: 'flex-row relative flex-wrap mb-3 gap-2',
    };

    return (
        <div className={twMergeCustom('absolute flex flex-col items-start gap-1', variantTwClass[variant])}>
            {flags.map(({ name, rgbColor }, index) => (
                <ProductFlag key={index} name={name} rgbColor={rgbColor} />
            ))}
        </div>
    );
};
