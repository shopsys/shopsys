import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ButtonBaseProps } from 'components/Forms/Button/Button';
import { twJoin } from 'tailwind-merge';

type SkeletonModuleProductDetailAddToCartProps = Pick<ButtonBaseProps, 'size'>;

export const SkeletonModuleProductDetailAddToCart: FC<SkeletonModuleProductDetailAddToCartProps> = ({
    size = 'xlarge',
}) => (
    <Skeleton
        className={twJoin(
            'w-full',
            size === 'small' && 'h-9',
            size === 'medium' && 'h-9',
            size === 'large' && 'h-9 sm:h-10',
            size === 'xlarge' && 'h-10 sm:h-14',
        )}
    />
);
