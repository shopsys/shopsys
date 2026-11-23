import { StyleguideMatrix, StyleguideMatrixLabel, StyleguidePreview, StyleguideSubSection } from './StyleguideElements';

const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5'] as const;

const getHeadingRow = (HeadingTag: (typeof HEADING_LEVELS)[number]) => ({
    label: <StyleguideMatrixLabel name={HeadingTag} />,
    cells: [
        <HeadingTag key="tag">Heading {HeadingTag}</HeadingTag>,
        <div key="class" className={HeadingTag}>
            Heading .{HeadingTag}
        </div>,
    ],
});

const headingRows = HEADING_LEVELS.map(getHeadingRow);

export const StyleguideTypography: FC = () => (
    <>
        <StyleguideSubSection
            description="Use the tag for the document outline and the class when the text only looks like a heading."
            title="Headings"
        >
            <StyleguideMatrix columns={['Level', 'Tag', 'Class']} rows={headingRows} />
        </StyleguideSubSection>

        <StyleguideSubSection title="Body text">
            <StyleguidePreview className="flex max-w-3xl flex-col gap-3">
                <p>
                    Regular paragraph. Lorem ipsum, dolor sit amet consectetur adipisicing elit. Tempora consectetur at
                    necessitatibus obcaecati optio quas dicta debitis et quod provident harum, voluptatem perferendis
                    ullam soluta temporibus corrupti, alias velit nobis.
                </p>
                <p className="text-sm">
                    Small text (text-sm) for supporting information, hints and dense layouts such as tables or cards.
                </p>
            </StyleguidePreview>
        </StyleguideSubSection>
    </>
);
