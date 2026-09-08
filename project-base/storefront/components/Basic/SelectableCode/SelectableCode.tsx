import { MouseEvent } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type SelectableCodeProps = {
    value: string;
    label?: string;
    className?: string;
    tabIndex?: number;
};

export const SelectableCode: FC<SelectableCodeProps> = ({ value, label, className, tabIndex }) => {
    const { t } = useTranslation();

    const selectCode = (event: MouseEvent<HTMLButtonElement>) => {
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(event.currentTarget);
        selection?.removeAllRanges();
        selection?.addRange(range);
    };

    return (
        <span className={twMergeCustom('text-text-less', className)}>
            {label && `${label}: `}
            <button
                aria-label={`${t('Select code')}: ${value}`}
                className="cursor-text select-text rounded-sm text-left focus-visible:outline-2 focus-visible:outline-offset-2"
                tabIndex={tabIndex}
                type="button"
                onClick={selectCode}
            >
                {value}
            </button>
        </span>
    );
};
