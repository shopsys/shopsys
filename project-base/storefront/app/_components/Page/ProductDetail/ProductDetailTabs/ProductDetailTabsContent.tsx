'use client';

import { Tabs, TabsContent, TabsList, TabsListItem } from 'app/_components/Basic/Tabs/Tabs';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { ReactNode, useState } from 'react';

export type ProductDetailTabsContentProps = {
    description?: ReactNode;
    files?: ReactNode;
    parameters?: ReactNode;
    relatedProducts?: ReactNode;
};

export const ProductDetailTabsContent: FC<ProductDetailTabsContentProps> = ({
    description,
    files,
    parameters,
    relatedProducts,
}) => {
    const { t } = useTranslation();
    const [selectedTab, setSelectedTab] = useState(0);
    const [skipInitialAnimation, setSkipInitialAnimation] = useState(true);

    let tabIndex = 0;
    const parametersTabIndex = parameters ? ++tabIndex : -1;
    const relatedProductsTabIndex = relatedProducts ? ++tabIndex : -1;
    const filesTabIndex = files ? ++tabIndex : -1;

    return (
        <Webline>
            <Tabs
                className="flex flex-col gap-5"
                selectedIndex={selectedTab}
                onSelect={(index) => {
                    setSkipInitialAnimation(false);
                    setSelectedTab(index);
                }}
            >
                <TabsList>
                    <TabsListItem>{t('Overview')}</TabsListItem>

                    {parameters && <TabsListItem>{t('Parameters')}</TabsListItem>}

                    {relatedProducts && <TabsListItem>{t('Related Products')}</TabsListItem>}

                    {files && <TabsListItem>{t('Files')}</TabsListItem>}
                </TabsList>

                {description && (
                    <TabsContent
                        headingTextMobile={t('Overview')}
                        isActive={selectedTab === 0}
                        skipInitialAnimation={skipInitialAnimation}
                    >
                        {description}
                    </TabsContent>
                )}

                {parameters && (
                    <TabsContent headingTextMobile={t('Parameters')} isActive={selectedTab === parametersTabIndex}>
                        {parameters}
                    </TabsContent>
                )}

                {relatedProducts && (
                    <TabsContent
                        headingTextMobile={t('Related Products')}
                        isActive={selectedTab === relatedProductsTabIndex}
                    >
                        {relatedProducts}
                    </TabsContent>
                )}

                {files && (
                    <TabsContent headingTextMobile={t('Files')} isActive={selectedTab === filesTabIndex}>
                        {files}
                    </TabsContent>
                )}
            </Tabs>
        </Webline>
    );
};
