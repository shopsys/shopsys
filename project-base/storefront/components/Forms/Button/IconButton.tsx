import { Tooltip, TooltipPlacement } from 'components/Basic/Tooltip/Tooltip';
import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

export type IconButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'children'> & {
    title: string;
    ariaLabel?: string;
    Icon: SvgFC;
    iconClassName?: string;
    shape?: 'circle' | 'rounded';
    size?: 'compact' | 'small' | 'medium' | 'large';
    tid?: string;
    tooltipLabel?: string;
    tooltipPlacement?: TooltipPlacement;
    variant?: 'default' | 'ghost';
};

export const IconButton = forwardRef<HTMLButtonElement, IconButtonProps>(
    (
        {
            Icon,
            title,
            ariaLabel,
            className,
            disabled,
            iconClassName,
            shape = 'circle',
            size = 'medium',
            tabIndex = 0,
            tid,
            tooltipLabel,
            tooltipPlacement,
            type = 'button',
            variant = 'default',
            ...props
        },
        ref,
    ) => {
        const button = (
            <button
                aria-label={ariaLabel || title}
                className={twMergeCustom(getIconButtonClassName(variant, size, shape), className)}
                data-tid={tid}
                disabled={disabled}
                ref={ref}
                tabIndex={tabIndex}
                title={tooltipLabel ? undefined : title}
                type={type}
                {...props}
            >
                <Icon
                    aria-hidden="true"
                    className={twMergeCustom(
                        size === 'compact' && 'size-4',
                        size !== 'compact' && 'size-6',
                        iconClassName,
                    )}
                />
            </button>
        );
        const tooltipTrigger = disabled ? <span className="inline-flex">{button}</span> : button;

        return tooltipLabel ? (
            <Tooltip label={tooltipLabel} placement={tooltipPlacement}>
                {tooltipTrigger}
            </Tooltip>
        ) : (
            button
        );
    },
);

const getIconButtonClassName = (
    variant: IconButtonProps['variant'],
    size: IconButtonProps['size'],
    shape: IconButtonProps['shape'],
) =>
    twJoin(
        'inline-flex shrink-0 cursor-pointer items-center justify-center outline-hidden transition-[background-color,border-color,color,box-shadow,transform] active:scale-95 disabled:pointer-events-none disabled:scale-100 disabled:cursor-no-drop motion-reduce:transition-none',
        shape === 'circle' && 'rounded-full',
        shape === 'rounded' && 'rounded-md',
        size === 'compact' && 'size-6',
        size === 'small' && 'size-8',
        size === 'medium' && 'size-10',
        size === 'large' && 'size-12',
        variant === 'default' && [
            'border border-icon-button-default-border-default bg-icon-button-default-bg-default/90 text-icon-button-default-icon-default shadow-sm backdrop-blur-xs',
            'hover:bg-icon-button-default-bg-hovered hover:text-icon-button-default-icon-hovered',
            'active:bg-icon-button-default-bg-active active:text-icon-button-default-icon-active',
            'disabled:border-icon-button-default-border-disabled disabled:bg-icon-button-default-bg-disabled disabled:text-icon-button-default-icon-disabled',
        ],
        variant === 'ghost' && [
            'border border-transparent bg-transparent text-icon-button-ghost-icon-default shadow-none',
            'hover:bg-icon-button-ghost-bg-hovered hover:text-icon-button-ghost-icon-hovered',
            'active:bg-icon-button-ghost-bg-active active:text-icon-button-ghost-icon-active',
            'aria-pressed:text-icon-accent',
            'disabled:bg-transparent disabled:text-icon-button-ghost-icon-disabled',
        ],
    );

IconButton.displayName = 'IconButton';
