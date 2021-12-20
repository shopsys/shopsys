import {
    ContactContentStyled,
    ContactHours,
    ContactWrapperStyled,
    HeaderContactIconStyled,
    HeaderContactStyled,
    PhoneNumberStyled,
} from './HeaderContact.style';
import { FC, useState } from 'react';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';

// TODO PRG
const dummyData = {
    phone: '+420 111 222 333',
    opening: 'Po - Út, 10 - 16 hod',
};

const HeaderContact: FC = () => {
    const testIdentifier = 'layout-header-contact';

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
        <HeaderContactStyled data-testid={testIdentifier}>
            <ContactWrapperStyled>
                <ContactContentStyled>
                    <HeaderContactIconStyled iconType="icon" icon="Phone" />
                    <PhoneNumberStyled href={'tel:' + dummyData.phone}>{dummyData.phone}</PhoneNumberStyled>
                    {areContactHoursVisible ? <ContactHours> {dummyData.opening}</ContactHours> : null}
                </ContactContentStyled>
            </ContactWrapperStyled>
        </HeaderContactStyled>
    );
};

export default HeaderContact;
