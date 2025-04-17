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
                    <PhoneIcon className="text-text-inverted w-5" />

                    <a
                        className="text-text-inverted hover:text-text-inverted focus-visible:ring-button-inverted-border-default rounded-md font-bold no-underline focus-visible:ring-1 focus-visible:ring-offset-2"
                        href={'tel:' + dummyData.phone}
                        tid={TIDs.simple_header_contact}
                    >
                        {dummyData.phone}
                    </a>

                    <p className="text-text-inverted hidden text-sm lg:block"> {dummyData.opening}</p>
                </div>
            </div>
        </div>
    );
};
