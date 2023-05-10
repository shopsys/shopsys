import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useState } from 'react';

const TEST_IDENTIFIER = 'layout-footer-footercontact';

export const FooterContact: FC = () => {
    const t = useTypedTranslationFunction();
    const [isDesktop, setIsDesktop] = useState(false);
    const { width } = useGetWindowSize();

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsDesktop(true),
        () => setIsDesktop(false),
    );

    return (
        <div
            className="flex flex-col items-start lg:items-center vl:w-1/4 vl:items-start vl:pl-5"
            data-testid={TEST_IDENTIFIER}
        >
            {isDesktop && (
                <>
                    <Heading
                        type="h4"
                        className="pointer-events-none mb-6 flex cursor-pointer items-center justify-between uppercase text-white"
                    >
                        {t('Follow Us')}
                    </Heading>
                    <div className="mb-6 flex h-24 w-full max-w-md overflow-hidden rounded-xl border-2 border-greyLight">
                        <FooterContactSocialsItem href="#">
                            <Icon iconType="icon" icon="Instagram" className="w-8 text-white" />
                        </FooterContactSocialsItem>
                        <FooterContactSocialsItem href="#">
                            <Icon iconType="image" icon="facebook" className="w-8" alt={t('Facebook')} />
                        </FooterContactSocialsItem>
                        <FooterContactSocialsItem href="#">
                            <Icon iconType="icon" icon="Youtube" className="w-11 text-[#d93738]" />
                        </FooterContactSocialsItem>
                    </div>
                </>
            )}
            <div className="-mb-2 flex flex-wrap gap-10">
                <FooterContactLangsItem href="#" text={t('Czechia')}>
                    <Icon iconType="image" icon="cz" width={24} height={16} alt={t('Czechia')} />
                </FooterContactLangsItem>
                <FooterContactLangsItem href="#" text={t('Slovakia')}>
                    <Icon iconType="image" icon="sk" width={24} height={16} alt={t('Slovakia')} />
                </FooterContactLangsItem>
            </div>
        </div>
    );
};

const FooterContactSocialsItem: FC<{ href: string }> = ({ children, href }) => (
    <a className="flex h-full w-1/3 items-center justify-center first:border-none" href={href}>
        {children}
    </a>
);

const FooterContactLangsItem: FC<{ href: string; text: string }> = ({ children, href, text }) => (
    <a className="mb-2 flex items-center hover:text-greyLight hover:no-underline" href={href}>
        {children}
        <span className="ml-2 text-sm text-greyLight">{text}</span>
    </a>
);
