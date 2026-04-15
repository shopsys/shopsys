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
                'rounded-md p-0.5 text-icon-less outline-hidden hover:cursor-pointer hover:text-icon-default',
                disabled && 'pointer-events-none text-input-border-disabled',
                className,
            )}
            onClick={onClick}
        >
            <Icon className="size-6" />
        </button>
    );
};
