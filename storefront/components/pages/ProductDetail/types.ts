import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { SlugType } from '../../../connectors/slug/Slug';
import { v4 as uuid } from 'uuid';

export interface ProductDetailType extends SlugType, BreadcrumbType {
    uuid: typeof uuid;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    description: string;
    catalogNumber: string;
}
