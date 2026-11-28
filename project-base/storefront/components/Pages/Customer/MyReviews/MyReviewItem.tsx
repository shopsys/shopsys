import { Alert } from 'components/Basic/Alert/Alert';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
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
            arePhotosOnGreyBackground
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
            {productReview.rejectedImagesCount > 0 && (
                <Alert icon={InfoIcon} variant="warning">
                    {t('{{ count }} attached photos were rejected during moderation.', {
                        count: productReview.rejectedImagesCount,
                    })}
                </Alert>
            )}
            {productReview.status === TypeProductReviewStatusEnum.Rejected && productReview.rejectionReason && (
                <Alert title={t('Rejection reason')} variant="error">
                    <p className="m-0 whitespace-pre-line">{productReview.rejectionReason}</p>

                    <p className="m-0 text-text-less text-xs">
                        {t('If you have any comments or questions about reviews, contact us at')}{' '}
                        <Link isExternal href={`mailto:${productReviewContactEmail}`}>
                            {productReviewContactEmail}
                        </Link>
                        .
                    </p>
                </Alert>
            )}
        </ProductReviewItem>
    );
};
