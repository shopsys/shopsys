import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { twJoin } from 'tailwind-merge';
import tinycolor from 'tinycolor2';

type ProductFlagProps = Omit<TypeSimpleFlagFragment, '__typename' | 'uuid'>;

export const ProductFlag: FC<ProductFlagProps> = ({ name, rgbColor }) => {
    return (
        <div
            style={{ backgroundColor: rgbColor || '#cdb3ff' }}
            className={twJoin(
                'z-flag text-text inline-flex rounded-sm px-2 py-1 text-xs',
                tinycolor(rgbColor).isDark() && 'text-textInverted',
            )}
        >
            {name}
        </div>
    );
};
