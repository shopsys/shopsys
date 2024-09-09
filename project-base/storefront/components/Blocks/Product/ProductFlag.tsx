import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { twJoin } from 'tailwind-merge';
import tinycolor from 'tinycolor2';

type ProductFlagProps = Omit<TypeSimpleFlagFragment, '__typename' | 'uuid'>;

export const ProductFlag: FC<ProductFlagProps> = ({ name, rgbColor }) => {
    return (
        <div
            style={{ backgroundColor: rgbColor || '#cdb3ff' }}
            className={twJoin(
                'inline-flex rounded py-1 px-2 text-xs text-text z-flag',
                tinycolor(rgbColor).isDark() && 'text-textInverted',
            )}
        >
            {name}
        </div>
    );
};
