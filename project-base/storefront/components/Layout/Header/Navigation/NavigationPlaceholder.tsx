import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { NavigationItemContent } from 'components/Layout/Header/Navigation/NavigationItemContent';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import type { NavigationProps } from './Navigation';
import { isNavigationItemLink, isNavigationItemWithCategories } from './navigationUtils';

const SKELETON_COUNT = 5;

type NavigationPlaceholderProps = {
    navigation?: NavigationProps['navigation'];
};

export const NavigationPlaceholder: FC<NavigationPlaceholderProps> = ({ navigation }) => {
    return (
        <nav
            aria-hidden={navigation?.length ? undefined : true}
            className="focus-visible:outline-hidden"
            id="main-navigation"
            tabIndex={-1}
        >
            <ul className="mask-[linear-gradient(to_right,black_calc(100%-32px),transparent_100%)] relative vl:flex hidden w-full overflow-hidden">
                {navigation?.length
                    ? navigation.map((navigationItem, index) => {
                          const link = navigationItem.link;
                          const isLink = isNavigationItemLink(navigationItem);
                          const isDropdownTrigger = isNavigationItemWithCategories(navigationItem);
                          const isExternalLink =
                              link !== null && (link.startsWith('http://') || link.startsWith('https://'));
                          const navigationItemClassName =
                              'relative m-0 flex cursor-pointer items-center whitespace-nowrap rounded-sm border-0 bg-transparent p-5 font-secondary font-semibold text-link-inverted-default text-sm vl:text-base no-underline hover:text-link-inverted-hovered hover:no-underline active:text-link-inverted-hovered disabled:text-link-inverted-disabled group-first-of-type:pl-0 group-last-of-type:pr-0 group-hover:text-link-inverted-hovered group-hover:no-underline';
                          const content = (
                              <NavigationItemContent
                                  isDropdownTrigger={isDropdownTrigger}
                                  isMenuOpened={false}
                                  name={navigationItem.name}
                              />
                          );

                          return (
                              <li key={index} className="group">
                                  {isLink && link !== null ? (
                                      <ExtendedNextLink
                                          className={navigationItemClassName}
                                          href={link}
                                          rel={isExternalLink ? 'nofollow noreferrer noopener' : undefined}
                                          skeletonType={
                                              getPageTypeKey(navigationItem.routeName) || DEFAULT_SKELETON_TYPE
                                          }
                                          target={isExternalLink ? '_blank' : undefined}
                                      >
                                          {content}
                                      </ExtendedNextLink>
                                  ) : (
                                      <button className={navigationItemClassName} type="button">
                                          {content}
                                      </button>
                                  )}
                              </li>
                          );
                      })
                    : [...Array(SKELETON_COUNT)].map((_, index) => (
                          <li key={index} className="relative m-0 flex items-center p-5 first:pl-0">
                              <Skeleton className="h-6 w-28 bg-skeleton-default" />
                          </li>
                      ))}
            </ul>
        </nav>
    );
};
