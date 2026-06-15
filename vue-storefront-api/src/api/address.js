import { apiStatus, apiError } from '../lib/util';
import { Router } from 'express';
import AddressProxy from "../platform/magento2/address";

export default ({config, db}) => {
  const addressApi = Router();

  addressApi.get('/suggest', (req, res) => {
    const addressProxy = new AddressProxy(config, req);
    const query = req.query || {};
    if (!query.q) {
      return apiStatus(res, 'search query is required', 500);
    }

    addressProxy.list(query).then((result) => {
      const addressList = [];
      if (result.addresses && result.addresses.length > 0) {
        for (const address of result.addresses) {
          if (address.address_id && address.full_address) {
            addressList.push({address: address.full_address, id: address.address_id });
          }
        }
      }
      apiStatus(res, addressList, 200);
    }).catch(err => {
      apiError(res, err);
    })
  });

  addressApi.get('/details', (req, res) => {
    const addressProxy = new AddressProxy(config, req);
    const query = req.query || {};
    if (!query.id) {
      return apiStatus(res, 'address id is required', 500);
    }

    addressProxy.details(query.id).then((result) => {
      const address = {};
      if (result.success && result.address) {
        address.street = `${result.address.street_number || ''} ${result.address.street || ''} ${result.address.street_type || ''}`;
        address.suburb = result.address.suburb || '';
        address.city = result.address.city || '';
        address.postcode = result.address.postcode || '';
      }
      apiStatus(res, address, 200);
    }).catch(err => {
      apiError(res, err);
    })
  });

  return addressApi;
}
