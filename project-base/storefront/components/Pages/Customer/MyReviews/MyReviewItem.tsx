import { Link } from 'components/Basic/Link/Link';
import { ProductReviewItem } from 'components/Blocks/ProductReviews/ProductReviewItem';
import { getProductReviewHtmlId } from 'components/Blocks/ProductReviews/productReviewUtils';
import { CustomerRecordProductImage } from 'components/Pages/Customer/CustomerRecordElements';
import { TypeCustomerUserProductReviewFragment } from 'graphql/requests/productReviews/fragments/CustomerUserProductReviewFragment.generated';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type MyReviewItemProps = {
    productReview: TypeCustomerUserProductReviewFragment;
};

export const MyReviewItem: FC<MyReviewItemProps> = ({ productReview }) => {
    const { t } = useTranslation();
    const productReviewContactEmail = t('reviews@shopsys.cz');
    const productFullName = productReview.product?.fullName ?? productReview.productName;
    const reviewSubjectLink = productReview.product?.isVisible
        ? {
              'aria-label': t('Go to product {{ productName }}', {
                  ns: 'accessibility',
                  productName: productFullName,
              }),
              href: productReview.product.slug,
              type: 'product' as const,
          }
        : undefined;

    return (
        <ProductReviewItem
            className="scroll-mt-fixed-header"
            id={getProductReviewHtmlId(productReview.uuid)}
            leadingContent={
                <CustomerRecordProductImage image={productReview.product?.mainImage?.url} imageAlt={productFullName} />
            }
            productReview={productReview}
            reviewSubjectLink={reviewSubjectLink}
            reviewTitle={productFullName}
            reviewStatus={productReview.status}
            status={productReview.isVerifiedPurchase ? 'verifiedPurchase' : undefined}
        >
            {productReview.status === TypeProductReviewStatusEnum.Rejected && productReview.rejectionReason && (
                <div className="flex flex-col gap-1 rounded-md border border-toast-border-error bg-toast-bg-error p-3">
                    <p className="m-0 font-semibold text-sm text-toast-text-error">{t('Rejection reason')}</p>
                    <p className="m-0 whitespace-pre-line text-sm">{productReview.rejectionReason}</p>

                    <p className="m-0 text-text-less text-xs">
                        {t('If you have any comments or questions about reviews, contact us at')}{' '}
                        <Link isExternal href={`mailto:${productReviewContactEmail}`} className="text-xs">
                            {productReviewContactEmail}
                        </Link>
                        .
                    </p>
                </div>
            )}
        </ProductReviewItem>
    );
};
