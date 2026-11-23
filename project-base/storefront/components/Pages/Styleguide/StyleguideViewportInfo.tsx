import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { useEffect, useState } from 'react';
import { mobileFirstSizes } from 'utils/mediaQueries';

const COARSE_POINTER_QUERY = '(pointer: coarse)';

type ViewportInfo = {
    width: number;
    breakpointName: string | undefined;
    breakpointMinWidth: number | undefined;
    isCoarsePointer: boolean;
};

const getViewportInfo = (): ViewportInfo => {
    const width = window.innerWidth;
    const matchingBreakpoints = Object.entries(mobileFirstSizes).filter(([, minWidth]) => width >= minWidth);
    const [breakpointName, breakpointMinWidth] = matchingBreakpoints.at(-1) ?? [];

    return {
        width,
        breakpointName,
        breakpointMinWidth,
        isCoarsePointer: window.matchMedia(COARSE_POINTER_QUERY).matches,
    };
};

const getBreakpointHint = ({ breakpointMinWidth, breakpointName }: ViewportInfo) =>
    breakpointName ? `styles from ${breakpointMinWidth} px apply` : 'below every breakpoint';

const getInputHint = ({ isCoarsePointer }: ViewportInfo) => (isCoarsePointer ? 'coarse pointer' : 'fine pointer');

type ViewportValueProps = {
    label: string;
    value: string;
    hint: string;
};

const ViewportValue: FC<ViewportValueProps> = ({ hint, label, value }) => (
    <div className="flex flex-col gap-0.5 rounded-control bg-background-more px-3 py-2.5">
        <dt className="font-semibold text-text-less text-xs uppercase tracking-wider">{label}</dt>
        <dd className="flex flex-col">
            <span className="font-heading text-lg tabular-nums">{value}</span>
            <span className="text-text-less text-xs">{hint}</span>
        </dd>
    </div>
);

/**
 * Shows the window the styleguide is tested in, so responsive sizes can be compared with the breakpoint they belong to.
 */
export const StyleguideViewportInfo: FC = () => {
    const [viewportInfo, setViewportInfo] = useState<ViewportInfo>();

    useEffect(() => {
        const updateViewportInfo = () => setViewportInfo(getViewportInfo());
        const coarsePointerQuery = window.matchMedia(COARSE_POINTER_QUERY);

        updateViewportInfo();
        window.addEventListener('resize', updateViewportInfo);
        coarsePointerQuery.addEventListener('change', updateViewportInfo);

        return () => {
            window.removeEventListener('resize', updateViewportInfo);
            coarsePointerQuery.removeEventListener('change', updateViewportInfo);
        };
    }, []);

    return (
        <section
            aria-label="Current viewport"
            className="flex w-full max-w-2xl flex-col gap-4 rounded-card border border-border-less bg-background-default p-4 vl:p-5 text-sm"
        >
            <div className="flex items-start gap-3">
                <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-background-accent-less text-icon-accent">
                    <InfoIcon aria-hidden className="size-5" />
                </span>
                <div className="flex flex-col gap-0.5">
                    <p className="font-heading text-base">Current viewport</p>
                    <p className="text-text-less">
                        Components change their size at breakpoints. Resize the window and compare these values with the
                        size tables of the sections.
                    </p>
                </div>
            </div>

            <dl className="grid grid-cols-1 gap-2 sm:grid-cols-3">
                <ViewportValue
                    hint="window width"
                    label="Width"
                    value={viewportInfo ? `${viewportInfo.width} px` : '–'}
                />
                <ViewportValue
                    hint={viewportInfo ? getBreakpointHint(viewportInfo) : '–'}
                    label="Breakpoint"
                    value={viewportInfo?.breakpointName ?? '–'}
                />
                <ViewportValue
                    hint={viewportInfo ? getInputHint(viewportInfo) : '–'}
                    label="Input"
                    value={viewportInfo ? (viewportInfo.isCoarsePointer ? 'Touch' : 'Mouse') : '–'}
                />
            </dl>
        </section>
    );
};
