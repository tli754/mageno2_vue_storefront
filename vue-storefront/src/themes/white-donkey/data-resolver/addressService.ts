import { DataResolverWd } from './dataResolverWd';
import Task from '@vue-storefront/core/lib/sync/types/Task'
import config from 'config';
import { processLocalizedURLAddress } from '@vue-storefront/core/helpers'
import { TaskQueue } from '@vue-storefront/core/lib/sync'
import getApiEndpointUrl from '@vue-storefront/core/helpers/getApiEndpointUrl';

const addressSuggestion = (query: string): Promise<Task> => {
  const url = processLocalizedURLAddress(getApiEndpointUrl(config.cart, 'address_suggest_endpoint').replace('{{query}}', query))
  return TaskQueue.execute({
    url: url,
    payload: {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
      mode: 'cors'
    },
    silent: false
  });
};

const addressDetails = (addressId: string): Promise<Task> => {
  const url = processLocalizedURLAddress(getApiEndpointUrl(config.cart, 'address_detail_endpoint').replace('{{addressId}}', addressId))
  return TaskQueue.execute({
    url: url,
    payload: {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
      mode: 'cors'
    },
    silent: false
  });
};

export const AddressService: DataResolverWd.AddressService = {
  addressSuggestion,
  addressDetails,
}
