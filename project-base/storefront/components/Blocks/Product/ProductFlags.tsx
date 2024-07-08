import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { twJoin } from 'tailwind-merge';
import tinycolor from 'tinycolor2';

type ProductFlagsProps = { flags: TypeSimpleFlagFragment[] };

export const ProductFlags: FC<ProductFlagsProps> = ({ flags }) => {
    if (!flags.length) {
        return null;
    }

    return (
        <>
            {flags.map(({ name, rgbColor }, index) => (
                <div
                    key={index}
                    style={{ backgroundColor: rgbColor || '#cdb3ff' }}
                    className={twJoin(
                        'mb-1 inline-flex rounded py-1 px-2 text-xs text-black z-flag',
                        tinycolor(rgbColor).isDark() && 'text-whiteSnow',
                    )}
                >
                    {name}
                </div>
            ))}
        </>
    );
};
