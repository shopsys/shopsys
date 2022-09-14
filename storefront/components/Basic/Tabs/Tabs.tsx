import {
    TabsContentInStyled,
    TabsContentMobileHeadingStyled,
    TabsContentStyled,
    TabsIconStyled,
    TabsListItemStyled,
    TabsListStyled,
    TabsStyled,
} from './Tabs.style';
import React, { FC, useState } from 'react';

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
                <TabsIconStyled iconType="icon" icon="Arrow" isActive={isActiveOnMobile} alt="" />
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
