import { twJoin } from 'tailwind-merge';
import type { ButtonBaseProps } from './Button';

export const getButtonIconClassName = (size: ButtonBaseProps['size']) =>
    twJoin(
        size === 'small' && 'size-4',
        size === 'medium' && 'size-4',
        size === 'large' && 'size-5',
        size === 'xlarge' && 'size-6',
    );

/**
 * Rendered height of a single-line button of the given size, for placeholders and controls that stand in for it.
 */
export const getButtonHeightClassName = (size: ButtonBaseProps['size']) =>
    twJoin(
        size === 'small' && 'h-9',
        size === 'medium' && 'h-10',
        size === 'large' && 'h-11 vl:h-12',
        size === 'xlarge' && 'h-11 vl:h-14',
    );

export const getButtonClassName = (
    variant: ButtonBaseProps['variant'],
    size: ButtonBaseProps['size'],
    hasDisabledLook: ButtonBaseProps['hasDisabledLook'],
) => {
    return twJoin(
        'inline-flex h-fit w-auto cursor-pointer items-center justify-center gap-2 rounded-button text-center font-secondary font-semibold transition-all hover:no-underline',
        // the neutral outline button is framed by a 1px line, the other variants keep their 2px outline
        variant === 'outline' ? 'outline-1 -outline-offset-1' : 'outline-2 -outline-offset-2',
        size === 'small' && 'px-3 py-2 text-sm',
        size === 'medium' && 'px-4 py-2.5 text-sm',
        size === 'large' && 'px-4 py-2.5 vl:py-3 text-base',
        size === 'xlarge' && 'px-4 vl:px-5 py-2.5 vl:py-3.5 text-base vl:text-lg',
        variant === 'primary' && [
            'bg-button-primary-bg-default text-button-primary-text-default outline-button-primary-border-default',
            !hasDisabledLook &&
                'hover:bg-button-primary-bg-hovered hover:text-button-primary-text-hovered hover:outline-button-primary-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-primary-bg-active active:text-button-primary-text-active active:outline-button-primary-border-active',
            hasDisabledLook &&
                'bg-button-primary-bg-disabled text-button-primary-text-disabled outline-button-primary-border-disabled',
        ],
        variant === 'secondary' && [
            'bg-button-secondary-bg-default text-button-secondary-text-default outline-button-secondary-border-default',
            !hasDisabledLook &&
                'hover:bg-button-secondary-bg-hovered hover:text-button-secondary-text-hovered hover:outline-button-secondary-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-secondary-bg-active active:text-button-secondary-text-active active:outline-button-secondary-border-active',
            hasDisabledLook &&
                'bg-button-secondary-bg-disabled text-button-secondary-text-disabled outline-button-secondary-border-disabled',
        ],
        variant === 'outline' && [
            'bg-button-outline-bg-default text-button-outline-text-default outline-button-outline-border-default',
            !hasDisabledLook &&
                'hover:bg-button-outline-bg-hovered hover:text-button-outline-text-hovered hover:outline-button-outline-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-outline-bg-active active:text-button-outline-text-active active:outline-button-outline-border-active',
            hasDisabledLook &&
                'bg-button-outline-bg-disabled text-button-outline-text-disabled outline-button-outline-border-disabled',
        ],
        variant === 'ghost' && [
            'bg-button-ghost-bg-default text-button-ghost-text-default outline-button-ghost-border-default',
            !hasDisabledLook &&
                'hover:bg-button-ghost-bg-hovered hover:text-button-ghost-text-hovered hover:outline-button-ghost-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-ghost-bg-active active:text-button-ghost-text-active active:outline-button-ghost-border-active',
            hasDisabledLook &&
                'bg-button-ghost-bg-disabled text-button-ghost-text-disabled outline-button-ghost-border-disabled',
        ],
        variant === 'danger' && [
            'bg-button-danger-bg-default text-button-danger-text-default outline-button-danger-border-default',
            !hasDisabledLook &&
                'hover:bg-button-danger-bg-hovered hover:text-button-danger-text-hovered hover:outline-button-danger-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-danger-bg-active active:text-button-danger-text-active active:outline-button-danger-border-active',
            hasDisabledLook &&
                'bg-button-danger-bg-disabled text-button-danger-text-disabled outline-button-danger-border-disabled',
        ],
        variant === 'inverted' && [
            'bg-button-inverted-bg-default text-button-inverted-text-default outline-button-inverted-border-default',
            !hasDisabledLook &&
                'hover:bg-button-inverted-bg-hovered hover:text-button-inverted-text-hovered hover:outline-button-inverted-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-inverted-bg-active active:text-button-inverted-text-active active:outline-button-inverted-border-active',
            hasDisabledLook &&
                'bg-button-inverted-bg-disabled text-button-inverted-text-disabled outline-button-inverted-border-disabled',
        ],
        // The disabled cursor belongs only to controls that cannot be used; a clickable button keeps the pointer.
        'disabled:cursor-no-drop',
    );
};
