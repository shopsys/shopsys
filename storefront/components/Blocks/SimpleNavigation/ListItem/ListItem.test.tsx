/**
 * @jest-environment jsdom
 */
import { ListItem } from './ListItem';
import { expect, test } from '@jest/globals';
import { ShopsysGlobalProvider } from 'context/ShopsysGlobalProvider/ShopsysGlobalProvider';
import 'jest-styled-components';
import renderer from 'react-test-renderer';

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
