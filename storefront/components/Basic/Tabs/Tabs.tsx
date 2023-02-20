import { Icon } from '../Icon/Icon';
import {
    TabsContentInStyled,
    TabsContentMobileHeadingStyled,
    TabsContentStyled,
    TabsListItemStyled,
    TabsListStyled,
    TabsStyled,
} from './Tabs.style';
import React, { FC, useState } from 'react';
import { twJoin } from 'tailwind-merge';

/**
 * In background of styled tab parts we are using - react-tabs components
 * https://github.com/reactjs/react-tabs
 */
type TabsContentProps = {
    headingTextMobile: string;
    testIdentifier?: string;
};

type TabsListItemProps = {
    testIdentifier?: string;
};

type TabFC<T = unknown> = FC<T> & { tabsRole: string };

export const Tabs: TabFC = ({ children }) => <TabsStyled>{children}</TabsStyled>;

export const TabsList: TabFC = ({ children }) => <TabsListStyled>{children}</TabsListStyled>;

export const TabsListItem: TabFC<TabsListItemProps> = ({ children, testIdentifier }) => (
    <TabsListItemStyled selectedClassName="active" data-testid={testIdentifier}>
        {children}
    </TabsListItemStyled>
);

export const TabsContent: TabFC<TabsContentProps> = ({ children, headingTextMobile, testIdentifier }) => {
    const [isActiveOnMobile, setIsActiveOnMobile] = useState<boolean | undefined>(false);
    const mobileTab = () => setIsActiveOnMobile(!isActiveOnMobile);

    return (
        <TabsContentStyled forceRender selectedClassName="active" data-testid={testIdentifier}>
            <TabsContentMobileHeadingStyled onClick={mobileTab}>
                {headingTextMobile}
                <Icon
                    iconType="icon"
                    icon="Arrow"
                    width={18}
                    height={18}
                    className={twJoin('rotate-0 transition', isActiveOnMobile && '-rotate-180 ')}
                />
            </TabsContentMobileHeadingStyled>
            <TabsContentInStyled isActiveOnMobile={isActiveOnMobile}>{children}</TabsContentInStyled>
        </TabsContentStyled>
    );
};

// define element roles needed for react-tabs component
Tabs.tabsRole = 'Tab';
TabsList.tabsRole = 'TabList';
TabsListItem.tabsRole = 'Tabs';
TabsContent.tabsRole = 'TabPanel';
