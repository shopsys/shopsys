import { useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import {
    StyleguideMatrix,
    StyleguideMatrixLabel,
    StyleguidePreview,
    StyleguideSpecimen,
    StyleguideSubSection,
} from './StyleguideElements';

// class names are listed in full, so Tailwind generates them
const RADIUS_SCALE = [
    { name: 'none', className: 'rounded-none' },
    { name: 'xs', className: 'rounded-xs' },
    { name: 'sm', className: 'rounded-sm' },
    { name: 'md', className: 'rounded-md' },
    { name: 'lg', className: 'rounded-lg' },
    { name: 'xl', className: 'rounded-xl' },
    { name: '2xl', className: 'rounded-2xl' },
    { name: '3xl', className: 'rounded-3xl' },
    { name: '4xl', className: 'rounded-4xl' },
    { name: 'full', className: 'rounded-full' },
];

const COMPONENT_RADII = [
    { name: 'button', className: 'rounded-button', usage: 'Button, LinkButton, SubmitButton, date picker' },
    { name: 'input', className: 'rounded-input', usage: 'Text inputs, search, social login' },
    { name: 'checkbox', className: 'rounded-checkbox', usage: 'Checkbox' },
    { name: 'counter', className: 'rounded-counter', usage: 'Spinbox' },
    { name: 'flag', className: 'rounded-flag', usage: 'Product and blog flags' },
    { name: 'tag', className: 'rounded-tag', usage: 'Tag' },
    { name: 'toast', className: 'rounded-toast', usage: 'Toast messages' },
    { name: 'product-card', className: 'rounded-product-card', usage: 'Product list items' },
];

const RADIUS_SAMPLE_ATTRIBUTE = 'data-radius-sample';

// the rendered radius of every sample, so the values always match the theme
const measureRadii = (container: HTMLElement) =>
    Object.fromEntries(
        Array.from(container.querySelectorAll<HTMLElement>(`[${RADIUS_SAMPLE_ATTRIBUTE}]`)).map((sample) => [
            sample.getAttribute(RADIUS_SAMPLE_ATTRIBUTE),
            window.getComputedStyle(sample).borderTopLeftRadius,
        ]),
    );

export const StyleguideRadius: FC = () => {
    const containerRef = useRef<HTMLDivElement>(null);
    const [measuredRadii, setMeasuredRadii] = useState<Record<string, string>>({});

    useEffect(() => {
        if (containerRef.current) {
            setMeasuredRadii(measureRadii(containerRef.current));
        }
    }, []);

    const getScaleName = (radius: string | undefined) =>
        RADIUS_SCALE.find((step) => measuredRadii[`scale-${step.name}`] === radius)?.name;

    const getComponentRow = ({ className, name, usage }: (typeof COMPONENT_RADII)[number]) => {
        const radius = measuredRadii[`component-${name}`];
        const scaleName = getScaleName(radius);

        return {
            label: <StyleguideMatrixLabel hint={usage} name={className} />,
            cells: [
                <span key="value" className="whitespace-nowrap text-sm">
                    {radius ? `${radius}${scaleName ? ` · ${scaleName}` : ''}` : '–'}
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
        };
    };

    return (
        <div ref={containerRef} className="flex flex-col gap-8">
            <StyleguideSubSection
                description="Components use the component tokens below, which point to a step of this scale."
                title="Scale"
            >
                <StyleguidePreview className="flex flex-wrap gap-6">
                    {RADIUS_SCALE.map(({ className, name }) => (
                        <StyleguideSpecimen key={name} hint={measuredRadii[`scale-${name}`]} label={name}>
                            <span
                                className={twJoin(
                                    'block size-16 border-2 border-border-default bg-background-accent-less',
                                    className,
                                )}
                                {...{ [RADIUS_SAMPLE_ATTRIBUTE]: `scale-${name}` }}
                            />
                        </StyleguideSpecimen>
                    ))}
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection
                description="Change the radius of a component in its token in styles/theme.css, not with a rounded-* class on the component."
                title="Component tokens"
            >
                <StyleguideMatrix columns={['Token', 'Value', 'Preview']} rows={COMPONENT_RADII.map(getComponentRow)} />
            </StyleguideSubSection>
        </div>
    );
};
