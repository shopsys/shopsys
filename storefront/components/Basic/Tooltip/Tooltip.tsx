import { TooltipStyled, TooltipWrapperStyled } from './Tooltip.style';
import {
    autoUpdate,
    flip,
    offset,
    Placement,
    shift,
    useDismiss,
    useFloating,
    useFocus,
    useHover,
    useInteractions,
    useRole,
} from '@floating-ui/react-dom-interactions';
import { cloneElement, FC, useMemo, useState } from 'react';
import { mergeRefs } from 'react-merge-refs';

type TooltipProps = {
    label?: string;
    placement?: Placement;
    children: JSX.Element;
};

export const Tooltip: FC<TooltipProps> = ({ children, label, placement = 'top' }) => {
    const [open, setOpen] = useState(false);

    const { x, y, reference, floating, strategy, context } = useFloating({
        placement,
        open,
        onOpenChange: setOpen,
        middleware: [offset(5), flip(), shift({ padding: 8 })],
        whileElementsMounted: autoUpdate,
    });

    const { getReferenceProps, getFloatingProps } = useInteractions([
        useHover(context),
        useFocus(context),
        useRole(context, { role: 'tooltip' }),
        useDismiss(context),
    ]);

    const ref = useMemo(() => mergeRefs([reference, (children as any).ref]), [reference, children]);

    return (
        <>
            {cloneElement(children, getReferenceProps({ ref, ...children.props }))}
            {open && label !== undefined && (
                <TooltipWrapperStyled>
                    <TooltipStyled
                        {...getFloatingProps({
                            ref: floating,
                            className: 'tooltip',
                            style: {
                                position: strategy,
                                top: y ?? 0,
                                left: x ?? 0,
                            },
                        })}
                    >
                        {label}
                    </TooltipStyled>
                </TooltipWrapperStyled>
            )}
        </>
    );
};
