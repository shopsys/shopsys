import { ProductReviewsSummaryPanel } from 'components/Blocks/ProductReviews/ProductReviewsSummaryPanel';
import { TypeProductReviewsSummaryFragment } from 'graphql/requests/productReviews/fragments/ProductReviewsSummaryFragment.generated';

type ProductReviewsHeaderProps = {
    summary?: TypeProductReviewsSummaryFragment | null;
};

export const ProductReviewsHeader: FC<ProductReviewsHeaderProps> = ({ children, summary }) => {
    if (summary) {
        return <ProductReviewsSummaryPanel summary={summary}>{children}</ProductReviewsSummaryPanel>;
    }

    return children ? (
        <div className="flex flex-col-reverse items-center gap-3 lg:flex-row lg:justify-between">{children}</div>
    ) : null;
};
