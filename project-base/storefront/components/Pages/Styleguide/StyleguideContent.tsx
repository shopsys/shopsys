import { StyleguideButtons } from './StyleguideButtons';
import { StyleguideCheckboxes } from './StyleguideCheckboxes';
import { StyleguideColors } from './StyleguideColors';
import { StyleguideForms } from './StyleguideForms';
import { StyleguideIcons } from './StyleguideIcons';
import { StyleguideNotImplementedYet } from './StyleguideNotImplementedYet';
import { StyleguidePopups } from './StyleguidePopups';
import { StyleguideRadiogroup } from './StyleguideRadiogroup';
import { StyleguideSelects } from './StyleguideSelects';
import { StyleguideSpinboxes } from './StyleguideSpinboxes';
import { StyleguideTables } from './StyleguideTables';
import { StyleguideToasts } from './StyleguideToasts';
import { StyleguideTooltips } from './StyleguideTooltips';
import { StyleguideTypography } from './StyleguideTypography';
import { Webline } from 'components/Layout/Webline/Webline';

type StyleguideContentProps = { iconList?: string[]; tailwindColors?: Record<string, any> };

export const StyleguideContent: FC<StyleguideContentProps> = ({ iconList, tailwindColors }) => {
    return (
        <Webline className="mb-10 flex flex-col gap-10">
            {tailwindColors && <StyleguideColors tailwindColors={tailwindColors} />}
            <StyleguideTypography />
            <StyleguideButtons />
            <StyleguidePopups />
            <StyleguideToasts />
            <StyleguideTooltips />
            <StyleguideForms />
            <StyleguideSelects />
            <StyleguideRadiogroup />
            <StyleguideCheckboxes />
            <StyleguideSpinboxes />
            <StyleguideTables />
            {iconList && <StyleguideIcons iconList={iconList} />}
            <StyleguideNotImplementedYet />
        </Webline>
    );
};
