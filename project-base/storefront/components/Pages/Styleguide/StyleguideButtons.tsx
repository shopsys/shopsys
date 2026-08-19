import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { Button } from 'components/Forms/Button/Button';
import { IconButton } from 'components/Forms/Button/IconButton';
import { StyleguideSection, StyleguideSubSection } from './StyleguideElements';

export const StyleguideButtons: FC = () => {
    const onButtonClick = (variant: string) => {
        alert(`${variant} button clicked`);
    };

    return (
        <StyleguideSection className="flex flex-col gap-5" title="Buttons">
            <StyleguideSubSection className="flex flex-wrap gap-3" title="Variants">
                <Button>Primary</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="tertiary">Tertiary</Button>
                <Button variant="danger">Danger</Button>
            </StyleguideSubSection>

            <StyleguideSubSection title="Inverted (dark backgrounds)">
                <div className="flex w-fit flex-wrap gap-3 rounded-md bg-background-brand p-4">
                    <Button variant="inverted">Inverted</Button>
                </div>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Sizes">
                <Button size="small">Small</Button>
                <Button>Default</Button>
                <Button size="large">Large</Button>
                <Button size="xlarge">Large</Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap items-center gap-3" title="Icon button variants">
                <IconButton Icon={CloseIcon} title="Default icon button" />
                <IconButton Icon={CloseIcon} shape="rounded" title="Ghost icon button" variant="ghost" />
                <IconButton disabled Icon={CloseIcon} title="Disabled icon button" />
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap items-center gap-3" title="Icon button sizes">
                <IconButton Icon={CloseIcon} size="compact" title="Compact icon button" />
                <IconButton Icon={CloseIcon} size="small" title="Small icon button" />
                <IconButton Icon={CloseIcon} title="Medium icon button" />
                <IconButton Icon={CloseIcon} size="large" title="Large icon button" />
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Disabled">
                <Button disabled hasDisabledLook variant="primary" onClick={() => onButtonClick('Primary')}>
                    Primary
                </Button>
                <Button disabled hasDisabledLook variant="secondary" onClick={() => onButtonClick('Secondary')}>
                    Secondary
                </Button>
                <Button disabled hasDisabledLook variant="tertiary" onClick={() => onButtonClick('Tertiary')}>
                    Tertiary
                </Button>
                <Button disabled hasDisabledLook variant="danger" onClick={() => onButtonClick('Danger')}>
                    Danger
                </Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Disabled look only (clickable)">
                <Button hasDisabledLook variant="primary" onClick={() => onButtonClick('Primary')}>
                    Primary
                </Button>
                <Button hasDisabledLook variant="secondary" onClick={() => onButtonClick('Secondary')}>
                    Secondary
                </Button>
                <Button hasDisabledLook variant="tertiary" onClick={() => onButtonClick('Tertiary')}>
                    Tertiary
                </Button>
                <Button hasDisabledLook variant="danger" onClick={() => onButtonClick('Danger')}>
                    Danger
                </Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Disabled cursor only (clickable)">
                <Button hasDisabledCursor variant="primary" onClick={() => onButtonClick('Primary')}>
                    Primary
                </Button>
                <Button hasDisabledCursor variant="secondary" onClick={() => onButtonClick('Secondary')}>
                    Secondary
                </Button>
                <Button hasDisabledCursor variant="tertiary" onClick={() => onButtonClick('Tertiary')}>
                    Tertiary
                </Button>
                <Button hasDisabledCursor variant="danger" onClick={() => onButtonClick('Danger')}>
                    Danger
                </Button>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
