/**
 * @package checkout
 */

import { mount } from '@vue/test-utils';
import orderStore from 'src/module/sw-order/state/order.store';

const existingCustomerId = '1234';
const notExistingCustomerId = '9876';

const responses = global.repositoryFactoryMock.responses;

responses.addResponse({
    method: 'Post',
    url: '/search/customer',
    status: 200,
    response: {
        data: [
            {
                id: existingCustomerId,
                attributes: {
                    id: existingCustomerId,
                },
                relationships: [],
            },
        ],
    },
});

async function createWrapper(customerId = null) {
    return mount(await wrapTestComponent('sw-order-create-initial', { sync: true }), {
        global: {
            mocks: {
                $route: {
                    query: {
                        customerId: customerId,
                    },
                },
            },
        },
    });
}

describe('src/module/sw-order/view/sw-order-create-initial', () => {
    let wrapper;

    beforeEach(async () => {
        wrapper = await createWrapper();

        if (Shopware.State.get('swOrder')) {
            Shopware.State.unregisterModule('swOrder');
        }

        Shopware.State.registerModule('swOrder', orderStore);

        await flushPromises();
    });

    afterEach(async () => {
        if (wrapper) {
            await wrapper.unmount();
        }

        await flushPromises();
    });

    it('should be a Vue.js component', async () => {
        expect(wrapper.vm).toBeTruthy();
    });

    it('should not load a customer if no customerId query parameter has been passed', async () => {
        // ToDo: Implement test
    });

    it('should not update the state if no customer is found', async () => {
        wrapper = await createWrapper(notExistingCustomerId);
        await flushPromises();

        const customer = Shopware.State.get('swOrder').customer;
        expect(customer).toBeNull();
    });

    it('should update the state if a customer is found', async () => {
        wrapper = await createWrapper(existingCustomerId);
        await flushPromises();

        const customer = Shopware.State.get('swOrder').customer;
        expect(customer).toEqual(expect.objectContaining({
            id: existingCustomerId,
        }));
    });
});
