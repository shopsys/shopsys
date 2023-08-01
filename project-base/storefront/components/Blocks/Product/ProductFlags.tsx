import { SimpleFlagFragmentApi } from 'graphql/generated';
import { twJoin } from 'tailwind-merge';
import tinycolor from 'tinycolor2';

type ProductFlagsProps = { flags: SimpleFlagFragmentApi[] };

const TEST_IDENTIFIER = 'blocks-product-flags-';

export const ProductFlags: FC<ProductFlagsProps> = ({ flags }) => {
    if (!flags.length) {
        return null;
    }

    return (
        <>
            {flags.map(({ name, rgbColor }, key) => (
                <div
                    className={twJoin(
                        'mb-1 mr-auto inline-flex rounded-sm py-1 px-2 text-xs uppercase text-black',
                        tinycolor(rgbColor).isDark() && 'text-whitesmoke',
                    )}
                    style={{ backgroundColor: rgbColor || '#cdb3ff' }}
                    key={key}
                    data-testid={TEST_IDENTIFIER + key}
                >
                    {name}
                </div>
            ))}
        </>
    );
};
