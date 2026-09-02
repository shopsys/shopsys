import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { StatusBadge } from 'components/Basic/StatusBadge/StatusBadge';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { ProductReviewDisplayStatus } from 'components/Blocks/ProductReviews/productReviewTypes';
import { ReviewStatus } from 'components/Blocks/ProductReviews/ReviewStatus';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductReviewStatusProps = {
    status: ProductReviewDisplayStatus;
};

export const ProductReviewStatus: FC<ProductReviewStatusProps> = ({ status }) => {
    const { t } = useTranslation();
    const isVerifiedPurchase = status === 'verifiedPurchase';

    if (!isVerifiedPurchase) {
        return (
            <Tooltip
                label={t('Your review is awaiting approval. Until it is approved, only you can see it.')}
                placement="top"
            >
                <button className="cursor-help rounded-lg" type="button">
                    <ReviewStatus status={TypeProductReviewStatusEnum.Pending} />
                </button>
            </Tooltip>
        );
    }

    return (
        <StatusBadge icon={CheckmarkIcon} variant="success">
            {t('Verified purchase')}
        </StatusBadge>
    );
};
