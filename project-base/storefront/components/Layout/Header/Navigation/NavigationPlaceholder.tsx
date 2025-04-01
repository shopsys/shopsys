import { NavigationProps } from './Navigation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { twJoin } from 'tailwind-merge';

export const NavigationPlaceholder: FC<NavigationProps> = ({ navigation, skeletonType }) => (
    <nav>
        <ul className="relative hidden w-full lg:flex">
            {navigation.map((navigationItem, index) => {
                const hasChildren = !!navigationItem.categoriesByColumns.length;

                return (
                    <li key={index} className="group">
                        <ExtendedNextLink
                            href={navigationItem.link}
                            skeletonType={skeletonType}
                            className={twJoin(
                                'font-secondary vl:text-base relative m-0 flex items-center p-5 text-sm font-bold group-first-of-type:pl-0',
                                'text-link-inverted no-underline',
                                'hover:text-link-inverted-hovered group-hover:text-link-inverted-hovered group-hover:no-underline hover:no-underline',
                                'active:text-link-inverted-hovered',
                                'disabled:text-link-inverted-disabled',
                            )}
                        >
                            {navigationItem.name}
                            {hasChildren && (
                                <ArrowIcon className="text-link-inverted group-hover:text-link-inverted-hovered ml-2 size-5 group-hover:rotate-180" />
                            )}
                        </ExtendedNextLink>
                    </li>
                );
            })}
        </ul>
    </nav>
);
