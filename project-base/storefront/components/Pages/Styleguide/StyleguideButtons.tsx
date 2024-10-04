import { StyleguideSection, StyleguideSubSection } from './StyleguideElements';
import { Button } from 'components/Forms/Button/Button';

export const StyleguideButtons: FC = () => {
    return (
        <StyleguideSection className="flex flex-col gap-5" title="Buttons">
            <StyleguideSubSection className="flex flex-wrap items-center gap-3" title="Variants">
                <Button>Primary</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="inverted">Inverted</Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap items-center gap-3" title="Sizes">
                <Button size="small">Small</Button>
                <Button>Default</Button>
                <Button size="large">Large</Button>
            </StyleguideSubSection>

            <StyleguideSubSection className="flex flex-wrap items-center gap-3" title="Disabled">
                <Button isDisabled variant="primary">
                    Primary
                </Button>
                <Button isDisabled variant="secondary">
                    Secondary
                </Button>
                <Button isDisabled variant="inverted">
                    Inverted
                </Button>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
