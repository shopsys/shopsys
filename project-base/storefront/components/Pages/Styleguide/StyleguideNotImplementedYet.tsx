import { StyleguideSection, StyleguideSubSection } from './StyleguideElements';
import {
    NotImplementedTooltip,
    NotImplementedYetInject,
    NotImplementedYetTag,
    NotImplementedYetWrapper,
    notImplementedYetHandler,
} from 'components/Basic/NotImplementedYet/NotImplementedYet';
import { Button } from 'components/Forms/Button/Button';

export const StyleguideNotImplementedYet: FC = () => {
    return (
        <StyleguideSection className="flex flex-col gap-5" title="Not implemented yet">
            <StyleguideSubSection title="Toast message implementation">
                <p>Show toast message:</p>
                <div className="flex gap-2">
                    <Button size="small" type="button" onClick={notImplementedYetHandler}>
                        Button with not implemented onClick
                    </Button>
                    <Button size="small" type="button" variant="primary" onMouseEnter={notImplementedYetHandler}>
                        Button with not implemented onClick onMouseEnter
                    </Button>
                </div>
            </StyleguideSubSection>

            <StyleguideSubSection title='"Not implemented yet" tooltip'>
                <p>Simple hover tooltip:</p>
                <NotImplementedTooltip>
                    <Button size="small" type="button" variant="primary">
                        Hover over
                    </Button>
                </NotImplementedTooltip>
            </StyleguideSubSection>

            <StyleguideSubSection title='"Not implemented yet" wrapper'>
                <p>Wrap around element/s:</p>
                <NotImplementedYetWrapper>
                    <p>
                        SEO is a marketing tactic that costs five percent of overall budget but brings in more than 20
                        percent of revenue for many Fortune 500 companies, and yet very few of them prioritize it.
                        Instead, while slowly changing, too many CMOS still favor paid search, which costs 20 percent
                        and brings in… 20 percent. In search, businesses need to shift priorities. Now.
                    </p>
                    <p>
                        Link volume is one of the most powerful signals{' '}
                        <strong>search engines use to rank websites</strong>. There’s a way to use links to generate
                        better search results, but too many marketers rely on traditional (or even black hat) tactics
                        that just don’t work anymore.
                    </p>
                </NotImplementedYetWrapper>
            </StyleguideSubSection>

            <StyleguideSubSection title='"Not implemented yet" inject'>
                <p>Inject inside positioned element:</p>
                <div className="relative">
                    <p>
                        SEO is a marketing tactic that costs five percent of overall budget but brings in more than 20
                        percent of revenue for many Fortune 500 companies, and yet very few of them prioritize it.
                        Instead, while slowly changing, too many CMOS still favor paid search, which costs 20 percent
                        and brings in… 20 percent. In search, businesses need to shift priorities. Now.
                    </p>
                    <NotImplementedYetInject />
                </div>
            </StyleguideSubSection>

            <StyleguideSubSection title='"Not implemented yet" tag'>
                <div>
                    <h4>
                        Example feature
                        <NotImplementedYetTag />
                    </h4>
                </div>
            </StyleguideSubSection>
        </StyleguideSection>
    );
};
