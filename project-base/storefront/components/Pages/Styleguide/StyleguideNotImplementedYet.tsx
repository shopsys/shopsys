import {
    NotImplementedTooltip,
    NotImplementedYetInject,
    NotImplementedYetTag,
    NotImplementedYetWrapper,
    notImplementedYetHandler,
} from 'components/Basic/NotImplementedYet/NotImplementedYet';
import { Button } from 'components/Forms/Button/Button';
import { StyleguidePreview, StyleguideSubSection } from './StyleguideElements';

export const StyleguideNotImplementedYet: FC = () => {
    return (
        <>
            <StyleguideSubSection
                description="notImplementedYetHandler shows a toast message on click or on mouse enter."
                title="Toast message"
            >
                <StyleguidePreview className="flex flex-wrap gap-3">
                    <Button size="small" type="button" onClick={notImplementedYetHandler}>
                        Not implemented onClick
                    </Button>
                    <Button size="small" type="button" variant="secondary" onMouseEnter={notImplementedYetHandler}>
                        Not implemented onMouseEnter
                    </Button>
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection description="NotImplementedTooltip shows a tooltip on hover." title="Tooltip">
                <StyleguidePreview>
                    <NotImplementedTooltip>
                        <Button size="small" type="button">
                            Hover over
                        </Button>
                    </NotImplementedTooltip>
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection description="NotImplementedYetWrapper wraps around elements." title="Wrapper">
                <StyleguidePreview>
                    <NotImplementedYetWrapper>
                        <p>
                            SEO is a marketing tactic that costs five percent of overall budget but brings in more than
                            20 percent of revenue for many Fortune 500 companies, and yet very few of them prioritize
                            it. Instead, while slowly changing, too many CMOS still favor paid search, which costs 20
                            percent and brings in… 20 percent. In search, businesses need to shift priorities. Now.
                        </p>
                        <p>
                            Link volume is one of the most powerful signals{' '}
                            <strong>search engines use to rank websites</strong>. There’s a way to use links to generate
                            better search results, but too many marketers rely on traditional (or even black hat)
                            tactics that just don’t work anymore.
                        </p>
                    </NotImplementedYetWrapper>
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection
                description="NotImplementedYetInject is injected inside a positioned element."
                title="Inject"
            >
                <StyleguidePreview className="relative pt-12 vl:pt-12">
                    <p>
                        SEO is a marketing tactic that costs five percent of overall budget but brings in more than 20
                        percent of revenue for many Fortune 500 companies, and yet very few of them prioritize it.
                        Instead, while slowly changing, too many CMOS still favor paid search, which costs 20 percent
                        and brings in… 20 percent. In search, businesses need to shift priorities. Now.
                    </p>
                    <NotImplementedYetInject />
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection description="NotImplementedYetTag marks a feature name." title="Tag">
                <StyleguidePreview>
                    <h4>
                        Example feature
                        <NotImplementedYetTag />
                    </h4>
                </StyleguidePreview>
            </StyleguideSubSection>
        </>
    );
};
