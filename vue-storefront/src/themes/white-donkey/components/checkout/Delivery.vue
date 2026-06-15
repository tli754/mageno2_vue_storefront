<template>
  <div class="pt20">
    <div class="row pl20">
      <div class="col-xs-1 col-sm-2 col-md-1">
        <div
          class="number-circle lh35 cl-white brdr-circle align-center weight-700"
          :class="{ 'bg-cl-th-accent' : isActive || isFilled, 'bg-cl-tertiary' : !isFilled && !isActive }"
        >
          3
        </div>
      </div>
      <div class="col-xs-11 col-sm-9 col-md-11">
        <div class="row">
          <div class="col-xs-12 col-md-7" :class="{ 'cl-bg-tertiary' : !isFilled && !isActive }">
            <h3 class="m0 mb30">Shipping methods</h3>
          </div>
          <div class="col-xs-12 col-md-5 pr30">
            <div class="lh30 flex end-lg" v-if="isFilled && !isActive">
              <a href="#" class="cl-tertiary flex" @click.prevent="edit">
                <span class="pr5">
                  Edit shipping method
                </span>
                <i class="material-icons cl-tertiary">edit</i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20 pr20" v-show="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <p class="m0">Based on the items in your order and your postcode, we have these shipping options available. Select a shipping or pickup option to see frequently asked questions.</p>
        <div class="row mb25">
          <div class="col-xs-12">
            <h4 class="col-xs-12">
              Select your shipping method
            </h4>
            <div v-for="(method, index) in shippingMethods" :key="index" class="col-md-12">
              <label :class="{'radioStyled': true, 'checkout__total-price--updating': isLoading}">
                <b>{{ method.method_title }} | {{ method.amount | price(storeView) }}</b>
                <input
                  type="radio"
                  :value="method.method_code"
                  name="shipping-method"
                  v-model="selectedMethod"
                  :disabled="isLoading"
                  @change="$v.selectedMethod.$touch(); changeShippingMethod();"
                >
                <span class="checkmark" />
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row" v-show="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div class="row">
          <div class="col-xs-12 col-md-8 px20">
            <slot name="placeOrderButton">
              <button-full
                @click.native="submitShippingMethod"
                data-testid="deliverySubmit"
                class="place-order-btn"
                :disabled="$v.selectedMethod.$invalid || isLoading"
              >
                Continue to payment
              </button-full>
            </slot>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20" v-if="!isActive && isFilled">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div class="row fs16 mb35">
          <div class="col-md-12">
            <label class="radioStyled"> {{ selectedShippingMethod.method_title }} | {{ selectedShippingMethod.amount | price(storeView) }}
              <input type="radio" value="" checked disabled name="chosen-shipping-method">
              <span class="checkmark" />
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'
import { currentStoreView } from '@vue-storefront/core/lib/multistore'
import ButtonFull from 'theme/components/theme/ButtonFull'
import { mapGetters } from 'vuex'

export default {
  name: 'ShippingMethods',
  props: {
    isActive: {
      type: Boolean,
      required: true
    }
  },
  components: {
    ButtonFull,
  },
  data () {
    return {
      isFilled: false,
      isLoading: false,
      selectedMethod: {},
    }
  },
  computed: {
    storeView () {
      return currentStoreView()
    },
    selectedShippingMethod () {
      return (this.shippingMethods || []).find((method) => method.method_code === this.shippingDetails.shippingMethod)
    },
    currentShippingMethod() {
      return this.shippingMethods.find(item => item.method_code === this.selectedMethod)
    },
    ...mapGetters({
      shippingMethods: 'checkout/getShippingMethods',
      shippingDetails: 'checkout/getShippingDetails',
      personalDetails: 'checkout/getPersonalDetails',
    }),
  },
  validations: {
    selectedMethod: {
      required
    }
  },
  methods: {
    changeShippingMethod () {
      if (this.currentShippingMethod) {
        this.isLoading = true
        const addressInformation = {
          shippingAddress: {
            region: this.shippingDetails.state,
            region_id: this.shippingDetails.region_id ? this.shippingDetails.region_id : 0,
            region_code: this.shippingDetails.region_code ? this.shippingDetails.region_code : '',
            country_id: this.shippingDetails.country || 'NZ',
            street: [
              this.shippingDetails.streetAddress,
              this.shippingDetails.state
            ],
            postcode: this.shippingDetails.zipCode,
            city: this.shippingDetails.city,
            firstname: this.personalDetails.firstName,
            lastname: this.personalDetails.lastName,
            email: this.personalDetails.emailAddress,
            telephone: this.shippingDetails.phoneNumber,
          },
          shipping_method_code: this.currentShippingMethod.method_code,
          shipping_carrier_code: this.currentShippingMethod.carrier_code,
        }
        this.$store.dispatch('cart/overrideServerTotals', { hasShippingInformation: true, addressInformation })
          .then(()=>this.isLoading = false)
      }
    },
    submitShippingMethod() {
      this.loading = true
      // update shippingMethod property in checkout.shippingDetails.shippingMethod
      this.$store.dispatch('checkout/updatePropValue', ['shippingMethod', this.currentShippingMethod.method_code])
      this.$store.dispatch('cart/syncTotals', {forceServerSync: true, methodsData: {
        country: this.shippingDetails.country,
        method_code: this.currentShippingMethod.method_code,
        carrier_code: this.currentShippingMethod.carrier_code,
        payment_method: ''
      }})
      this.loading = false
      this.$emit('updateActiveSection', 'payment')
      this.isFilled = true
    },
    edit () {
      if (this.isFilled) {
        this.$emit('updateActiveSection', 'delivery')
      }
    },
  }
}
</script>
