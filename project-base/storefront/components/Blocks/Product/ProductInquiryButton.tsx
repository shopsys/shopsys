import { Button } from 'components/Forms/Button/Button';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const InquiryPopup = dynamic(
    () => import('components/Blocks/Popup/InquiryPopup').then((component) => component.InquiryPopup),
    {
        ssr: false,
    },
);

type ProductInquiryButtonProps = {
    productUuid: string;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    skipKeyboardNavigation?: boolean;
    className?: string;
};

export const ProductInquiryButton: FC<ProductInquiryButtonProps> = ({
    productUuid,
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
            aria-label={t('Open inquiry popup', { ns: 'accessibility' })}
            className={twJoin(className)}
            size={buttonSize}
            tabIndex={skipKeyboardNavigation ? -1 : 0}
            title={t('Inquire popup')}
            onClick={openInquiryPopup}
        >
            {t('Inquire')}
        </Button>
    );
};
