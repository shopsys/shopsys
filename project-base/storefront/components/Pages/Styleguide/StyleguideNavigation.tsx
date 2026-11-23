import { twJoin } from 'tailwind-merge';

export type StyleguideNavigationGroup = {
    title: string;
    sections: { id: string; title: string }[];
};

type StyleguideNavigationProps = {
    activeSectionId: string | null;
    groups: StyleguideNavigationGroup[];
};

export const StyleguideNavigation: FC<StyleguideNavigationProps> = ({ activeSectionId, groups }) => (
    <nav
        aria-label="Styleguide sections"
        className="vl:sticky vl:top-[calc(var(--sticky-navigation-offset,0px)+var(--spacing-5))] flex vl:max-h-[calc(100vh-var(--sticky-navigation-offset,0px)-var(--spacing-10))] flex-col gap-5 vl:overflow-y-auto vl:overscroll-contain rounded-card bg-background-more px-2 py-4"
    >
        <div className="grid grid-cols-2 vl:grid-cols-1 gap-5 md:grid-cols-3">
            {groups.map((group) => (
                // in the single column a line separates the groups, so a title starts its group instead of ending the previous one
                <div
                    key={group.title}
                    className="flex flex-col gap-2 vl:border-border-less vl:border-t vl:pt-4 vl:first:border-t-0 vl:first:pt-0"
                >
                    <p className="px-3 font-bold text-text-default text-xs uppercase tracking-wider">{group.title}</p>

                    <ul className="flex flex-col gap-0.5">
                        {group.sections.map((section) => (
                            <StyleguideNavigationLink
                                key={section.id}
                                id={section.id}
                                isActive={section.id === activeSectionId}
                                title={section.title}
                            />
                        ))}
                    </ul>
                </div>
            ))}
        </div>
    </nav>
);

type StyleguideNavigationLinkProps = {
    id: string;
    isActive: boolean;
    title: string;
};

const StyleguideNavigationLink: FC<StyleguideNavigationLinkProps> = ({ id, isActive, title }) => (
    <li>
        <a
            aria-current={isActive ? 'location' : undefined}
            href={`#${id}`}
            className={twJoin(
                // a row with a hover background reads as clickable, unlike the plain group title above it
                'block rounded-control px-3 py-1.5 text-sm no-underline transition-colors hover:bg-background-default hover:no-underline',
                isActive
                    ? 'bg-background-default font-semibold text-text-accent hover:text-text-accent'
                    : 'text-text-default hover:text-text-accent',
            )}
        >
            {title}
        </a>
    </li>
);
