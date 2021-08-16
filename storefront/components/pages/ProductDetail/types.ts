import { SlugType } from '../../../connectors/slug/Slug';
import { v4 as uuid } from 'uuid';

export interface ProductDetailType extends SlugType {
    uuid: typeof uuid;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    description: string;
    catalogNumber: string;
}
