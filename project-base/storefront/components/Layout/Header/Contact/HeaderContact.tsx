import { isElementVisible } from 'helpers/isElementVisible';
import { PhoneIcon } from 'components/Basic/Icon/IconsSvg';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useState } from 'react';

// TODO PRG
const dummyData = {
    phone: '+420 111 222 333',
    opening: 'Po - Út, 10 - 16 hod',
};

const TEST_IDENTIFIER = 'layout-header-contact';

export const HeaderContact: FC = () => {
    const { width } = useGetWindowSize();
    const [areContactHoursVisible, setAreContactHoursVisible] = useState(true);
    useResizeWidthEffect(
        width,
        mobileFirstSizes.lg,
        () => setAreContactHoursVisible(true),
        () => setAreContactHoursVisible(false),
        () => setAreContactHoursVisible(isElementVisible([{ min: 0, max: 769 }], width)),
    );

    return (
        <div className="order-2 ml-auto flex" data-testid={TEST_IDENTIFIER}>
            <div className="relative flex flex-1 flex-col items-start bg-primary py-4 pr-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex flex-wrap items-center lg:flex-1 xl:justify-center">
                    <PhoneIcon className="mr-3 w-5 text-orange" />
                    <a className="font-bold text-creamWhite no-underline lg:mr-4" href={'tel:' + dummyData.phone}>
                        {dummyData.phone}
                    </a>
                    {areContactHoursVisible ? (
                        <p className="m-0 text-sm text-creamWhite"> {dummyData.opening}</p>
                    ) : null}
                </div>
            </div>
        </div>
    );
};
