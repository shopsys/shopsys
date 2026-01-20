import { cloneElement, ReactElement, useCallback, useRef, useState } from 'react';

type Placement = 'top' | 'bottom' | 'left' | 'right';

type TooltipProps = {
    label?: string;
    placement?: Placement;
    children: ReactElement<any>;
};

const OFFSET = 8;

const getTooltipPosition = (
    triggerRect: DOMRect,
    tooltipRect: DOMRect,
    placement: Placement,
): { top: number; left: number } => {
    switch (placement) {
        case 'bottom':
            return {
                top: triggerRect.bottom + OFFSET,
                left: triggerRect.left + (triggerRect.width - tooltipRect.width) / 2,
            };
        case 'left':
            return {
                top: triggerRect.top + (triggerRect.height - tooltipRect.height) / 2,
                left: triggerRect.left - tooltipRect.width - OFFSET,
            };
        case 'right':
            return {
                top: triggerRect.top + (triggerRect.height - tooltipRect.height) / 2,
                left: triggerRect.right + OFFSET,
            };
        case 'top':
        default:
            return {
                top: triggerRect.top - tooltipRect.height - OFFSET,
                left: triggerRect.left + (triggerRect.width - tooltipRect.width) / 2,
            };
    }
};

export const Tooltip: FC<TooltipProps> = ({ children, label, placement = 'top' }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [position, setPosition] = useState({ top: 0, left: 0 });
    const triggerRef = useRef<HTMLElement>(null);
    const tooltipRef = useRef<HTMLDivElement>(null);

    const updatePosition = useCallback(() => {
        if (!triggerRef.current || !tooltipRef.current) {
            return;
        }

        const triggerRect = triggerRef.current.getBoundingClientRect();
        const tooltipRect = tooltipRef.current.getBoundingClientRect();
        const newPosition = getTooltipPosition(triggerRect, tooltipRect, placement);

        setPosition(newPosition);
    }, [placement]);

    const handleOpen = useCallback(() => {
        setIsOpen(true);
        requestAnimationFrame(updatePosition);
    }, [updatePosition]);

    const handleClose = useCallback(() => {
        setIsOpen(false);
    }, []);

    const childProps = {
        ...children.props,
        ref: triggerRef,
        onMouseEnter: handleOpen,
        onMouseLeave: handleClose,
        onFocus: handleOpen,
        onBlur: handleClose,
    };

    return (
        <>
            {cloneElement(children, childProps)}
            {isOpen && label && (
                <div
                    className="tooltip bg-background-most fixed z-50 block rounded-md p-2 text-sm"
                    ref={tooltipRef}
                    role="tooltip"
                    style={{ top: position.top, left: position.left }}
                >
                    {label}
                </div>
            )}
        </>
    );
};
