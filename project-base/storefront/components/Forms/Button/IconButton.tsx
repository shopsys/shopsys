import { twMergeCustom } from 'utils/twMerge';

type IconButtonProps = {
    onClick: () => void;
    title: string;
    ariaLabel?: string;
    tabIndex?: number;
    Icon: SvgFC;
    className?: string;
    tid?: string;
    disabled?: boolean;
    buttonRef?: React.RefObject<HTMLButtonElement | null>;
};

export const IconButton: FC<IconButtonProps> = ({
    Icon,
    onClick,
    title,
    ariaLabel,
    tabIndex = 0,
    className,
    tid,
    disabled,
    buttonRef,
}) => {
    return (
        <button
            aria-label={ariaLabel}
            data-tid={tid}
            disabled={disabled}
            ref={buttonRef}
            tabIndex={tabIndex}
            title={title}
            className={twMergeCustom(
                'text-icon-less hover:text-icon-default rounded-md p-0.5 outline-none hover:cursor-pointer',
                disabled && 'text-input-border-disabled pointer-events-none',
                className,
            )}
            onClick={onClick}
        >
            <Icon className="size-6" />
        </button>
    );
};
