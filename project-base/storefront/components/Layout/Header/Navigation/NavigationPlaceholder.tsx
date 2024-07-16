import { NavigationProps } from './Navigation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { twJoin } from 'tailwind-merge';

export const NavigationPlaceholder: FC<NavigationProps> = ({ navigation }) => (
    <ul className="relative hidden w-full lg:flex">
        {navigation.map((navigationItem, index) => {
            const hasChildren = !!navigationItem.categoriesByColumns.length;

            return (
                <li key={index} className="group">
                    <ExtendedNextLink
                        href={navigationItem.link}
                        className={twJoin(
                            'relative m-0 flex items-center px-6 xl:px-5 py-4 group-first-of-type:pl-0 text-sm font-bold uppercase text-white no-underline hover:text-orangeLight hover:no-underline group-hover:text-orangeLight group-hover:no-underline vl:text-base',
                        )}
                    >
                        {navigationItem.name}
                        {hasChildren && (
                            <ArrowIcon className="ml-2 text-white group-hover:rotate-180 group-hover:text-orangeLight" />
                        )}
                    </ExtendedNextLink>
                </li>
            );
        })}
    </ul>
);
