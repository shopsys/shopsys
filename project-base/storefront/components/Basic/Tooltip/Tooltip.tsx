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
} from '@floating-ui/react';
import { cloneElement, useMemo, useState } from 'react';
import { mergeRefs } from 'react-merge-refs';

type TooltipProps = {
    label?: string;
    placement?: Placement;
    children: JSX.Element;
};

export const Tooltip: FC<TooltipProps> = ({ children, label, placement = 'top' }) => {
    const [isOpen, setIsOpen] = useState(false);

    const { x, y, refs, context, strategy } = useFloating({
        placement,
        open: isOpen,
        onOpenChange: setIsOpen,
        middleware: [offset(5), flip(), shift({ padding: 8 })],
        whileElementsMounted: autoUpdate,
    });

    const { getReferenceProps, getFloatingProps } = useInteractions([
        useHover(context),
        useFocus(context),
        useRole(context, { role: 'tooltip' }),
        useDismiss(context),
    ]);
    const ref = useMemo(() => mergeRefs([refs.setReference, (children as any).ref]), [refs.setReference, children]);

    return (
        <>
            {cloneElement(children, getReferenceProps({ ref, ...children.props }))}
            {isOpen && label && (
                <div
                    ref={refs.setFloating}
                    {...getFloatingProps({
                        className: 'tooltip block rounded-md bg-black bg-opacity-75 p-2 text-white',
                        style: {
                            position: strategy,
                            top: y,
                            left: x,
                        },
                    })}
                >
                    {label}
                </div>
            )}
        </>
    );
};
