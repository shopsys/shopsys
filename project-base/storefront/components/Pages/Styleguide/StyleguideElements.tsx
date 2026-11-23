import { ReactNode, RefObject } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type StyleguideSectionProps = {
    id: string;
    group: string;
    title: string;
    description: ReactNode;
    ref: RefObject<HTMLElement | null>;
};

export const StyleguideSection: FC<StyleguideSectionProps> = ({ children, description, group, id, ref, title }) => (
    <section ref={ref} aria-labelledby={`${id}-title`} className="flex scroll-mt-fixed-header flex-col gap-8" id={id}>
        <header className="flex flex-col gap-2 border-border-less border-b pb-5">
            <p className="font-semibold text-text-accent text-xs uppercase tracking-wider">{group}</p>
            <h2 className="outline-none" id={`${id}-title`} tabIndex={-1}>
                {title}
            </h2>
            <p className="text-text-less">{description}</p>
        </header>

        {children}
    </section>
);

type StyleguideSubSectionProps = {
    title: string;
    description?: ReactNode;
};

export const StyleguideSubSection: FC<StyleguideSubSectionProps> = ({ children, description, title }) => (
    <div className="flex flex-col gap-3">
        <div className="flex flex-col gap-1">
            <h3 className="h5">{title}</h3>
            {description && <p className="text-sm text-text-less">{description}</p>}
        </div>

        {children}
    </div>
);

type StyleguidePreviewProps = {
    background?: 'default' | 'brand';
};

/**
 * Framed canvas for examples. The brand background switches the text color, so specimen captions stay readable.
 */
export const StyleguidePreview: FC<StyleguidePreviewProps> = ({ background = 'default', children, className, tid }) => (
    <div
        data-tid={tid}
        className={twMergeCustom(
            'rounded-xl border border-border-less p-5 vl:p-6',
            background === 'default' ? 'bg-background-default' : 'bg-background-brand text-text-inverted',
            className,
        )}
    >
        {children}
    </div>
);

type StyleguideSpecimenProps = {
    label: string;
    hint?: string;
};

/**
 * One example with a caption naming the component or prop combination it shows.
 */
export const StyleguideSpecimen: FC<StyleguideSpecimenProps> = ({ children, className, hint, label }) => (
    <figure className="flex min-w-0 flex-col items-start gap-2">
        <div className={className}>{children}</div>

        <figcaption className="flex flex-col text-xs">
            <code className="font-semibold">{label}</code>
            {hint && <span className="opacity-70">{hint}</span>}
        </figcaption>
    </figure>
);

type StyleguideMatrixRow = {
    label: ReactNode;
    cells: ReactNode[];
};

type StyleguideMatrixProps = {
    columns: string[];
    rows: StyleguideMatrixRow[];
};

/**
 * Table of examples, one row per option (size, level) and one column per component or state.
 */
export const StyleguideMatrix: FC<StyleguideMatrixProps> = ({ columns, rows }) => (
    <div className="overflow-x-auto rounded-xl border border-border-less bg-background-default">
        <table className="w-full border-collapse text-left">
            <thead>
                <tr className="border-border-less border-b bg-background-more">
                    {columns.map((column) => (
                        <th
                            key={column}
                            className="whitespace-nowrap px-4 py-3 font-semibold text-text-less text-xs uppercase tracking-wider"
                            scope="col"
                        >
                            {column}
                        </th>
                    ))}
                </tr>
            </thead>

            <tbody>
                {rows.map((row, rowIndex) => (
                    <tr key={rowIndex} className="border-border-less border-b last:border-b-0">
                        <th className="whitespace-nowrap px-4 py-4 align-middle font-normal" scope="row">
                            {row.label}
                        </th>
                        {row.cells.map((cell, cellIndex) => (
                            <td key={cellIndex} className="px-4 py-4 align-middle">
                                {cell}
                            </td>
                        ))}
                    </tr>
                ))}
            </tbody>
        </table>
    </div>
);

type StyleguideMatrixLabelProps = {
    name: string;
    hint?: string;
};

export const StyleguideMatrixLabel: FC<StyleguideMatrixLabelProps> = ({ hint, name }) => (
    <span className="flex flex-col">
        <code className="font-semibold text-sm">{name}</code>
        {hint && <span className="text-text-less text-xs">{hint}</span>}
    </span>
);

type StyleguideRulesProps = {
    title?: string;
    rules: ReactNode[];
};

export const StyleguideRules: FC<StyleguideRulesProps> = ({ rules, title = 'Rules' }) => (
    <div className="rounded-xl bg-background-more p-5 text-sm">
        <p className="mb-2 font-semibold">{title}</p>

        <ul className="flex list-disc flex-col gap-1.5 pl-5 marker:text-text-less">
            {rules.map((rule, index) => (
                <li key={index}>{rule}</li>
            ))}
        </ul>
    </div>
);

export const StyleguideCode: FC = ({ children, className }) => (
    <code className={twJoin('rounded-sm bg-background-most px-1 py-0.5 text-[0.9em]', className)}>{children}</code>
);
