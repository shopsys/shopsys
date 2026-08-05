import { ProductReviewItem } from 'components/Blocks/ProductReviews/ProductReviewItem';
import { TypeCustomerUserProductReviewFragment } from 'graphql/requests/productReviews/fragments/CustomerUserProductReviewFragment.generated';
import { TypeProductReviewFragment } from 'graphql/requests/productReviews/fragments/ProductReviewFragment.generated';

type ProductReviewsListProps = {
    isProductNameShown: boolean;
    pendingReviews: TypeCustomerUserProductReviewFragment[];
    reviews: TypeProductReviewFragment[];
};

export const ProductReviewsList: FC<ProductReviewsListProps> = ({ isProductNameShown, pendingReviews, reviews }) => (
    <ul className="m-0 flex list-none flex-col p-0">
        {pendingReviews.map((pendingReview) => (
            <ProductReviewItem
                key={pendingReview.uuid}
                productName={isProductNameShown ? pendingReview.productName : undefined}
                productReview={pendingReview}
                status="awaitingApproval"
            />
        ))}

        {reviews.map((productReview) => (
            <ProductReviewItem
                key={productReview.uuid}
                productName={isProductNameShown ? productReview.productName : undefined}
                productReview={productReview}
                status={productReview.isVerifiedPurchase ? 'verifiedPurchase' : undefined}
            />
        ))}
    </ul>
);
