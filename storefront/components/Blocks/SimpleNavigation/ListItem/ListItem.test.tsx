/**
 * @jest-environment jsdom
 */

import 'jest-styled-components';
import { expect, test } from '@jest/globals';
import ListItem from './ListItem';
import renderer from 'react-test-renderer';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';

test('test render list item', async () => {
    const component = renderer
        .create(
            <ShopsysGlobalProvider>
                <ListItem
                    listedItem={{
                        slug: 'item-slug',
                        image: null,
                        name: 'Item name',
                    }}
                />
            </ShopsysGlobalProvider>,
        )
        .toJSON();
    expect(component).toMatchSnapshot();
});
