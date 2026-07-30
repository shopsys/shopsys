import * as TooltipPrimitive from '@radix-ui/react-tooltip';
import { ReactElement, useEffect, useState } from 'react';

export type TooltipPlacement = 'top' | 'bottom' | 'left' | 'right';

type TooltipProps = {
    label?: string;
    placement?: TooltipPlacement;
    children: ReactElement;
    disabled?: boolean;
};

const OFFSET = 8;
const OPEN_DELAY_MS = 250;
const SKIP_DELAY_MS = 300;

export const Tooltip: FC<TooltipProps> = ({ children, label, placement = 'top', disabled = false }) => {
    const [isOpen, setIsOpen] = useState(false);

    useEffect(() => {
        if (disabled) {
            setIsOpen(false);
        }
    }, [disabled]);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        const handleClose = () => setIsOpen(false);
        window.addEventListener('blur', handleClose);
        document.addEventListener('visibilitychange', handleClose);

        return () => {
            window.removeEventListener('blur', handleClose);
            document.removeEventListener('visibilitychange', handleClose);
        };
    }, [isOpen]);

    if (!label) {
        return children;
    }

    return (
        <TooltipPrimitive.Root
            open={!disabled && isOpen}
            onOpenChange={(nextIsOpen) => setIsOpen(!disabled && nextIsOpen)}
        >
            <TooltipPrimitive.Trigger asChild>{children}</TooltipPrimitive.Trigger>

            <TooltipPrimitive.Portal>
                <TooltipPrimitive.Content
                    className="tooltip pointer-events-none z-maximum block origin-(--radix-tooltip-content-transform-origin) scale-100 rounded-md bg-background-dark px-2 py-1 text-text-inverted text-xs leading-4 opacity-100 shadow-sm motion-safe:starting:scale-[0.96] motion-safe:starting:opacity-0 motion-safe:transition-[opacity,scale] motion-safe:duration-140 motion-safe:ease-out"
                    side={placement}
                    sideOffset={OFFSET}
                >
                    {label}
                </TooltipPrimitive.Content>
            </TooltipPrimitive.Portal>
        </TooltipPrimitive.Root>
    );
};

export const TooltipProvider: FC = ({ children }) => (
    <TooltipPrimitive.Provider disableHoverableContent delayDuration={OPEN_DELAY_MS} skipDelayDuration={SKIP_DELAY_MS}>
        {children}
    </TooltipPrimitive.Provider>
);
