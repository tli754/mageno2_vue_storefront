<template>
  <div class="payment pt20">
    <div class="row pl20">
      <div class="col-xs-1 col-sm-2 col-md-1">
        <div
          class="number-circle lh35 cl-white brdr-circle align-center weight-700"
          :class="{ 'bg-cl-th-accent' : isActive || isFilled, 'bg-cl-tertiary' : !isFilled && !isActive }"
        >4</div>
      </div>
      <div class="col-xs-11 col-sm-9 col-md-11">
        <div class="row mb15">
          <div class="col-xs-12 col-md-7" :class="{ 'cl-bg-tertiary' : !isActive }">
            <h3 class="m0 mb30">
              Payment
            </h3>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20" v-if="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-11 col-sm-9 col-md-10">
        <div class="row" v-if="isActive">
          <base-checkbox
            class="col-xs-12 mb35"
            id="sameAsShippingCheckbox"
            v-model="sameAsShipping"
          >
            My billing address is the same as my shipping address
          </base-checkbox>
          <template v-if="!sameAsShipping">
            <br>
            <base-input
              class="col-xs-12 mb35"
              type="text"
              name="phone-number"
              placeholder="Phone"
              helper="Phone number may be needed by carrier"
              v-model.trim="payment.phoneNumber"
              :validations="[
              {
                condition: $v.payment.phoneNumber.$error && !$v.payment.phoneNumber.required,
                text: 'Field is required'
              }
            ]"
              autocomplete="tel"
              @blur="$v.payment.phoneNumber.$touch()"
              required
            />
            <address-suggestion
              @selectedAddress="updateSelectedAddress"
              @isValidAddress="addressValidation=$event"
              :street="payment.streetAddress"
              @change="payment.streetAddress=$event"
            />
            <base-input
              class="col-xs-12 col-sm-6 mb25"
              type="text"
              name="state"
              placeholder="Suburb (optional)"
              v-model.trim="payment.state"
              autocomplete="address-level1"
            />
            <base-input
              class="col-xs-12 col-sm-6 mb25"
              type="text"
              name="city"
              placeholder="City"
              v-model.trim="payment.city"
              @blur="$v.payment.city.$touch()"
              autocomplete="address-level2"
              :validations="[
              {
                condition: $v.payment.city.$error && !$v.payment.city.required,
                text: 'Field is required'
              },
              {
                condition: $v.payment.city.$error && $v.payment.city.required,
                text: 'Please provide valid city name'
              }
            ]"
              required
            />
            <base-input
              class="col-xs-12 col-sm-6"
              type="text"
              name="zip-code"
              placeholder="Postcode"
              v-model.trim="payment.zipCode"
              @blur="$v.payment.zipCode.$touch()"
              autocomplete="postal-code"
              :validations="[
              {
                condition: $v.payment.zipCode.$error && !$v.payment.zipCode.required,
                text: 'Field is required'
              },
              {
                condition: !$v.payment.zipCode.minLength,
                text: 'Postcode must have at least 3 numbers.'
              }
            ]"
              required
            />
            <base-checkbox
              class="col-xs-12 mb25"
              id="addCompanyCheckbox"
              v-model="addCompany"
            >
              Add my company name
            </base-checkbox>
            <template v-if="addCompany">
              <base-input
                class="col-xs-12 mb10"
                type="text"
                name="company-name"
                placeholder="Company name (optional)"
                v-model.trim="payment.company"
                autocomplete="organization"
              />
            </template>
          </template>
          <div class="col-xs-12">
            <h4>Payment method</h4>
          </div>
          <div v-for="(method, index) in paymentMethods" :key="index" class="col-md-12">
            <label class="radioStyled"> {{ method.title ? method.title : method.name }}
              <input
                type="radio"
                :value="method.code"
                name="payment-method"
                v-model="payment.paymentMethod"
                @change="$v.payment.paymentMethod.$touch(); changePaymentMethod();"
              >
              <span class="checkmark" />
            </label>
          </div>
          <span class="validation-error" v-if="!$v.payment.paymentMethod.required">Field is required</span>
        </div>
      </div>
    </div>
    <div class="row" v-if="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div class="row">
          <div class="col-xs-12 col-md-8 px20 my30">
            <paypal-button v-if="payment.paymentMethod === 'paypal_express'"/>
            <button-full
              v-else
              @click.native="sendDataToCheckout"
              data-testid="paymentSubmit"
              :disabled="$v.payment.$invalid"
            >
              Place order
            </button-full>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20" v-if="!isActive && isFilled">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div class="row fs16 mb35">
          <div class="col-xs-12">
            <span>{{ payment.streetAddress }}, {{ payment.city }}</span>
            <span v-if="payment.state">{{ payment.state }}, </span>
            <span>{{ payment.zipCode }}</span>
            <p>
              <span>{{ payment.phoneNumber }}</span>
            </p>
            <p v-if="addCompany">
              {{ payment.company }}
            </p>
            <div class="col-xs-12">
              <h4>Payment method</h4>
            </div>
            <div class="col-md-12 mb15">
              <label class="radioStyled"> {{ getPaymentMethod().title }}
                <input type="radio" value="" checked disabled name="chosen-payment-method">
                <span class="checkmark" />
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { required, minLength } from 'vuelidate/lib/validators'
import { unicodeAlpha, unicodeAlphaNum } from '@vue-storefront/core/helpers/validators'
import PaypalButton from 'src/modules/paypal/components/Button'
import BaseCheckbox from 'theme/components/core/blocks/Form/BaseCheckbox'
import BaseInput from 'theme/components/core/blocks/Form/BaseInput'
import BaseSelect from 'theme/components/core/blocks/Form/BaseSelect'
import ButtonFull from 'theme/components/theme/ButtonFull'
import { mapState, mapGetters } from 'vuex'
import RootState from '@vue-storefront/core/types/RootState'
import toString from 'lodash-es/toString'
import debounce from 'lodash-es/debounce'
import AddressSuggestion from "theme/components/checkout/AddressSuggestion"

export default {
  name: 'Payment',
  props: {
    isActive: {
      type: Boolean,
      required: true
    }
  },
  components: {
    BaseCheckbox,
    BaseInput,
    BaseSelect,
    AddressSuggestion,
    PaypalButton,
    ButtonFull
  },
  data () {
    return {
      isFilled: false,
      payment: this.$store.getters['checkout/getPaymentDetails'],
      addCompany: false,
      sameAsShipping: true,
      addressValidation: false,
      sendToBillingAddress: false
    }
  },
  computed: {
    ...mapState({
      currentUser: (state) => state.user.current,
      shippingDetails: (state) => state.checkout.shippingDetails
    }),
    ...mapGetters({
      paymentMethods: 'checkout/getPaymentMethods',
      paymentDetails: 'checkout/getPaymentDetails',
    })
  },
  beforeMount () {
    this.payment = this.$store.getters['checkout/getPaymentDetails']
  },
  mounted () {
    if (this.payment.firstName) {
      this.initializeBillingAddress()
    } else {
      if (this.payment.company) {
        this.addCompany = true
      }
    }
    this.changePaymentMethod()
  },

  watch: {
    shippingDetails: {
      handler () {
        if (this.sameAsShipping) {
          this.copyShippingToBillingAddress()
        }
      },
      deep: true
    },
    sameAsShipping: {
      handler () {
        this.useShippingAddress()
      }
    },
    addCompany: {
      handler () {
        this.initCompanyName()
      }
    },
    paymentMethods: {
      handler: debounce(function () {
        this.changePaymentMethod()
      }, 500)
    }
  },
  methods: {
    sendDataToCheckout () {
      if (this.payment.paymentMethod && this.paymentMethods.find(item => (item.code === this.payment.paymentMethod
        && item.is_server_method === true))) {
        this.$store.dispatch('checkout/savePaymentDetails', this.payment).then(
          ()=>this.$bus.$emit('checkout-do-placeOrder')
        )
      }
    },
    updateSelectedAddress(address) {
      this.payment.country_id = address.country_id || 'NZ';
      this.payment.city = address.city || '';
      this.payment.state = address.suburb || '';
      this.payment.zipCode = address.postcode || '';
      this.payment.streetAddress = address.street || '';
    },
    initializeBillingAddress () {
      this.payment = this.paymentDetails || {
        firstName: '',
        lastName: '',
        company: '',
        country: '',
        state: '',
        city: '',
        streetAddress: '',
        apartmentNumber: '',
        postcode: '',
        zipCode: '',
        phoneNumber: '',
        taxId: '',
        paymentMethod: this.paymentMethods.length > 0 ? this.paymentMethods[0].code : ''
      }
    },
    useShippingAddress () {
      if (this.sameAsShipping) {
        this.copyShippingToBillingAddress()
      } else {
        this.payment = this.paymentDetails
      }
    },
    copyShippingToBillingAddress () {
      this.payment = {
        firstName: this.shippingDetails.firstName,
        lastName: this.shippingDetails.lastName,
        country: this.shippingDetails.country,
        state: this.shippingDetails.state,
        city: this.shippingDetails.city,
        streetAddress: this.shippingDetails.streetAddress,
        apartmentNumber: this.shippingDetails.apartmentNumber,
        zipCode: this.shippingDetails.zipCode,
        phoneNumber: this.shippingDetails.phoneNumber,
        paymentMethod: this.paymentMethods.length > 0 ? this.paymentMethods[0].code : ''
      }
    },
    initCompanyName () {
      if (!this.addCompany) {
        this.payment.company = ''
      }
    },
    getPaymentMethod () {
      for (let i = 0; i < this.paymentMethods.length; i++) {
        if (this.paymentMethods[i].code === this.payment.paymentMethod) {
          return {
            title: this.paymentMethods[i].title ? this.paymentMethods[i].title : this.paymentMethods[i].name
          }
        }
      }
      return {
        name: ''
      }
    },
    notInMethods (method) {
      let availableMethods = this.paymentMethods
      if (availableMethods.find(item => item.code === method)) {
        return false
      }
      return true
    },
    changePaymentMethod () {
      // reset the additional payment method component container if exists.
      if (document.getElementById('checkout-order-review-additional-container')) {
        document.getElementById('checkout-order-review-additional-container').innerHTML = '<div id="checkout-order-review-additional">&nbsp;</div>' // reset
      }

      // Let anyone listening know that we've changed payment method, usually a payment extension.
      if (this.payment.paymentMethod) {
        this.$bus.$emit('checkout-payment-method-changed', this.payment.paymentMethod)
      }
    },
    onCheckoutLoad () {
      this.payment = this.$store.getters['checkout/getPaymentDetails']
    }
  },
  validations () {
    return {
      payment: {
        phoneNumber: {
          required
        },
        streetAddress: {
          required,
          unicodeAlphaNum
        },
        zipCode: {
          required,
          minLength: minLength(3),
          unicodeAlphaNum
        },
        city: {
          required,
          unicodeAlpha
        },
        paymentMethod: {
          required
        }
      }
    }
  }
}
</script>
