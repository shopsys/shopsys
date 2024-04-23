import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { TIDs } from 'cypress/tids';

// TODO PRG
const dummyData = {
    phone: '+420 111 222 333',
    opening: 'Po - Út, 10 - 16 hod',
};

export const HeaderContact: FC = () => {
    return (
        <div className="order-2 ml-auto flex">
            <div className="relative flex flex-1 flex-col items-start py-4 pr-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex flex-wrap items-center gap-3 lg:flex-1 xl:justify-center">
                    <PhoneIcon className="w-5 text-secondary" />

                    <a
                        className="font-bold text-whiteSnow no-underline hover:text-whiteSnow"
                        href={'tel:' + dummyData.phone}
                        tid={TIDs.simple_header_contact}
                    >
                        {dummyData.phone}
                    </a>

                    <p className="hidden text-sm text-whiteSnow lg:block"> {dummyData.opening}</p>
                </div>
            </div>
        </div>
    );
};
