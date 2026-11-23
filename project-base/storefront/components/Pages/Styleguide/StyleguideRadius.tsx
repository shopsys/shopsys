import { ChangeEvent, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import {
    StyleguideCode,
    StyleguideMatrix,
    StyleguideMatrixLabel,
    StyleguidePreview,
    StyleguideRules,
    StyleguideSpecimen,
    StyleguideSubSection,
} from './StyleguideElements';

const DEFAULT_RADIUS_BASE = 10;
const RADIUS_BASE_PROPERTY = '--radius-base';
const RADIUS_SAMPLE_ATTRIBUTE = 'data-radius-sample';

// class names are listed in full, so Tailwind generates them
const RADIUS_ROLES = [
    {
        name: 'detail',
        className: 'rounded-detail',
        size: 'h-10 w-24',
        usage: 'Flags, badges, checkbox, tooltip, icons inside a field',
    },
    {
        name: 'control',
        className: 'rounded-control',
        size: 'h-12 w-32',
        usage: 'Buttons, fields, chips, pagination, dropdowns',
    },
    {
        name: 'card',
        className: 'rounded-card',
        size: 'h-24 w-36',
        usage: 'Cards in a list, selection cards, panels, popovers, alerts, infobox',
    },
    {
        name: 'surface',
        className: 'rounded-surface',
        size: 'h-28 w-44',
        usage: 'Popup, toast, cookie consent, form blocks, cart summary',
    },
    { name: 'full', className: 'rounded-full', size: 'h-10 w-24', usage: 'Pills, avatars, dots, switch, radio' },
];

const COMPONENT_RADII = [
    { name: 'button', className: 'rounded-button', usage: 'Button, LinkButton, SubmitButton, rounded IconButton' },
    { name: 'input', className: 'rounded-input', usage: 'Text fields, select, search' },
    { name: 'tag', className: 'rounded-tag', usage: 'Tag, selected filter chip' },
    { name: 'counter', className: 'rounded-counter', usage: 'Spinbox' },
    { name: 'toast', className: 'rounded-toast', usage: 'Toast messages' },
    { name: 'checkbox', className: 'rounded-checkbox', usage: 'Checkbox' },
    { name: 'flag', className: 'rounded-flag', usage: 'Product and blog flags' },
    { name: 'product-card', className: 'rounded-product-card', usage: 'Product list items' },
];

// the rendered radius of every sample, so the values always match the theme and the controls
const measureRadii = (container: HTMLElement) =>
    Object.fromEntries(
        Array.from(container.querySelectorAll<HTMLElement>(`[${RADIUS_SAMPLE_ATTRIBUTE}]`)).map((sample) => [
            sample.getAttribute(RADIUS_SAMPLE_ATTRIBUTE),
            window.getComputedStyle(sample).borderTopLeftRadius,
        ]),
    );

// the project may set its own --radius-base in styles/theme.css, so the preview starts from the theme value
const readThemeRadiusBase = () => {
    const themeRadiusBase = Number.parseFloat(
        window.getComputedStyle(document.documentElement).getPropertyValue(RADIUS_BASE_PROPERTY),
    );

    return Number.isNaN(themeRadiusBase) ? DEFAULT_RADIUS_BASE : themeRadiusBase;
};

const formatRadius = (radius: string | undefined) =>
    radius ? `${Math.round(Number.parseFloat(radius) * 10) / 10} px` : '–';

export const StyleguideRadius: FC = () => {
    const containerRef = useRef<HTMLDivElement>(null);
    const [measuredRadii, setMeasuredRadii] = useState<Record<string, string>>({});
    const [themeRadiusBase, setThemeRadiusBase] = useState(DEFAULT_RADIUS_BASE);
    const [customRadiusBase, setCustomRadiusBase] = useState<number | null>(null);

    const radiusBase = customRadiusBase ?? themeRadiusBase;

    useEffect(() => {
        setThemeRadiusBase(readThemeRadiusBase());

        // the controls preview the whole page, so leaving the styleguide restores the theme
        return () => {
            document.documentElement.style.removeProperty(RADIUS_BASE_PROPERTY);
        };
    }, []);

    useEffect(() => {
        const rootElement = document.documentElement;

        // only a moved slider overrides the theme
        if (customRadiusBase === null) {
            rootElement.style.removeProperty(RADIUS_BASE_PROPERTY);
        } else {
            rootElement.style.setProperty(RADIUS_BASE_PROPERTY, `${customRadiusBase}px`);
        }

        if (containerRef.current) {
            setMeasuredRadii(measureRadii(containerRef.current));
        }
    }, [customRadiusBase, themeRadiusBase]);

    const handleRadiusBaseChange = (event: ChangeEvent<HTMLInputElement>) =>
        setCustomRadiusBase(Number(event.target.value));

    const getComponentRow = ({ className, name, usage }: (typeof COMPONENT_RADII)[number]) => ({
        label: <StyleguideMatrixLabel hint={usage} name={className} />,
        cells: [
            <span key="value" className="whitespace-nowrap text-sm">
                {formatRadius(measuredRadii[`component-${name}`])}
            </span>,
            <span
                key="preview"
                className={twJoin(
                    'block h-10 w-24 border-2 border-border-default bg-background-accent-less',
                    className,
                )}
                {...{ [RADIUS_SAMPLE_ATTRIBUTE]: `component-${name}` }}
            />,
        ],
    });

    return (
        <div ref={containerRef} className="flex flex-col gap-8">
            <StyleguideRules
                rules={[
                    <>
                        One variable, <StyleguideCode>--radius-base</StyleguideCode> ({themeRadiusBase} px), sets every
                        radius. The roles are its multiples 0.6 / 1 / 1.2 / 1.6, so a project changes the whole look in
                        one line and 0 makes it square with the same hierarchy.
                    </>,
                    'Pick the role by the element: full for pills and dots, detail up to 28 px or inside a control, control for what you click or type into, card for repeated items, smaller panels and boxed messages, surface for large areas, dialogs and toasts.',
                    'A nested element is never rounder than its parent. Next to the parent edge with up to 8 px of padding it gets the parent radius minus the padding, at least the detail radius.',
                    <>
                        Components use the role or component tokens, never <StyleguideCode>rounded-md</StyleguideCode>,{' '}
                        <StyleguideCode>-lg</StyleguideCode>, <StyleguideCode>-xl</StyleguideCode> or{' '}
                        <StyleguideCode>-2xl</StyleguideCode>. The default Tailwind scale stays unchanged for project
                        code.
                    </>,
                ]}
            />

            <StyleguideSubSection
                description="The controls change the radius of the whole page while you stay in the styleguide."
                title="Try it"
            >
                <StyleguidePreview className="flex flex-wrap items-center gap-x-10 gap-y-4">
                    <label className="flex items-center gap-3 text-sm" htmlFor="styleguide-radius-base">
                        <StyleguideCode>--radius-base</StyleguideCode>
                        <input
                            className="w-48"
                            id="styleguide-radius-base"
                            max={20}
                            min={0}
                            type="range"
                            value={radiusBase}
                            onChange={handleRadiusBaseChange}
                        />
                        <span className="w-12 font-semibold">{radiusBase} px</span>
                    </label>
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection title="Roles">
                <StyleguidePreview className="flex flex-wrap items-end gap-8">
                    {RADIUS_ROLES.map(({ className, name, size, usage }) => (
                        <StyleguideSpecimen
                            key={name}
                            hint={`${formatRadius(measuredRadii[`role-${name}`])} · ${usage}`}
                            label={className}
                        >
                            <span
                                className={twJoin(
                                    'block border-2 border-border-default bg-background-accent-less',
                                    size,
                                    className,
                                )}
                                {...{ [RADIUS_SAMPLE_ATTRIBUTE]: `role-${name}` }}
                            />
                        </StyleguideSpecimen>
                    ))}
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection
                description="Each component token points to a role. Change a single component in its token in styles/theme.css, not with a rounded-* class."
                title="Component tokens"
            >
                <StyleguideMatrix columns={['Token', 'Value', 'Preview']} rows={COMPONENT_RADII.map(getComponentRow)} />
            </StyleguideSubSection>
        </div>
    );
};
