import { CartIcon } from 'components/Basic/Icon/CartIcon';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCart: FC = () => {
    return (
        <div className="flex order-3 vl:order-4">
            <Skeleton
                className="h-12 w-24 bg-primaryLight lg:bg-orangeLight"
                containerClassName="h-12 w-24 hidden lg:flex items-center"
            />
            <div className="flex items-center justify-center text-lg lg:hidden p-3">
                <CartIcon className="w-6 text-white hover:text-white" />
            </div>
        </div>
    );
};
