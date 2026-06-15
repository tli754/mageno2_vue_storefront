<template>
  <div class="pt20">
    <div class="row pl20">
      <div class="col-xs-1 col-sm-2 col-md-1">
        <div
          class="number-circle lh35 cl-white brdr-circle align-center weight-700"
          :class="{ 'bg-cl-th-accent' : isActive || isFilled, 'bg-cl-tertiary' : !isFilled && !isActive }"
        >
          2
        </div>
      </div>
      <div class="col-xs-11 col-sm-9 col-md-11">
        <div class="row mb15">
          <div class="col-xs-12 col-md-7" :class="{ 'cl-bg-tertiary' : !isFilled && !isActive }">
            <h3 class="m0 mb30">Shipping Address</h3>
          </div>
          <div class="col-xs-12 col-md-5 pr30">
            <div class="lh30 flex end-lg" v-if="isFilled && !isActive">
              <a href="#" class="cl-tertiary flex" @click.prevent="edit">
                <span class="pr5">
                  Edit shipping address
                </span>
                <i class="material-icons cl-tertiary">edit</i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20" v-if="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-11 col-sm-9 col-md-10">
        <div class="row">
          <base-input
            class="col-xs-12 mb35"
            type="text"
            name="phone-number"
            placeholder="Phone"
            helper="Phone number may be needed by carrier"
            v-model.trim="shipping.phoneNumber"
            :validations="[
              {
                condition: $v.shipping.phoneNumber.$error && !$v.shipping.phoneNumber.required,
                text: 'Field is required'
              }
            ]"
            autocomplete="tel"
            @blur="$v.shipping.phoneNumber.$touch()"
            required
          />
          <address-suggestion
            @selectedAddress="updateSelectedAddress"
            @isValidAddress="addressValidation=$event"
            :street="shipping.streetAddress"
            @change="shipping.streetAddress=$event"
          />
          <base-input
            class="col-xs-12 col-sm-6 mb25"
            type="text"
            name="state"
            placeholder="Suburb (optional)"
            v-model.trim="shipping.state"
            autocomplete="address-level1"
          />
          <base-input
            class="col-xs-12 col-sm-6 mb25"
            type="text"
            name="city"
            placeholder="City"
            v-model.trim="shipping.city"
            @blur="$v.shipping.city.$touch()"
            autocomplete="address-level2"
            :validations="[
              {
                condition: $v.shipping.city.$error && !$v.shipping.city.required,
                text: 'Field is required'
              },
              {
                condition: $v.shipping.city.$error && $v.shipping.city.required,
                text: 'Please provide valid city name'
              }
            ]"
            required
          />
          <base-input
            class="col-xs-12 col-sm-6 mb25"
            type="text"
            name="zip-code"
            placeholder="Postcode"
            v-model.trim="shipping.zipCode"
            @blur="$v.shipping.zipCode.$touch()"
            autocomplete="postal-code"
            :validations="[
              {
                condition: $v.shipping.zipCode.$error && !$v.shipping.zipCode.required,
                text: 'Field is required'
              },
              {
                condition: !$v.shipping.zipCode.minLength,
                text: 'Postcode must have at least 3 numbers.'
              }
            ]"
            required
          />
        </div>
      </div>
    </div>
    <div class="row" v-if="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div class="row">
          <div class="col-xs-12 col-md-8 my30 px20">
            <button-full
              data-testid="shippingSubmit"
              @click.native="submitShippingDetails"
              :disabled="!isValidAddress"
            >
              Continue to shipping
            </button-full>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20" v-if="!isActive && isFilled">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div class="row">
          <div class="col-xs-12">
            <span>{{ shipping.streetAddress }}, {{ shipping.city }}</span>
            <span v-if="shipping.state">{{ shipping.state }}, </span>
            <span>{{ shipping.zipCode }}</span>
            <p class="mb0">
              <span>{{ shipping.phoneNumber }}</span>
              <span class="h5 cl-bg-tertiary">(Phone number may be needed by carrier)</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { required, minLength } from 'vuelidate/lib/validators'
import { unicodeAlpha, unicodeAlphaNum } from 'theme/helpers'
import AddressSuggestion from "theme/components/checkout/AddressSuggestion"
import BaseInput from 'theme/components/form/BaseInput'
import BaseSelect from 'theme/components/form/BaseSelect'
import ButtonFull from 'theme/components/form/ButtonFull'
import { mapGetters } from 'vuex'

export default {
  name: 'Shipping',
  props: {
    isActive: {
      type: Boolean,
      required: true
    }
  },
  data () {
    return {
      isFilled: false,
      addressValidation: false,
      shipping: this.$store.state.checkout.shippingDetails,
    }
  },
  components: {
    ButtonFull,
    AddressSuggestion,
    BaseInput,
    BaseSelect
  },
  computed: {
    ...mapGetters({
      shippingDetails: 'checkout/getShippingDetails',
    }),
    isValidAddress() {
      return !this.$v.shipping.$invalid && !this.addressValidation
    }
  },
  beforeMount() {
    this.shipping = this.shippingDetails
  },
  methods: {
    updateSelectedAddress(address) {
      this.shipping.country_id = address.country_id || 'NZ';
      this.shipping.city = address.city || '';
      this.shipping.state = address.suburb || '';
      this.shipping.zipCode = address.postcode || '';
      this.shipping.streetAddress = address.street || '';
    },
    submitShippingDetails () {
      this.$store.dispatch('checkout/saveShippingDetails', Object.assign({}, this.shippingDetails, this.shipping))
      this.$store.dispatch('themeCart/submitShippingDetails', {}).then(({ result }) => {
        if (result.length > 0) {
          const newShippingMethods = result
            .map(method => ({ ...method, is_server_method: true }))
            .filter(method => !method.hasOwnProperty('available') || method.available)
          this.$store.dispatch('checkout/replaceShippingMethods', newShippingMethods)
          this.$emit('updateActiveSection', 'delivery')
          this.isFilled = true
        }
      })
    },
    edit () {
      if (this.isFilled) {
        this.$emit('updateActiveSection', 'shipping')
      }
    },
  },
  validations: {
    shipping: {
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
      }
    }
  }
}
</script>
