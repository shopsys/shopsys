import { CreateProductReviewPopupProps } from 'components/Blocks/ProductReviews/productReviewTypes';
import { useCallback } from 'react';
import { useSessionStore } from 'store/useSessionStore';

export const useOpenCreateProductReviewPopup = () => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    return useCallback(
        async (popupProps: CreateProductReviewPopupProps) => {
            const { CreateProductReviewPopup } = await import('components/Blocks/Popup/CreateProductReviewPopup');

            updatePortalContent(<CreateProductReviewPopup {...popupProps} />);
        },
        [updatePortalContent],
    );
};
