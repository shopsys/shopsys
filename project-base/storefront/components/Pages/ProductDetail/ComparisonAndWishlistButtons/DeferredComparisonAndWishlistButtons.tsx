import { SkeletonModuleComparisonAndWishlistButtons } from 'components/Blocks/Skeleton/SkeletonModuleComparisonAndWishlistButtons';
import { ComparisonAndWishlistButtonsProps } from 'components/Pages/ProductDetail/ComparisonAndWishlistButtons/ComparisonAndWishlistButtons';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const ComparisonAndWishlistButtons = dynamic(
    () => import('./ComparisonAndWishlistButtons').then((component) => component.ComparisonAndWishlistButtons),
    {
        ssr: false,
        loading: () => <SkeletonModuleComparisonAndWishlistButtons />,
    },
);

export const DeferredComparisonAndWishlistButtons: FC<ComparisonAndWishlistButtonsProps> = ({ product }) => {
    const shouldRender = useDeferredRender('comparison_and_wishlist_button');

    return shouldRender ? (
        <ComparisonAndWishlistButtons product={product} />
    ) : (
        <SkeletonModuleComparisonAndWishlistButtons />
    );
};
