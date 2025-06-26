import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AnimateRotateDiv } from 'components/Basic/Animations/AnimateRotateDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { AnimatePresence } from 'framer-motion';
import { useState } from 'react';
import {
    Tab,
    TabList,
    TabListProps,
    TabPanel,
    TabPanelProps,
    TabProps,
    TabsProps,
    Tabs as TabsReact,
} from 'react-tabs';
import { twJoin } from 'tailwind-merge';
import { createAriaParameter } from 'utils/accessibility/createAriaParameter';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';

/**
 * In background-default of styled tab parts we are using - react-tabs components
 * https://github.com/reactjs/react-tabs
 */
type TabsContentProps = {
    headingTextMobile: string;
    isActive: boolean;
    skipInitialAnimation?: boolean;
};

type TabFC<T = unknown> = FC<T> & { tabsRole: string };

// this is hack for react-tabs bug,
// when passing ...props to lib component, react-tabs are complaining about ref type
type PropsWithRef<T> = T & { ref: any };

export const Tabs: TabFC<Partial<TabsProps>> = ({ children, className, ...props }) => (
    <TabsReact className={twJoin('xl:my-auto xl:max-w-7xl', className)} {...props}>
        {children}
    </TabsReact>
);

export const TabsList: TabFC<Partial<TabListProps>> = ({ children }) => (
    <TabList className="z-above hidden flex-row lg:flex lg:gap-5">{children}</TabList>
);

export const TabsListItem: TabFC<Partial<PropsWithRef<TabProps>>> = ({ children, className, ...props }) => (
    <Tab
        selectedClassName="isActive"
        tabIndex="0"
        className={twMergeCustom(
            'bg-background-more hover:bg-background-most font-secondary outline-border-success [&.isActive]:bg-background-default cursor-pointer rounded-2xl px-3 py-2 text-sm font-semibold select-none [&.isActive]:outline-1',
            'focus-visible:text-text-default! focus-visible:bg-orange-500! focus-visible:outline-none!',
            className,
        )}
        {...props}
    >
        {children}
    </Tab>
);

export const TabsContent: TabFC<TabsContentProps & Partial<PropsWithRef<TabPanelProps>>> = ({
    children,
    headingTextMobile,
    isActive,
    skipInitialAnimation = false,
    ...props
}) => {
    const [isActiveOnMobile, setIsActiveOnMobile] = useState<boolean | undefined>(false);
    const mobileTab = () => setIsActiveOnMobile(!isActiveOnMobile);
    const isLg = useMediaMin('lg');

    const handleKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
        if (e.key === 'Enter' || e.key === ' ') {
            mobileTab();
        }
    };

    return (
        <TabPanel
            forceRender
            className="flex flex-col flex-wrap lg:hidden [&.isActive]:flex [&.isActive]:lg:pt-5"
            selectedClassName="isActive"
            {...props}
        >
            <div
                aria-controls={createAriaParameter('tabs-content', headingTextMobile)}
                aria-expanded={isActiveOnMobile}
                className="bg-background-more font-secondary flex w-full cursor-pointer items-center justify-between rounded-xl p-3 text-sm font-semibold lg:hidden"
                role="button"
                tabIndex={0}
                onClick={mobileTab}
                onKeyDown={handleKeyDown}
            >
                {headingTextMobile}
                <AnimateRotateDiv className="flex items-start" condition={isActiveOnMobile}>
                    <ArrowIcon className={twJoin('text-text-default size-4 rotate-0 transition')} />
                </AnimateRotateDiv>
            </div>

            <div className="relative mt-3 w-full lg:mt-0" id={createAriaParameter('tabs-content', headingTextMobile)}>
                <AnimatePresence initial={false}>
                    {(isActiveOnMobile || (isActive && isLg)) && (
                        <AnimateCollapseDiv
                            className="!block"
                            initial={skipInitialAnimation ? 'open' : 'closed'}
                            keyName={`tabs-content-${headingTextMobile}`}
                        >
                            {children}
                        </AnimateCollapseDiv>
                    )}
                </AnimatePresence>
            </div>
        </TabPanel>
    );
};

// define element roles needed for react-tabs component
Tabs.tabsRole = 'Tabs';
TabsList.tabsRole = 'TabList';
TabsListItem.tabsRole = 'Tab';
TabsContent.tabsRole = 'TabPanel';
