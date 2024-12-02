import { Url, getInternationalizedStaticUrl } from './getInternationalizedStaticUrl';
import { SameLengthOutput } from 'types/SameLengthOutput';

export const getInternationalizedStaticUrls = <InputUrls extends Url[]>(urls: [...InputUrls]) => {
    return urls.map((url) => getInternationalizedStaticUrl(url)) as SameLengthOutput<InputUrls>;
};
