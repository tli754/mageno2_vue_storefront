<template>
  <div id="checkout">
    <div class="container">
      <div class="row" v-show="!isThankYouPage">
        <div class="col-sm-7 col-xs-12 pb70">
          <div class="checkout-title py5 px20">
            <h1>Checkout</h1>
          </div>
          <personal-details
            class="line relative"
            @updateActiveSection="setActivateSection"
            :is-active="activeSection.personalDetails"
          />
          <shipping
            class="line relative"
            @updateActiveSection="setActivateSection"
            :is-active="activeSection.shipping"
          />
          <Delivery
            class="line relative"
            :is-active="activeSection.delivery"
            @updateActiveSection="setActivateSection"
          />
          <payment
            class="line relative"
            :is-active="activeSection.payment"
          />
<!--          <order-review class="line relative" :is-active="activeSection.orderReview" />-->
          <div id="custom-steps" />
        </div>
        <div class="hidden-xs col-sm-5 bg-cl-secondary">
          <cart-summary />
        </div>
      </div>
    </div>
    <thank-you-page v-show="isThankYouPage" />
  </div>
</template>

<script>
import PersonalDetails from 'theme/components/checkout/PersonalDetails'
import Shipping from 'theme/components/checkout/Shipping'
import Payment from 'theme/components/checkout/Payment'
import Delivery from 'theme/components/checkout/Delivery'
import OrderReview from 'theme/components/checkout/OrderReview'
import CartSummary from 'theme/components/checkout/CartSummary'
import ThankYouPage from 'theme/components/checkout/ThankYouPage'
import { registerModule } from '@vue-storefront/core/lib/modules'
import { OrderModule } from '@vue-storefront/core/modules/order'
import config from 'config'
import VueOfflineMixin from 'vue-offline/mixin'
import { mapGetters } from 'vuex'
import { StorageManager } from '@vue-storefront/core/lib/storage-manager'
import Composite from '@vue-storefront/core/mixins/composite'
import { currentStoreView, localizedRoute } from '@vue-storefront/core/lib/multistore'
import { isServer } from '@vue-storefront/core/helpers'
import { Logger } from '@vue-storefront/core/lib/logger'

export default {
  name: 'Checkout',
  mixins: [Composite, VueOfflineMixin],
  components: {
    PersonalDetails,
    Shipping,
    Payment,
    Delivery,
    OrderReview,
    CartSummary,
    ThankYouPage
  },
  data () {
    return {
      stockCheckCompleted: false,
      stockCheckOK: false,
      confirmation: null, // order confirmation from server
      activeSection: {
        personalDetails: true,
        shipping: false,
        delivery: false,
        payment: false,
        orderReview: false
      },
      order: {},
      shipping: {},
      shippingMethod: {},
      payment: {},
      orderReview: {},
      cartSummary: {},
    }
  },
  computed: {
    ...mapGetters({
      isThankYouPage: 'checkout/isThankYouPage',
      shippingDetails: 'checkout/getShippingDetails',
      personalDetails: 'checkout/getPersonalDetails',
      paymentDetails: 'checkout/getPaymentDetails',
      shippingMethods: 'checkout/getShippingMethods',
    })
  },
  beforeCreate () {
    registerModule(OrderModule)
  },
  async beforeMount () {
    await this.$store.dispatch('checkout/load')
    this.$bus.$emit('checkout-after-load')
    this.$store.dispatch('checkout/setModifiedAt', Date.now())
    this.$bus.$on('checkout-after-cartSummary', this.onAfterCartSummary)
    this.$bus.$on('order-after-placed', this.onAfterPlaceOrder)
    this.$bus.$on('checkout-do-placeOrder', this.onDoPlaceOrder)

    if (!this.isThankYouPage) {
      this.$store.dispatch('cart/load', { forceClientState: true }).then(() => {
        if (this.$store.state.cart.cartItems.length === 0) {
          this.notifyEmptyCart()
          this.$router.push(this.localizedRoute('/'))
        } else {
          this.stockCheckCompleted = false
          const checkPromises = []
          for (let product of this.$store.state.cart.cartItems) { // check the results of online stock check
            if (product.onlineStockCheckid) {
              checkPromises.push(new Promise((resolve, reject) => {
                StorageManager.get('syncTasks').getItem(product.onlineStockCheckid, (err, item) => {
                  if (err || !item) {
                    if (err) Logger.error(err)()
                    resolve(null)
                  } else {
                    product.stock = item.result
                    resolve(product)
                  }
                })
              }))
            }
          }
          Promise.all(checkPromises).then((checkedProducts) => {
            this.stockCheckCompleted = true
            this.stockCheckOK = true
            for (let chp of checkedProducts) {
              if (chp && chp.stock) {
                if (!chp.stock.is_in_stock) {
                  this.stockCheckOK = false
                  chp.errors.stock = 'Out of stock!'
                  this.notifyOutStock(chp)
                }
              }
            }
          })
        }
      })
    }
    // const storeView = currentStoreView()
    // let country = this.$store.state.checkout.shippingDetails.country
    // if (!country) country = storeView.i18n.defaultCountry
    // this.$bus.$emit('checkout-before-shippingMethods', country)
    // this.$bus.$emit('checkout-before-shippingMethods')
  },
  mounted () {
    this.$store.commit('ui/setMicrocart', false)
    this.$store.commit('ui/setSidebar', false)

    if (!isServer && window.paypalScriptLoaded === undefined) {
      const storeView = currentStoreView()
      const { currencyCode } = storeView.i18n
      const clientId = config.paypal.hasOwnProperty('clientId') ? config.paypal.clientId : ''
      const sdkUrl = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=${currencyCode}&disable-funding=card,credit,mybank,sofort`
      var script = document.createElement('script')
      script.setAttribute('src', sdkUrl)
      document.head.appendChild(script)
      window.paypalScriptLoaded = true
    }
  },
  beforeDestroy () {
    this.$store.dispatch('checkout/setModifiedAt', 0) // exit checkout
    this.$bus.$off('checkout-after-cartSummary', this.onAfterCartSummary)
    this.$bus.$off('order-after-placed', this.onAfterPlaceOrder)
    this.$bus.$on('checkout-do-placeOrder', this.onDoPlaceOrder)
  },
  watch: {
    '$route': 'activateHashSection',
    'OnlineOnly': 'onNetworkStatusCheck'
  },
  methods: {
    async onAfterPlaceOrder (payload) {
      this.confirmation = payload.confirmation
      this.$store.dispatch('checkout/setThankYouPage', true)
      Logger.debug(payload.order)()
    },
    setActivateSection (section) {
      this.activateSection(section)
    },
    onAfterCartSummary (receivedData) {
      this.cartSummary = receivedData
    },
    onDoPlaceOrder (additionalPayload) {
      if (this.$store.state.cart.cartItems.length === 0) {
        this.notifyEmptyCart()
        this.$router.push(this.localizedRoute('/'))
      } else {
        this.payment.paymentMethodAdditional = additionalPayload
        this.placeOrder()
      }
    },
    placeOrder () {
      if (this.$store.state.cart.cartItems.length === 0) {
        this.notifyEmptyCart()
        this.$router.push(this.localizedRoute('/'))
      } else {
        this.checkConnection({ online: typeof navigator !== 'undefined' ? navigator.onLine : true })
        if (this.checkStocks()) {
          this.$store.dispatch('checkout/placeOrder', { order: this.prepareOrder() })
        } else {
          this.notifyNotAvailable()
        }
      }
    },
    onNetworkStatusCheck (isOnline) {
      this.checkConnection(isOnline)
    },
    checkStocks () {
      let isValid = true
      for (let child of this.$children) {
        if (child.hasOwnProperty('$v') && child.$v.$invalid) {
          isValid = false
          break
        }
      }
      if (typeof navigator !== 'undefined' && navigator.onLine) {
        if (this.stockCheckCompleted) {
          if (!this.stockCheckOK) {
            isValid = false
            this.notifyNotAvailable()
          }
        } else {
          this.notifyStockCheck()
          isValid = false
        }
      }
      return isValid
    },
    activateHashSection () {
      if (!isServer) {
        var urlStep = window.location.hash.replace('#', '')
        if (this.activeSection.hasOwnProperty(urlStep) && this.activeSection[urlStep] === false) {
          this.activateSection(urlStep)
        } else if (urlStep === '') {
          this.activateSection('personalDetails')
        }
      }
    },
    checkConnection (isOnline) {
      if (!isOnline) {
        this.notifyNoConnection()
      }
    },
    activateSection (sectionToActivate) {
      for (let section in this.activeSection) {
        this.activeSection[section] = false
      }
      this.activeSection[sectionToActivate] = true
      if (!isServer) window.location.href = window.location.origin + window.location.pathname + '#' + sectionToActivate
    },
    // This method checks if there exists a mapping of chosen payment method to one of Magento's payment methods.
    getPaymentMethod () {
      let paymentMethod = this.payment.paymentMethod
      if (config.orders.payment_methods_mapping.hasOwnProperty(paymentMethod)) {
        paymentMethod = config.orders.payment_methods_mapping[paymentMethod]
      }
      return paymentMethod
    },
    prepareOrder () {
      const shippingMethod = this.shippingMethods.find((m)=>m.method_code===this.shippingDetails.shippingMethod);
      return {
        user_id: '',
        cart_id: this.$store.state.cart.cartServerToken ? this.$store.state.cart.cartServerToken.toString() : '',
        products: this.$store.state.cart.cartItems,
        addressInformation: {
          billingAddress: {
            region: this.paymentDetails.state,
            region_id: this.paymentDetails.region_id ? this.paymentDetails.region_id : 0,
            country_id: this.paymentDetails.country || 'NZ',
            street: [this.paymentDetails.streetAddress, this.paymentDetails.state],
            company: this.paymentDetails.company,
            telephone: this.paymentDetails.phoneNumber,
            postcode: this.paymentDetails.zipCode,
            city: this.paymentDetails.city,
            firstname: this.personalDetails.firstName,
            lastname: this.personalDetails.lastName,
            email: this.personalDetails.emailAddress,
            region_code: this.paymentDetails.region_code ? this.paymentDetails.region_code : '',
            vat_id: this.paymentDetails.taxId || ''
          },
          shippingAddress: {
            region: this.shippingDetails.state,
            region_id: this.shippingDetails.region_id ? this.shippingDetails.region_id : 0,
            country_id: this.shippingDetails.country || 'NZ',
            street: [this.shippingDetails.streetAddress, this.shippingDetails.state],
            company: '',
            telephone: this.shippingDetails.phoneNumber,
            postcode: this.shippingDetails.zipCode,
            city: this.shippingDetails.city,
            firstname: this.personalDetails.firstName,
            lastname: this.personalDetails.lastName,
            email: this.personalDetails.emailAddress,
            region_code: this.shippingDetails.region_code ? this.shippingDetails.region_code : ''
          },
          shipping_method_code: shippingMethod ? shippingMethod.method_code : this.shippingDetails.shippingMethod,
          shipping_carrier_code: shippingMethod.carrier_code || '',
          payment_method_code: this.paymentDetails.paymentMethod,
          payment_method_additional: this.paymentDetails.paymentMethodAdditional,
        }
      }
    },
    notifyEmptyCart () {
      this.$store.dispatch('notification/spawnNotification', {
        type: 'warning',
        message: 'Shopping cart is empty. Please add some products before entering Checkout',
        action1: { label: 'OK' }
      })
    },
    notifyOutStock (chp) {
      this.$store.dispatch('notification/spawnNotification', {
        type: 'error',
        message: chp.name + ' is out of stock!',
        action1: { label: 'OK' }
      })
    },
    notifyNotAvailable () {
      this.$store.dispatch('notification/spawnNotification', {
        type: 'error',
        message: 'Some of the ordered products are not available!',
        action1: { label: 'OK' }
      })
    },
    notifyStockCheck () {
      this.$store.dispatch('notification/spawnNotification', {
        type: 'warning',
        message: 'Stock check in progress, please wait while available stock quantities are checked',
        action1: { label: 'OK' }
      })
    },
    notifyNoConnection () {
      this.$store.dispatch('notification/spawnNotification', {
        type: 'warning',
        message: 'There is no Internet connection. You can still place your order. We will notify you if any of ordered products is not available because we cannot check it right now.',
        action1: { label: 'OK' }
      })
    }
  },
  metaInfo () {
    return {
      title: this.$route.meta.title || 'Checkout',
      meta: this.$route.meta.description ? [{ vmid: 'description', name: 'description', content: this.$route.meta.description }] : []
    }
  },
  asyncData ({ store, route, context }) { // this is for SSR purposes to prefetch data
    return new Promise((resolve, reject) => {
      if (context) context.output.cacheTags.add(`checkout`)
      if (context) context.server.response.redirect(localizedRoute('/'))
      resolve()
    })
  }
}
</script>

<style lang="scss">
  @import '~theme/css/base/text';
  @import '~theme/css/variables/colors';
  @import '~theme/css/helpers/functions/color';
  $bg-secondary: color(secondary, $colors-background);
  $color-tertiary: color(tertiary);
  $color-secondary: color(secondary);
  $color-error: color(error);
  $color-white: color(white);
  $color-black: color(black);

  #checkout {
    .number-circle {
      width: 35px;
      height: 35px;

      @media (max-width: 768px) {
        width: 25px;
        height: 25px;
        line-height: 25px;
      }
    }
    .radioStyled {
      display: block;
      position: relative;
      padding-left: 35px;
      margin-bottom: 12px;
      cursor: pointer;
      font-size: 16px;
      line-height: 30px;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;

      input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
      }

      .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 25px;
        width: 25px;
        border-radius: 50%;
        border: 2px solid $color-secondary;

        &:after {
          content: "";
          position: absolute;
          display: none;
          top: 3px;
          left: 3px;
          width: 19px;
          height: 19px;
          border-radius: 50%;
          background: $color-secondary;
        }
      }

      input:checked ~ .checkmark:after {
        display: block;
      }
    }
  }

  .line {
    &:after {
      content: '';
      display: block;
      position: absolute;
      top: 0;
      left: 37px;
      z-index: -1;
      width: 1px;
      height: 100%;
      background-color: $bg-secondary;

      @media (max-width: 768px) {
        display: none;
      }
    }
  }

  .checkout-title {
    @media (max-width: 767px) {
      background-color: $bg-secondary;
      margin-bottom: 25px;

      h1 {
        font-size: 36px;
      }
    }
  }

  .checkout__total-price--updating {
    text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
  }
</style>
