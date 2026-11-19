import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { Button } from 'components/Forms/Button/Button';
import { getButtonIconClassName } from 'components/Forms/Button/buttonUtils';
import { IconButton } from 'components/Forms/Button/IconButton';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { TIDs } from 'cypress/tids';
import { FormEvent, useState } from 'react';
import { StyleguideSection, StyleguideSubSection } from './StyleguideElements';

const BUTTON_SIZES = ['small', 'medium', 'large', 'xlarge'] as const;

const ButtonSizeRow: FC<{ size: (typeof BUTTON_SIZES)[number] }> = ({ size }) => (
    <div className="flex flex-wrap items-center gap-3">
        <span className="w-20 text-sm">{size}</span>
        <Button size={size} tid={`${TIDs.styleguide_button_}${size}`}>
            Continue
        </Button>
        <LinkButton href="/styleguide" size={size} tid={`${TIDs.styleguide_link_button_}${size}`}>
            Continue
        </LinkButton>
        <form onSubmit={(event) => event.preventDefault()}>
            <SubmitButton size={size} tid={`${TIDs.styleguide_submit_button_}${size}`}>
                Continue
            </SubmitButton>
        </form>
        <Button size={size} tid={`${TIDs.styleguide_icon_button_}${size}`}>
            <CartIcon aria-hidden className={getButtonIconClassName(size)} />
            Continue
        </Button>
    </div>
);

export const StyleguideButtons: FC = () => {
    const [email, setEmail] = useState('');
    const [invalidFormName, setInvalidFormName] = useState('');
    const [isInvalidFormSubmitted, setIsInvalidFormSubmitted] = useState(false);
    const isInvalidFormNameMissing = isInvalidFormSubmitted && invalidFormName.trim() === '';

    const onButtonClick = (variant: string) => {
        alert(`${variant} button clicked`);
    };

    const handleInvalidFormSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsInvalidFormSubmitted(true);
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

            <StyleguideSubSection className="flex flex-col gap-4" title="Responsive sizes">
                <p className="text-sm">
                    Medium is the default for Button, LinkButton and SubmitButton. Use large for main form submits and
                    xlarge for major purchase actions. Below 1024 px the heights are 36 / 40 / 44 / 44 px; from 1024 px
                    they are 36 / 40 / 48 / 56 px. Small and medium use 14 px text, large uses 16 px, and xlarge uses 16
                    / 18 px. CSS media queries control the sizes. Text can wrap and grow.
                </p>
                <p className="text-sm">
                    Small is for compact places only (table rows, filters, actions inside a card); a standalone action
                    uses medium or larger so it stays easy to tap. Buttons placed side by side share one size and
                    express priority with their variant. A button stays enabled while a form is invalid and shows the
                    errors after the click; only a truly disabled button shows the disabled cursor.
                </p>
                {BUTTON_SIZES.map((size) => (
                    <ButtonSizeRow key={size} size={size} />
                ))}
            </StyleguideSubSection>

            <StyleguideSubSection
                className="flex flex-wrap items-start gap-4"
                title="Long labels, loading and input pairing"
            >
                <div className="w-48">
                    <Button size="xlarge" tid={TIDs.styleguide_wrapping_button}>
                        Continue to the next step of your order
                    </Button>
                </div>
                <SubmitButton disabled shouldShowSpinner size="large" tid={TIDs.styleguide_loading_button}>
                    Saving changes
                </SubmitButton>
                <div className="flex items-start gap-2">
                    <TextInput
                        id="styleguide-email"
                        inputSize="small"
                        label="Your email"
                        type="email"
                        value={email}
                        onChange={(event) => setEmail(event.target.value)}
                    />
                    <Button className="min-h-12" size="large" tid={TIDs.styleguide_input_paired_button}>
                        Send
                    </Button>
                </div>
                <p className="text-sm">
                    A button paired with an input keeps the input height (min-h-12) and aligns to the top, so validation
                    messages do not stretch it. IconButton keeps its existing scale on every pointer type. Check target
                    size and spacing in context.
                </p>
            </StyleguideSubSection>

            <StyleguideSubSection title="CMS call to action (large)">
                <div className="user-text">
                    <a className="gjs-button-link" data-tid={TIDs.styleguide_cms_button} href="/styleguide">
                        Continue
                    </a>
                </div>
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
                <Button
                    disabled
                    hasDisabledLook
                    tid={TIDs.styleguide_disabled_button}
                    variant="primary"
                    onClick={() => onButtonClick('Primary')}
                >
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

            <StyleguideSubSection title="Invalid form (button stays enabled)">
                <form noValidate className="flex max-w-md flex-col gap-3" onSubmit={handleInvalidFormSubmit}>
                    <TextInput
                        aria-describedby={isInvalidFormNameMissing ? 'styleguide-invalid-form-error' : undefined}
                        aria-invalid={isInvalidFormNameMissing}
                        hasError={isInvalidFormNameMissing}
                        id="styleguide-invalid-form-name"
                        label="Your name"
                        value={invalidFormName}
                        onChange={(event) => setInvalidFormName(event.target.value)}
                    />
                    {isInvalidFormNameMissing && (
                        <p className="text-sm text-text-error" id="styleguide-invalid-form-error">
                            Please enter your name
                        </p>
                    )}
                    <Button className="self-start" size="large" type="submit">
                        Send
                    </Button>
                </form>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
