import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Button } from 'components/Forms/Button/Button';
import { getButtonIconClassName } from 'components/Forms/Button/buttonUtils';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { TIDs } from 'cypress/tids';
import { FormEvent, useState } from 'react';
import {
    StyleguideCode,
    StyleguideMatrix,
    StyleguideMatrixLabel,
    StyleguidePreview,
    StyleguideRules,
    StyleguideSpecimen,
    StyleguideSubSection,
} from './StyleguideElements';

// the variant gives the structure, the theme tokens give the color, so a project restyles a variant in styles/theme.css
const BUTTON_VARIANTS = [
    { variant: 'primary', usage: 'The main action of the view, filled with the primary color' },
    { variant: 'secondary', usage: 'A visible action that does not compete with primary, tonal secondary color' },
    { variant: 'outline', usage: 'Neutral actions such as repeat, edit or back, white with a 1px line' },
    { variant: 'ghost', usage: 'Low emphasis actions in toolbars and popups, no surface until hover' },
    { variant: 'danger', usage: 'Destructive actions' },
] as const;

type ButtonSize = 'small' | 'medium' | 'large' | 'xlarge';

// documents the rendered sizes, which come from getButtonClassName and are verified by the styleguide Cypress test
const BUTTON_SIZE_SPECIFICATIONS: { size: ButtonSize; usage: string; belowVl: string; fromVl: string }[] = [
    { size: 'small', usage: 'Table rows, filters, card actions', belowVl: '36 px · 14 px', fromVl: '36 px · 14 px' },
    { size: 'medium', usage: 'Default', belowVl: '40 px · 14 px', fromVl: '40 px · 14 px' },
    { size: 'large', usage: 'Main form submits', belowVl: '44 px · 16 px', fromVl: '48 px · 16 px' },
    { size: 'xlarge', usage: 'Major purchase actions', belowVl: '44 px · 16 px', fromVl: '56 px · 18 px' },
];

const preventSubmit = (event: FormEvent<HTMLFormElement>) => event.preventDefault();

const variantRows = BUTTON_VARIANTS.map(({ usage, variant }) => ({
    label: <StyleguideMatrixLabel hint={usage} name={variant} />,
    cells: [
        <Button key="default" variant={variant}>
            Continue
        </Button>,
        <Button
            key="disabled"
            disabled
            hasDisabledLook
            tid={variant === 'primary' ? TIDs.styleguide_disabled_button : undefined}
            variant={variant}
        >
            Continue
        </Button>,
    ],
}));

const sizeRows = BUTTON_SIZE_SPECIFICATIONS.map(({ belowVl, fromVl, size, usage }) => ({
    label: <StyleguideMatrixLabel hint={usage} name={size} />,
    cells: [
        <span key="below-vl" className="whitespace-nowrap text-sm">
            {belowVl}
        </span>,
        <span key="from-vl" className="whitespace-nowrap text-sm">
            {fromVl}
        </span>,
        <Button key="button" size={size} tid={`${TIDs.styleguide_button_}${size}`}>
            Continue
        </Button>,
        <LinkButton key="link-button" href="/styleguide" size={size} tid={`${TIDs.styleguide_link_button_}${size}`}>
            Continue
        </LinkButton>,
        <form key="submit-button" onSubmit={preventSubmit}>
            <SubmitButton size={size} tid={`${TIDs.styleguide_submit_button_}${size}`}>
                Continue
            </SubmitButton>
        </form>,
        <Button key="button-with-icon" size={size} tid={`${TIDs.styleguide_button_with_icon_}${size}`}>
            <CartIcon aria-hidden className={getButtonIconClassName(size)} />
            Continue
        </Button>,
    ],
}));

export const StyleguideButtons: FC = () => {
    const [email, setEmail] = useState('');
    const [invalidFormName, setInvalidFormName] = useState('');
    const [isInvalidFormSubmitted, setIsInvalidFormSubmitted] = useState(false);
    const isInvalidFormNameMissing = isInvalidFormSubmitted && invalidFormName.trim() === '';

    const handleInvalidFormSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setIsInvalidFormSubmitted(true);
    };

    return (
        <>
            <StyleguideRules
                rules={[
                    <>
                        <StyleguideCode>medium</StyleguideCode> is the default size. Use{' '}
                        <StyleguideCode>large</StyleguideCode> for main form submits and{' '}
                        <StyleguideCode>xlarge</StyleguideCode> for major purchase actions.
                    </>,
                    <>
                        <StyleguideCode>small</StyleguideCode> is only for compact places (table rows, filters, actions
                        inside a card). A standalone action uses medium or larger, so it stays easy to tap.
                    </>,
                    'Buttons placed side by side share one size and express their priority with the variant.',
                    'A button stays enabled while a form is invalid and shows the errors after the click. Only a truly disabled button shows the disabled cursor.',
                    'Text can wrap and the button grows with it.',
                ]}
            />

            <StyleguideSubSection
                description="Hover and press each button to check its states. The disabled column uses hasDisabledLook."
                title="Variants and states"
            >
                <StyleguideMatrix columns={['Variant', 'Default', 'Disabled']} rows={variantRows} />
            </StyleguideSubSection>

            <StyleguideSubSection description="For buttons on dark backgrounds." title="Inverted variant">
                <StyleguidePreview background="brand" className="flex flex-wrap gap-8">
                    <StyleguideSpecimen label="inverted">
                        <Button variant="inverted">Continue</Button>
                    </StyleguideSpecimen>
                    <StyleguideSpecimen label="inverted" hint="disabled">
                        <Button disabled hasDisabledLook variant="inverted">
                            Continue
                        </Button>
                    </StyleguideSpecimen>
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection
                description="Height · font size per breakpoint. CSS media queries switch the scale at 1024 px (vl); the pointer type does not change it."
                title="Sizes"
            >
                <StyleguideMatrix
                    columns={[
                        'Size',
                        'Below 1024 px',
                        'From 1024 px',
                        'Button',
                        'LinkButton',
                        'SubmitButton',
                        'With icon',
                    ]}
                    rows={sizeRows}
                />
            </StyleguideSubSection>

            <StyleguideSubSection title="Edge cases">
                <StyleguidePreview className="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                    <StyleguideSpecimen hint="xlarge in a 192 px wide container" label="Long label wraps">
                        <div className="w-48">
                            <Button size="xlarge" tid={TIDs.styleguide_wrapping_button}>
                                Continue to the next step of your order
                            </Button>
                        </div>
                    </StyleguideSpecimen>

                    <StyleguideSpecimen hint="disabled + shouldShowSpinner" label="Loading">
                        <SubmitButton disabled shouldShowSpinner size="large" tid={TIDs.styleguide_loading_button}>
                            Saving changes
                        </SubmitButton>
                    </StyleguideSpecimen>

                    <StyleguideSpecimen
                        hint="A default field is 56 px high. The large button next to it gets min-h-14 and aligns to the top, so validation messages do not stretch it."
                        label="Paired with an input"
                    >
                        <div className="flex items-start gap-2" data-tid={TIDs.styleguide_input_button_pair}>
                            <TextInput
                                id="styleguide-email"
                                label="Your email"
                                type="email"
                                value={email}
                                onChange={(event) => setEmail(event.target.value)}
                            />
                            <Button className="min-h-14" size="large" tid={TIDs.styleguide_input_paired_button}>
                                Send
                            </Button>
                        </div>
                    </StyleguideSpecimen>
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection
                description="Submit the empty form. The button stays enabled and the error appears after the click."
                title="Invalid form"
            >
                <StyleguidePreview>
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
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection
                description={
                    <>
                        Button link inserted by editors in the CMS (<StyleguideCode>.gjs-button-link</StyleguideCode>),
                        sized as large.
                    </>
                }
                title="CMS call to action"
            >
                <StyleguidePreview>
                    <div className="user-text">
                        <a className="gjs-button-link" data-tid={TIDs.styleguide_cms_button} href="/styleguide">
                            Continue
                        </a>
                    </div>
                </StyleguidePreview>
            </StyleguideSubSection>
        </>
    );
};
