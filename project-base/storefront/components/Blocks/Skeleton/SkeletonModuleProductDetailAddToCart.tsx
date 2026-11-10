import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ButtonBaseProps } from 'components/Forms/Button/Button';
import { twJoin } from 'tailwind-merge';

type SkeletonModuleProductDetailAddToCartProps = Pick<ButtonBaseProps, 'size'> & {
    isFullWidth?: boolean;
};

export const SkeletonModuleProductDetailAddToCart: FC<SkeletonModuleProductDetailAddToCartProps> = ({
    size = 'xlarge',
    isFullWidth = false,
}) => (
    <Skeleton
        className={twJoin(
            'w-full',
            !isFullWidth && 'sm:max-w-60',
            size === 'small' && 'h-9',
            size === 'medium' && 'h-9',
            size === 'large' && 'h-9 sm:h-10',
            size === 'xlarge' && 'h-14',
        )}
    />
);
