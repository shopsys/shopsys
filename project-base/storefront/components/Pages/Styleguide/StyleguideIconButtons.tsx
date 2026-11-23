import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TIDs } from 'cypress/tids';
import {
    StyleguideCode,
    StyleguidePreview,
    StyleguideRules,
    StyleguideSpecimen,
    StyleguideSubSection,
} from './StyleguideElements';

const ICON_BUTTON_SIZES = [
    { size: 'compact', dimension: '24 px' },
    { size: 'small', dimension: '32 px' },
    { size: 'medium', dimension: '40 px (default)' },
    { size: 'large', dimension: '48 px' },
] as const;

export const StyleguideIconButtons: FC = () => (
    <>
        <StyleguideRules
            rules={[
                <>
                    <StyleguideCode>variant="default"</StyleguideCode> with the default{' '}
                    <StyleguideCode>shape="circle"</StyleguideCode> is a standalone control with its own surface placed
                    over content or media: carousel and gallery arrows, back to top, closing a drawer.
                </>,
                <>
                    <StyleguideCode>variant="ghost"</StyleguideCode> with{' '}
                    <StyleguideCode>shape="rounded"</StyleguideCode> is a control inside another component: the arrow of
                    a select, search and password inputs, closing a popup or a toast, removing a cart item. It shows a
                    surface only on hover, and its corners use the button radius, so it fits the rectangular field
                    around it.
                </>,
                'Icon buttons placed side by side share one size. The title is the accessible name.',
            ]}
        />

        <StyleguideSubSection title="Variants in context">
            <StyleguidePreview className="flex flex-wrap items-start gap-10">
                <StyleguideSpecimen hint="over content or media" label="default">
                    <div className="flex h-28 w-48 items-center justify-between rounded-lg bg-background-most px-2">
                        <IconButton Icon={ArrowIcon} iconClassName="rotate-90" title="Previous" />
                        <IconButton Icon={ArrowIcon} iconClassName="-rotate-90" title="Next" />
                    </div>
                </StyleguideSpecimen>
                <StyleguideSpecimen hint='shape="rounded", inside a field' label="ghost">
                    <div className="flex h-14 w-64 items-center justify-between rounded-input border-2 border-input-border-default pr-1 pl-3">
                        <span className="font-secondary font-semibold text-input-placeholder-default">
                            Choose a country
                        </span>
                        <IconButton Icon={ArrowIcon} shape="rounded" title="Open" variant="ghost" />
                    </div>
                </StyleguideSpecimen>
                <StyleguideSpecimen label="disabled">
                    <IconButton disabled Icon={CloseIcon} title="Disabled icon button" />
                </StyleguideSpecimen>
            </StyleguidePreview>
        </StyleguideSubSection>

        <StyleguideSubSection description="Check the target size and spacing in the real context." title="Sizes">
            <StyleguidePreview className="flex flex-wrap items-end gap-10">
                {ICON_BUTTON_SIZES.map(({ dimension, size }) => (
                    <StyleguideSpecimen key={size} hint={dimension} label={size}>
                        <IconButton
                            Icon={CloseIcon}
                            size={size}
                            tid={`${TIDs.styleguide_icon_button_}${size}`}
                            title={`${size} icon button`}
                        />
                    </StyleguideSpecimen>
                ))}
            </StyleguidePreview>
        </StyleguideSubSection>
    </>
);
