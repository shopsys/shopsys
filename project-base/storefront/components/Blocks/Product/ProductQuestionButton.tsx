import { ChatIcon } from 'components/Basic/Icon/ChatIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

const ProductQuestionPopup = dynamic(
    () => import('components/Blocks/Popup/ProductQuestionPopup').then((component) => component.ProductQuestionPopup),
    {
        ssr: false,
    },
);

type ProductQuestionButtonProps = {
    productUuid: string;
    productName: string;
    isWithText?: boolean;
    isWithShortText?: boolean;
    tabIndex?: number;
};

export const ProductQuestionButton: FC<ProductQuestionButtonProps> = ({
    className,
    productUuid,
    productName,
    isWithText,
    isWithShortText,
    tabIndex = 0,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const openProductQuestionPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();
        updatePortalContent(<ProductQuestionPopup productUuid={productUuid} />);
    };
    const title = t('Ask a product question');
    const ariaLabel = t('Open the product question form for product {{ productName }}', {
        productName,
        ns: 'accessibility',
    });

    if (!isWithText) {
        return (
            <IconButton
                Icon={ChatIcon}
                aria-haspopup="dialog"
                ariaLabel={ariaLabel}
                className={className}
                shape="rounded"
                tabIndex={tabIndex}
                title={title}
                tooltipLabel={title}
                variant="ghost"
                onClick={openProductQuestionPopup}
            />
        );
    }

    return (
        <button
            aria-haspopup="dialog"
            aria-label={ariaLabel}
            tabIndex={tabIndex}
            title={title}
            className={twMergeCustom(
                'flex cursor-pointer items-center gap-2 text-icon-less hover:text-icon-accent',
                'rounded-sm outline-hidden',
                className,
            )}
            onClick={openProductQuestionPopup}
        >
            <ChatIcon className="size-6 shrink-0" />
            {isWithShortText ? (
                <>
                    <span className="xs:hidden truncate text-sm">{t('Question')}</span>
                    <span className="xs:inline hidden truncate text-sm">{t('Ask a question')}</span>
                </>
            ) : (
                <span className="truncate text-sm">{t('Ask a question')}</span>
            )}
        </button>
    );
};

ProductQuestionButton.displayName = 'ProductQuestionButton';
