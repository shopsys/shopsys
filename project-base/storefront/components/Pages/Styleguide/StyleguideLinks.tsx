import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Link } from 'components/Basic/Link/Link';
import { ShowMoreButton } from 'components/Basic/ShowMoreButton/ShowMoreButton';
import { TextButton } from 'components/Basic/TextButton/TextButton';
import { TIDs } from 'cypress/tids';
import {
    StyleguideCode,
    StyleguideMatrix,
    StyleguideMatrixLabel,
    StyleguidePreview,
    StyleguideRules,
    StyleguideSpecimen,
    StyleguideSubSection,
} from './StyleguideElements';

const componentChoiceRows = [
    {
        label: <StyleguideMatrixLabel name="Navigates" />,
        cells: [
            <StyleguideCode key="button">LinkButton</StyleguideCode>,
            <StyleguideCode key="text">Link</StyleguideCode>,
        ],
    },
    {
        label: <StyleguideMatrixLabel name="Runs an action" />,
        cells: [
            <span key="button">
                <StyleguideCode>Button</StyleguideCode>, <StyleguideCode>IconButton</StyleguideCode>
            </span>,
            <span key="text">
                <StyleguideCode>TextButton</StyleguideCode>, <StyleguideCode>ShowMoreButton</StyleguideCode>
            </span>,
        ],
    },
];

export const StyleguideLinks: FC = () => (
    <>
        <StyleguideSubSection
            description="Answer two questions: does it navigate or run an action, and should it look like a button or like text?"
            title="Which component to use"
        >
            <StyleguideMatrix columns={['', 'Looks like a button', 'Looks like text']} rows={componentChoiceRows} />
        </StyleguideSubSection>

        <StyleguideRules
            rules={[
                <>
                    <StyleguideCode>variant="text"</StyleguideCode> sits inside a sentence or alone next to text (a
                    policy under a button, the consent link in the footer), keeps the font of the text and is always
                    underlined.
                </>,
                <>
                    <StyleguideCode>variant="action"</StyleguideCode> is a text action on its own with one fixed
                    typography and a 16 px trailing icon. It is always underlined.
                </>,
                <>
                    <StyleguideCode>variant="navigation"</StyleguideCode> sits among similar links in a menu, footer
                    column or breadcrumbs and is underlined on hover.
                </>,
                <>
                    <StyleguideCode>tone</StyleguideCode> is <StyleguideCode>accent</StyleguideCode>,{' '}
                    <StyleguideCode>default</StyleguideCode>, <StyleguideCode>subtle</StyleguideCode> (always
                    underlined) or <StyleguideCode>inverted</StyleguideCode>.
                </>,
                'Cards are not text links: they react to hover by their surface, never by an underline.',
                'Exceptions: the main menu in the header responds with color only, and the icon actions on a product (wishlist, compare, ask a question) stay muted until hover.',
                'Focus keeps the orange background. Do not style links with underline or text link classes by hand.',
            ]}
        />

        <StyleguideSubSection title="Inline">
            <StyleguidePreview className="flex flex-col items-start gap-8">
                <StyleguideSpecimen label="<Link>" hint="variant text, tone accent (default)">
                    <p>
                        By submitting, you agree with the{' '}
                        <Link href="/styleguide" tid={TIDs.styleguide_content_link}>
                            terms and conditions
                        </Link>{' '}
                        and the{' '}
                        <Link href="/styleguide" target="_blank">
                            privacy policy
                        </Link>
                        .
                    </p>
                </StyleguideSpecimen>
                <StyleguideSpecimen hint='tone="subtle", a single link next to text' label="<Link>">
                    <Link
                        className="text-sm"
                        href="/styleguide"
                        target="_blank"
                        tid={TIDs.styleguide_subtle_link}
                        tone="subtle"
                    >
                        How we process and verify reviews
                    </Link>
                </StyleguideSpecimen>
            </StyleguidePreview>
        </StyleguideSubSection>

        <StyleguideSubSection title="Standalone">
            <StyleguidePreview className="flex flex-wrap items-start gap-x-12 gap-y-8">
                <StyleguideSpecimen label="<TextButton>">
                    <TextButton tid={TIDs.styleguide_text_action}>Edit details</TextButton>
                </StyleguideSpecimen>
                <StyleguideSpecimen label="<ShowMoreButton>">
                    <ShowMoreButton isExpanded={false}>Show 2 more products</ShowMoreButton>
                </StyleguideSpecimen>
                <StyleguideSpecimen hint="jump to a section of the page" label='<Link variant="action">'>
                    <Link href="#links" variant="action">
                        12 reviews
                    </Link>
                </StyleguideSpecimen>
            </StyleguidePreview>
        </StyleguideSubSection>

        <StyleguideSubSection title="List">
            <StyleguidePreview className="flex flex-wrap items-start gap-x-12 gap-y-8">
                <StyleguideSpecimen hint="tone default" label='<Link variant="navigation">'>
                    <Link href="/styleguide" variant="navigation" tid={TIDs.styleguide_navigation_link}>
                        About us
                    </Link>
                </StyleguideSpecimen>
                <StyleguideSpecimen hint='tone="accent"' label='<Link variant="navigation">'>
                    <Link href="/styleguide" variant="navigation" tone="accent">
                        All categories
                    </Link>
                </StyleguideSpecimen>
            </StyleguidePreview>
        </StyleguideSubSection>

        <StyleguideSubSection title="Inverted">
            <StyleguidePreview background="brand">
                <StyleguideSpecimen label='<Link tone="inverted">'>
                    <Link href="/styleguide" tid={TIDs.styleguide_inverted_link} tone="inverted">
                        Personal data processing
                    </Link>
                </StyleguideSpecimen>
            </StyleguidePreview>
        </StyleguideSubSection>

        <StyleguideSubSection
            description="A card is one link. It reacts to hover by its surface (border, background, shadow), never by an underline."
            title="Card link"
        >
            <StyleguidePreview className="flex flex-wrap items-start gap-x-12 gap-y-8">
                <StyleguideSpecimen hint="group/card-link +" label="<ExtendedNextLink>">
                    <ExtendedNextLink
                        className="group/card-link block rounded-lg bg-background-more p-4 text-text-default no-underline hover:text-text-default hover:no-underline"
                        href="/styleguide"
                        tid={TIDs.styleguide_card_link}
                    >
                        <span className="font-semibold" data-tid={TIDs.styleguide_card_title}>
                            Product title
                        </span>
                        <p data-tid={TIDs.styleguide_card_text}>Supporting card text keeps its decoration.</p>
                    </ExtendedNextLink>
                </StyleguideSpecimen>
                <StyleguideSpecimen hint="no destination, no link" label="Plain text">
                    <span>Consent text without a destination stays ordinary text.</span>
                </StyleguideSpecimen>
            </StyleguidePreview>
        </StyleguideSubSection>
    </>
);
