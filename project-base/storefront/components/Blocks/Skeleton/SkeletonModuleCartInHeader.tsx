import { CartIcon } from 'components/Basic/Icon/CartIcon';
import Skeleton from 'react-loading-skeleton';
import { twJoin } from 'tailwind-merge';

export const SkeletonModuleCartInHeader: FC = () => {
    return (
        <div className="flex order-3 vl:order-4">
            <Skeleton
                className={twJoin('h-12 w-24 border', 'bg-actionPrimaryBackground border-actionPrimaryBorder')}
                containerClassName="h-12 w-24 hidden lg:flex items-center"
            />
            <div
                className={twJoin(
                    'flex h-full w-full items-center justify-center p-3 border rounded-lg lg:hidden',
                    'bg-actionPrimaryBackground text-actionPrimaryText border-actionPrimaryBorder',
                    'hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered hover:border-actionPrimaryBorderHovered',
                    'active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive active:border-actionPrimaryBorderActive',
                )}
            >
                <CartIcon className="w-6" />
            </div>
        </div>
    );
};
