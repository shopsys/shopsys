import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Link } from 'components/Basic/Link/Link';
import { ShowMoreButton } from 'components/Basic/ShowMoreButton/ShowMoreButton';
import { TextButton } from 'components/Basic/TextButton/TextButton';
import { TIDs } from 'cypress/tids';
import { StyleguideSection, StyleguideSubSection } from './StyleguideElements';

export const StyleguideTypography: FC = () => {
    return (
        <StyleguideSection className="flex flex-col flex-wrap gap-3" title="Typography">
            <h1>H1 title</h1>
            <div className="h1">H1 className</div>
            <h2>H2 title</h2>
            <div className="h2">H2 className</div>
            <h3>H3 title</h3>
            <div className="h3">H3 className</div>
            <h4>H4 title</h4>
            <div className="h4">H4 className</div>
            <h5>H5 title</h5>
            <div className="h5">H5 className</div>

            <p>
                Example paragraph. Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempora consectetur at
                necessitatibus obcaecati optio quas dicta debitis et quod provident harum, voluptatem perferendis ullam
                soluta temporibus corrupti, alias velit nobis.
            </p>

            <StyleguideSubSection className="flex flex-col items-start gap-4" title="Links and text actions">
                <p className="text-sm">
                    Pick the component by two questions: does it navigate (Link, LinkButton) or run an action (Button,
                    TextButton, IconButton), and should it look like a button or like text? Text links and text actions
                    share two props. Placement: inline (inside a sentence, keeps the font of the text, always
                    underlined), standalone (a text action on its own, one fixed typography and a 16px trailing icon,
                    always underlined) or list (among similar links in a menu, footer column or breadcrumbs, underlined
                    on hover). Tone: accent, default, subtle (always underlined) or inverted. Cards underline only their
                    title through a named group. Exceptions: the main menu in the header responds with color only, and
                    the icon actions on a product (wishlist, compare, ask a question) stay muted until hover. Focus
                    keeps the orange background. Do not style links with underline or text-link classes by hand.
                </p>
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
                <TextButton tid={TIDs.styleguide_text_action}>Edit details (standalone text action)</TextButton>
                <ShowMoreButton isExpanded={false}>Show 2 more products</ShowMoreButton>
                <Link href="/styleguide" placement="standalone" target="_blank" tone="subtle">
                    How we process and verify reviews
                </Link>
                <Link href="/styleguide" placement="list" tid={TIDs.styleguide_navigation_link}>
                    About us (list link)
                </Link>
                <Link href="/styleguide" placement="list" tone="accent">
                    All categories (accent list link)
                </Link>
                <div className="rounded-lg bg-background-brand p-4">
                    <Link href="/styleguide" tid={TIDs.styleguide_inverted_link} tone="inverted">
                        Personal data processing
                    </Link>
                </div>
                <ExtendedNextLink
                    className="group/card-link rounded-lg bg-background-more p-4 text-text-default no-underline hover:text-text-default hover:no-underline"
                    href="/styleguide"
                    tid={TIDs.styleguide_card_link}
                >
                    <span
                        className="font-semibold group-hover/card-link:underline"
                        data-tid={TIDs.styleguide_card_title}
                    >
                        Product title
                    </span>
                    <p data-tid={TIDs.styleguide_card_text}>Supporting card text keeps its decoration.</p>
                </ExtendedNextLink>
                <span>Consent text without a destination stays ordinary text.</span>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
