import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ButtonBaseProps } from 'components/Forms/Button/Button';
import { getButtonHeightClassName } from 'components/Forms/Button/buttonUtils';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';

type ProductActionSkeletonProps = {
    isWithAddToCart?: boolean;
    isWithProductListButtons?: boolean;
    buttonSize?: ButtonBaseProps['size'];
} & FunctionComponentProps;

export const ProductActionSkeleton: FC<ProductActionSkeletonProps> = ({
    className,
    isWithAddToCart = true,
    isWithProductListButtons = true,
    buttonSize = 'medium',
}) => (
    <div
        className={twMergeCustom(
            'grid w-full min-w-0 items-center gap-2',
            isWithAddToCart && isWithProductListButtons && 'grid-cols-[minmax(0,1fr)_2.75rem_2.75rem]',
            isWithAddToCart && !isWithProductListButtons && 'grid-cols-1',
            !isWithAddToCart && isWithProductListButtons && 'grid-cols-[2.75rem_2.75rem]',
            className,
        )}
    >
        {isWithAddToCart && <Skeleton className={twMergeCustom('w-full', getButtonHeightClassName(buttonSize))} />}

        {isWithProductListButtons && (
            <>
                <Skeleton className="size-11" />
                <Skeleton className="size-11" />
            </>
        )}
    </div>
);
