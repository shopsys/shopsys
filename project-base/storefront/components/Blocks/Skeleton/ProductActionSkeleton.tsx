import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';

type ProductActionSkeletonProps = {
    isWithAddToCart?: boolean;
    isWithProductListButtons?: boolean;
} & FunctionComponentProps;

export const ProductActionSkeleton: FC<ProductActionSkeletonProps> = ({
    className,
    isWithAddToCart = true,
    isWithProductListButtons = true,
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
        {isWithAddToCart && <Skeleton className="h-9 w-full" />}

        {isWithProductListButtons && (
            <>
                <Skeleton className="size-11" />
                <Skeleton className="size-11" />
            </>
        )}
    </div>
);
