import { Button } from 'components/Forms/Button/Button';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

const InquiryPopup = dynamic(
    () => import('components/Blocks/Popup/InquiryPopup').then((component) => component.InquiryPopup),
    {
        ssr: false,
    },
);

type ProductInquiryButtonProps = {
    productUuid: string;
    productName: string;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    skipKeyboardNavigation?: boolean;
    className?: string;
};

export const ProductInquiryButton: FC<ProductInquiryButtonProps> = ({
    productUuid,
    productName,
    buttonSize,
    skipKeyboardNavigation = false,
    className,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const openInquiryPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();
        updatePortalContent(<InquiryPopup productUuid={productUuid} />);
    };

    return (
        <Button
            aria-haspopup="dialog"
            className={twMergeCustom('w-full', className)}
            size={buttonSize}
            tabIndex={skipKeyboardNavigation ? -1 : 0}
            title={t('Inquire popup')}
            aria-label={t('Open inquiry popup for product {{ productName }}', {
                productName: productName,
                ns: 'accessibility',
            })}
            onClick={openInquiryPopup}
        >
            {t('Inquire')}
        </Button>
    );
};
