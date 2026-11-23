import { Webline } from 'components/Layout/Webline/Webline';
import { ErrorDebugSettingsContent } from 'components/Pages/ErrorPage/ErrorDebugSettingsContent';
import { createRef, ReactNode, RefObject, useMemo } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isEnvironment } from 'utils/isEnvironment';
import { useHashNavigation } from 'utils/ui/useHashNavigation';
import { StyleguideButtons } from './StyleguideButtons';
import { StyleguideCheckboxes } from './StyleguideCheckboxes';
import { StyleguideColors } from './StyleguideColors';
import { StyleguideCode, StyleguideSection } from './StyleguideElements';
import { StyleguideForms } from './StyleguideForms';
import { StyleguideIconButtons } from './StyleguideIconButtons';
import { StyleguideIcons } from './StyleguideIcons.generated';
import { StyleguideInfobox } from './StyleguideInfobox';
import { StyleguideLinks } from './StyleguideLinks';
import { StyleguideNavigation, StyleguideNavigationGroup } from './StyleguideNavigation';
import { StyleguideNotImplementedYet } from './StyleguideNotImplementedYet';
import { StyleguidePopups } from './StyleguidePopups';
import { StyleguideRadiogroup } from './StyleguideRadiogroup';
import { StyleguideRadius } from './StyleguideRadius';
import { StyleguideSelects } from './StyleguideSelects';
import { StyleguideSpinboxes } from './StyleguideSpinboxes';
import { StyleguideTables } from './StyleguideTables';
import { StyleguideToasts } from './StyleguideToasts';
import { StyleguideTooltips } from './StyleguideTooltips';
import { StyleguideTypography } from './StyleguideTypography';
import { StyleguideViewportInfo } from './StyleguideViewportInfo';
import { ThemeColorBlock } from './themeColors';

const STYLEGUIDE_GROUPS = [
    'Foundations',
    'Actions',
    'Forms',
    'Feedback and overlays',
    'Data display',
    'Development',
] as const;

type StyleguideSectionDefinition = {
    id: string;
    group: (typeof STYLEGUIDE_GROUPS)[number];
    title: string;
    description: ReactNode;
    content: ReactNode;
};

type StyleguideSectionWithRef = StyleguideSectionDefinition & { ref: RefObject<HTMLElement | null> };

const getStyleguideSections = (
    themeColorBlocks: ThemeColorBlock[] | undefined,
    errorDebugSettingsTitle: string,
): StyleguideSectionDefinition[] => [
    {
        id: 'typography',
        group: 'Foundations',
        title: 'Typography',
        description:
            'Heading levels and body text. Every heading tag has a matching class for text that must look like a heading without being one.',
        content: <StyleguideTypography />,
    },
    ...(themeColorBlocks
        ? [
              {
                  id: 'colors',
                  group: 'Foundations' as const,
                  title: 'Colors',
                  description: (
                      <>
                          Theme tokens read from <StyleguideCode>styles/theme.css</StyleguideCode>, in the blocks and
                          order of the file. Shown only in development.
                      </>
                  ),
                  content: <StyleguideColors themeColorBlocks={themeColorBlocks} />,
              },
          ]
        : []),
    {
        id: 'radius',
        group: 'Foundations',
        title: 'Radius',
        description: 'Corner radius scale and the component tokens that use it.',
        content: <StyleguideRadius />,
    },
    {
        id: 'icons',
        group: 'Foundations',
        title: 'Icons',
        description: (
            <>
                All icons from <StyleguideCode>components/Basic/Icon</StyleguideCode>. Regenerate the list with{' '}
                <StyleguideCode>make generate-icons-for-styleguide</StyleguideCode>.
            </>
        ),
        content: <StyleguideIcons />,
    },
    {
        id: 'buttons',
        group: 'Actions',
        title: 'Buttons',
        description:
            'Button, LinkButton and SubmitButton share variants and sizes. Pick the variant by priority and the size by placement.',
        content: <StyleguideButtons />,
    },
    {
        id: 'icon-buttons',
        group: 'Actions',
        title: 'Icon buttons',
        description:
            'Buttons with only an icon. The title is the accessible name, and the scale is the same on every pointer type.',
        content: <StyleguideIconButtons />,
    },
    {
        id: 'links',
        group: 'Actions',
        title: 'Links and text actions',
        description:
            'Text that navigates (Link) or runs an action (TextButton, ShowMoreButton). Placement and tone set the look.',
        content: <StyleguideLinks />,
    },
    {
        id: 'forms',
        group: 'Forms',
        title: 'Form layout',
        description: 'Form wrappers with validated text inputs. Submit the empty form to see the error state.',
        content: <StyleguideForms />,
    },
    {
        id: 'selects',
        group: 'Forms',
        title: 'Selects',
        description:
            'A select picks one option, a multiselect picks more. Both can filter the options by typing (with search); the select also loads more options on scroll.',
        content: <StyleguideSelects />,
    },
    {
        id: 'radio-buttons',
        group: 'Forms',
        title: 'Radio buttons',
        description: 'A radio button group controlled by a form, with one disabled option.',
        content: <StyleguideRadiogroup />,
    },
    {
        id: 'checkboxes',
        group: 'Forms',
        title: 'Checkboxes',
        description: 'Regular and color checkboxes in all states, and the additional services built from them.',
        content: <StyleguideCheckboxes />,
    },
    {
        id: 'spinbox',
        group: 'Forms',
        title: 'Spinbox',
        description: 'Quantity input with step buttons, limited to its minimum and maximum.',
        content: <StyleguideSpinboxes />,
    },
    {
        id: 'infobox',
        group: 'Feedback and overlays',
        title: 'Infobox',
        description: 'Static information inside the page content.',
        content: <StyleguideInfobox />,
    },
    {
        id: 'toasts',
        group: 'Feedback and overlays',
        title: 'Toasts',
        description: 'Short messages about the result of an action. They close on their own.',
        content: <StyleguideToasts />,
    },
    {
        id: 'tooltips',
        group: 'Feedback and overlays',
        title: 'Tooltips',
        description: 'Hover or focus an element to see its tooltip in each placement.',
        content: <StyleguideTooltips />,
    },
    {
        id: 'popups',
        group: 'Feedback and overlays',
        title: 'Popups',
        description: 'Modal dialog rendered into the portal.',
        content: <StyleguidePopups />,
    },
    {
        id: 'tables',
        group: 'Data display',
        title: 'Tables',
        description: 'Table cells with alignment and wrapping options, and a key and value table.',
        content: <StyleguideTables />,
    },
    {
        id: 'not-implemented-yet',
        group: 'Development',
        title: 'Not implemented yet',
        description: 'Helpers that mark features which are not finished yet.',
        content: <StyleguideNotImplementedYet />,
    },
    ...(isEnvironment('development')
        ? [
              {
                  id: 'error-debug-settings',
                  group: 'Development' as const,
                  title: errorDebugSettingsTitle,
                  description: 'Debug tools for error handling. Shown only in development.',
                  content: <ErrorDebugSettingsContent />,
              },
          ]
        : []),
];

const getNavigationGroups = (sections: StyleguideSectionDefinition[]): StyleguideNavigationGroup[] =>
    STYLEGUIDE_GROUPS.map((group) => ({
        title: group,
        sections: sections.filter((section) => section.group === group),
    })).filter((group) => group.sections.length > 0);

type StyleguideContentProps = { themeColorBlocks?: ThemeColorBlock[] };

export const StyleguideContent: FC<StyleguideContentProps> = ({ themeColorBlocks }) => {
    const { t } = useTranslation();

    // useHashNavigation reads the sections once, so they and their refs must stay stable between renders
    const sections = useMemo<StyleguideSectionWithRef[]>(
        () =>
            getStyleguideSections(themeColorBlocks, t('Error Debug Settings')).map((section) => ({
                ...section,
                ref: createRef<HTMLElement>(),
            })),
        [],
    );
    const navigationGroups = useMemo(() => getNavigationGroups(sections), [sections]);
    const { activeSection } = useHashNavigation(sections);

    return (
        <Webline className="mb-10 flex flex-col gap-8">
            <header className="flex flex-col items-start gap-6 border-border-less border-b pb-6">
                <div className="flex flex-col gap-2">
                    <h1>Styleguide</h1>
                    <p className="text-text-less">
                        The shared storefront components in their variants, sizes and states. Use it to compare
                        components side by side and to check them on every breakpoint before you use them on a page.
                    </p>
                </div>

                <StyleguideViewportInfo />
            </header>

            <div className="flex vl:grid vl:grid-cols-[14rem_minmax(0,1fr)] flex-col vl:items-start gap-10">
                <StyleguideNavigation activeSectionId={activeSection} groups={navigationGroups} />

                <div className="flex min-w-0 flex-col gap-20">
                    {sections.map((section) => (
                        <StyleguideSection
                            key={section.id}
                            description={section.description}
                            group={section.group}
                            id={section.id}
                            ref={section.ref}
                            title={section.title}
                        >
                            {section.content}
                        </StyleguideSection>
                    ))}
                </div>
            </div>
        </Webline>
    );
};
