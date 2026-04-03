import { Button } from 'components/Forms/Button/Button';
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
                <Button variant="inverted">Inverted</Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Sizes">
                <Button size="small">Small</Button>
                <Button>Default</Button>
                <Button size="large">Large</Button>
                <Button size="xlarge">Large</Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Disabled">
                <Button disabled hasDisabledLook variant="primary" onClick={() => onButtonClick('Primary')}>
                    Primary
                </Button>
                <Button disabled hasDisabledLook variant="secondary" onClick={() => onButtonClick('Secondary')}>
                    Secondary
                </Button>
                <Button disabled hasDisabledLook variant="inverted" onClick={() => onButtonClick('Inverted')}>
                    Inverted
                </Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Disabled look only (clickable)">
                <Button hasDisabledLook variant="primary" onClick={() => onButtonClick('Primary')}>
                    Primary
                </Button>
                <Button hasDisabledLook variant="secondary" onClick={() => onButtonClick('Secondary')}>
                    Secondary
                </Button>
                <Button hasDisabledLook variant="inverted" onClick={() => onButtonClick('Inverted')}>
                    Inverted
                </Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap gap-3" title="Disabled cursor only (clickable)">
                <Button hasDisabledCursor variant="primary" onClick={() => onButtonClick('Primary')}>
                    Primary
                </Button>
                <Button hasDisabledCursor variant="secondary" onClick={() => onButtonClick('Secondary')}>
                    Secondary
                </Button>
                <Button hasDisabledCursor variant="inverted" onClick={() => onButtonClick('Inverted')}>
                    Inverted
                </Button>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
