import * as types from './mutation-types'
import getCurrentConfigurationFromTotals from './helpers/getCurrentConfigurationFromTotals'
import { AddressService } from 'theme/data-resolver/addressService'
import { CartService } from '@vue-storefront/core/data-resolver'

const actions = {
  async configureProduct(context, {product}) {
    if (product.type_id === 'simple') {
      const configuration = getCurrentConfigurationFromTotals(product)
      const parentProduct = await context.dispatch('product/findConfigurableParent', {
        product,
        configuration
      }, {root: true})
      return context.dispatch('cart/updateItem', {product: parentProduct}, {root: true})
    }

    return Promise.resolve()
  },
  openEditMode(context, {product, selectedOptions}) {
    context.commit(types.CART_OPEN_EDIT_MODE, {productId: product.id, qty: product.qty, selectedOptions})
  },
  editModeSetFilters({commit}, {filterOptions}) {
    commit(types.CART_EDIT_MODE_SET_FILTERS, {filterOptions})
  },
  editModeSetQty({commit}, {qty}) {
    commit(types.CART_EDIT_QTY, {qty})
  },
  closeEditMode({commit}) {
    commit(types.CART_CLOSE_EDIT_MODE)
  },
  addressSuggestion(context, {query}) {
    return AddressService.addressSuggestion(query);
  },
  addressDetails(context, {id}) {
    return AddressService.addressDetails(id);
  },
  submitShippingDetails({rootGetters}, {}) {
    const shippingDetails = rootGetters['checkout/getShippingDetails']
    // we do not need city here. we get shipping method only base on postcode, city will add extra filter
    const address = {
      country_id: shippingDetails.contry_id || 'NZ',
      region: shippingDetails.state,
      region_id: shippingDetails.region_id ? shippingDetails.region_id : 0,
      street: [shippingDetails.streetAddress, shippingDetails.state],
      postcode: shippingDetails.zipCode,
      region_code: shippingDetails.region_code ? shippingDetails.region_code : ''
    }

    return CartService.getShippingMethods(address)
  }
}

export default actions
