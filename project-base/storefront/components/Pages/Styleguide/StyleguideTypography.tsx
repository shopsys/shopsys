import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Link } from 'components/Basic/Link/Link';
import { getLinkClassName } from 'components/Basic/Link/linkUtils';
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
                    One rule for every text link: placement decides the default underline, hover is the same everywhere.
                    A link inside text is always underlined; a standalone link (navigation, footer, card title) starts
                    without an underline. On hover every link is underlined, and colored links also shift their color.
                    Cards underline only their title. The header (main menu, phone and icon menu) is the only exception
                    and responds with color only. Focus keeps the existing orange background.
                </p>
                <Link href="/styleguide" tid={TIDs.styleguide_content_link}>
                    Content link
                </Link>
                <button className={getLinkClassName()} data-tid={TIDs.styleguide_text_action} type="button">
                    Edit details
                </button>
                <ExtendedNextLink
                    className={getLinkClassName({ variant: 'navigation' })}
                    href="/styleguide"
                    tid={TIDs.styleguide_navigation_link}
                >
                    About us
                </ExtendedNextLink>
                <div className="rounded-lg bg-background-brand p-4">
                    <Link
                        className={getLinkClassName({ isInverted: true })}
                        href="/styleguide"
                        tid={TIDs.styleguide_inverted_link}
                    >
                        Personal data processing
                    </Link>
                </div>
                <ExtendedNextLink
                    className="group rounded-lg bg-background-more p-4 text-text-default no-underline hover:text-text-default hover:no-underline"
                    href="/styleguide"
                    tid={TIDs.styleguide_card_link}
                >
                    <span className="font-semibold group-hover:underline" data-tid={TIDs.styleguide_card_title}>
                        Product title
                    </span>
                    <p data-tid={TIDs.styleguide_card_text}>Supporting card text keeps its decoration.</p>
                </ExtendedNextLink>
                <span>Consent text without a destination stays ordinary text.</span>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
