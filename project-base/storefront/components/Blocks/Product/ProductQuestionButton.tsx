import { ChatIcon } from 'components/Basic/Icon/ChatIcon';
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

    return (
        <button
            aria-haspopup="dialog"
            tabIndex={tabIndex}
            title={t('Ask a product question')}
            aria-label={t('Open the product question form for product {{ productName }}', {
                productName,
                ns: 'accessibility',
            })}
            className={twMergeCustom(
                'flex cursor-pointer items-center gap-2 text-icon-less hover:text-icon-accent',
                'rounded-sm outline-hidden',
                className,
            )}
            onClick={openProductQuestionPopup}
        >
            <ChatIcon className="size-6 shrink-0" />
            {isWithText &&
                (isWithShortText ? (
                    <>
                        <span className="xs:hidden truncate text-sm">{t('Question')}</span>
                        <span className="xs:inline hidden truncate text-sm">{t('Ask a question')}</span>
                    </>
                ) : (
                    <span className="truncate text-sm">{t('Ask a question')}</span>
                ))}
        </button>
    );
};

ProductQuestionButton.displayName = 'ProductQuestionButton';
