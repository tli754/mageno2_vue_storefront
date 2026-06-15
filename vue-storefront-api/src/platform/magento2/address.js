import AbstractAddressProxy from '../abstract/address';
import cache from "../../lib/cache-instance";
var request = require('request');

class AddressProxy extends AbstractAddressProxy {
  constructor (config, req) {
    super(config, req);
  }

  details(id) {
    const options = this._config.address || {};
    return this.setToken().then((token)=>{
      const init = {
        url: `${options.addressSuggestionUrl}/${id}`,
        method: 'GET',
        json: true,
        body: '',
        headers: {
          'client_id': options.clientId,
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'user_name': options.userName,
        },
      };

      return new Promise(function (resolve, reject) {
        request(init, function(error, response, body) {
          if (error) {
            reject(error)
          }
          resolve(body);
        });
      });
    });
  }

  list(query) {
    const count = query.c || 10;
    const searchQuery = encodeURIComponent(query.q);
    const options = this._config.address || {};

    return this.setToken().then((token)=>{
      const init = {
        url: `${options.addressSuggestionUrl}?q=${searchQuery}&count=${count}`,
        method: 'GET',
        json: true,
        body: '',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      };

      return new Promise(function (resolve, reject) {
        request(init, function(error, response, body) {
          if (error) {
            reject(error)
          }
          resolve(body);
        });
      });
    });
  }

  setToken =  async function() {
    let token = await cache.get('api:address-token');
    if (token === null) {
      const tokenData = await this.authorization();
      if (tokenData) {
        const data = JSON.parse(tokenData);
        if (data.access_token) {
          cache.set('api:address-token', data.access_token, ['address'], {timeout: (data.expires_in || 86399)});
          token = data.access_token;
        }
      }
    }

    return token;
  }

  authorization() {
    const options = this._config.address || {};
    const uri = `${options.authenticationTokenUrl}?client_id=${options.clientId}&client_secret=${options.clientSecret}&grant_type=client_credentials`;
    return new Promise(function (resolve, reject) {
      request.post(uri, function(error, response, body) {
        if (error) {
          reject(error)
        }
        resolve(body);
      });
    });
  }
}

export default AddressProxy
