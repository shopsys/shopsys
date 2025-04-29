import { GrapesJs } from 'app/_components/Basic/UserText/GrapesJs';
import { getFormatDate } from 'app/_utils/formatting/getFormatDate';
import { Webline } from 'components/Layout/Webline/Webline';
import dayjs from 'dayjs';

const GrapesJSTemplatePage = async () => {
    const { formatDate } = await getFormatDate();

    return (
        <Webline>
            <h1>Blog or Article title</h1>
            <div className="mb-12 flex w-full flex-col">
                <div className="text-textAccent mb-2 text-left text-xs font-semibold">{formatDate(dayjs())}</div>
                <GrapesJs className="gjs-editable pt-4 pb-4" />
            </div>
        </Webline>
    );
};

export default GrapesJSTemplatePage;
