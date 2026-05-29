import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { HomeIcon } from 'components/Basic/Icon/HomeIcon';
import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';

const ErrorPageNavigationLink: FC<{ item: ErrorPageNavigationItem }> = ({ item }) => {
    const Icon = item.icon;

    return (
        <ExtendedNextLink
            href={item.href}
            skeletonType={item.skeletonType}
            className={twMergeCustom(
                'group flex h-full items-center gap-4 rounded-lg border border-background-more bg-background-more p-4 text-text-default no-underline transition hover:no-underline',
                'hover:border-border-less hover:bg-background-default hover:text-text-default',
                'focus-visible:outline-2 focus-visible:outline-border-default focus-visible:-outline-offset-2',
            )}
        >
            <span className="flex size-10 shrink-0 items-center justify-center rounded-md bg-background-default text-text-accent">
                <Icon className="size-5" />
            </span>

            <span className="min-w-0 flex-1">
                <span className="block font-secondary font-semibold text-sm">{item.title}</span>
                <span className="mt-1 block text-sm text-text-less leading-relaxed">{item.description}</span>
            </span>
        </ExtendedNextLink>
    );
};

const ErrorPageNavigation: FC<{ ariaLabel: string; items: readonly ErrorPageNavigationItem[] }> = ({
    ariaLabel,
    items,
}) => (
    <nav aria-label={ariaLabel} className="w-full text-left">
        <div className="mt-4 grid gap-3 md:grid-cols-[repeat(auto-fit,minmax(240px,1fr))]">
            {items.map((item) => (
                <ErrorPageNavigationLink item={item} key={item.href} />
            ))}
        </div>
    </nav>
);

type ErrorPageNavigationItem = {
    href: string;
    title: string;
    description: string;
    icon: SvgFC;
    skeletonType?: PageType;
};

export const ErrorPageUsefulLinks: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [homepageUrl, catalogUrl, storesUrl, contactFormUrl] = getInternationalizedStaticUrls(
        ['/', '/catalog', '/stores', '/contact-form'],
        url,
    );

    const navigationItems = [
        {
            href: homepageUrl,
            title: t('Back to shop'),
            description: t('Return to the homepage and continue from the start.'),
            icon: HomeIcon,
            skeletonType: 'homepage',
        },
        {
            href: catalogUrl,
            title: t('Shop by category'),
            description: t('Browse the catalog and find another route to the products.'),
            icon: MenuIcon,
            skeletonType: 'catalog',
        },
        {
            href: storesUrl,
            title: t('Stores'),
            description: t('Check store addresses and opening hours.'),
            icon: MarkerIcon,
            skeletonType: 'stores',
        },
        {
            href: contactFormUrl,
            title: t('Write to us'),
            description: t('Tell us what went missing and we will help you find it.'),
            icon: MailIcon,
            skeletonType: 'contact',
        },
    ] as const satisfies readonly ErrorPageNavigationItem[];

    return <ErrorPageNavigation ariaLabel={t('Useful links for continuing shopping')} items={navigationItems} />;
};
