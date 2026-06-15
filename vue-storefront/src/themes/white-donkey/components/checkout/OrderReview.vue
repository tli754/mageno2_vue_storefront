<template>
  <div class="order-review pt20">
    <div class="row pl20">
      <div class="col-xs-1 col-sm-2 col-md-1">
        <div
          class="number-circle lh35 cl-white brdr-circle align-center weight-700"
          :class="{ 'bg-cl-th-accent' : isActive || isFilled, 'bg-cl-tertiary' : !isFilled && !isActive }"
        >
          {{ (isVirtualCart ? 3 : 4) }}
        </div>
      </div>
      <div class="col-xs-11 col-sm-9 col-md-11">
        <div class="row">
          <div class="col-md-12" :class="{ 'cl-bg-tertiary' : !isFilled && !isActive }">
            <h3 class="m0">
              {{ $t('Review order') }}
            </h3>
          </div>
        </div>
      </div>
    </div>
    <div class="row pl20 pr20" v-show="isActive">
      <div class="hidden-xs col-sm-2 col-md-1" />
      <div class="col-xs-12 col-sm-9 col-md-11">
        <div id="checkout-order-review-additional-container">
          <div id="checkout-order-review-additional">
&nbsp;
          </div>
        </div>
        <div class="row mb15 mt20">
          <div class="col-xs-12">
            <p class="h4">
              {{ $t('Please check if all data are correct') }}
            </p>
            <div class="row">
              <div class="cartsummary-wrapper">
                <cart-summary />
              </div>
              <base-checkbox
                class="col-xs-11 col-sm-12 col-md-8 bg-cl-secondary p15 mb35 ml10"
                id="acceptTermsCheckbox"
                @blur="$v.orderReview.terms.$touch()"
                v-model="orderReview.terms"
                :validations="[{
                  condition: !$v.orderReview.terms.sameAs && $v.orderReview.terms.$error,
                  text: $t('Field is required')
                }]"
              >
                {{ $t('I agree to') }}
                <span
                  class="link pointer"
                  @click.prevent="$bus.$emit('modal-toggle', 'modal-terms')"
                >
                  {{ $t('Terms and conditions') }}
                </span>
              </base-checkbox>
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
                @click.native="placeOrder"
                data-testid="orderReviewSubmit"
                class="place-order-btn"
                :disabled="$v.orderReview.$invalid"
              >
                {{ $t('Place the order') }}
              </button-full>
            </slot>
          </div>
        </div>
      </div>
    </div>

    <modal name="modal-terms">
      <p slot="header">
        {{ $t('Terms and conditions') }}
      </p>
      <div slot="content">
        <p>
          This website ("website") is operated by Luma Inc., which includes Luma stores, and Luma Private Sales. This privacy policy only covers information collected at this website, and does not cover any information collected offline by Luma. All Luma websites are covered by this privacy policy.
        </p>
        <h2>
          Luma Security
        </h2>
        <p>
          Personal information provided on the website and online credit card transactions are transmitted through a secure server. We are committed to handling your personal information with high standards of information security. We take appropriate physical, electronic, and administrative steps to maintain the security and accuracy of personally identifiable information we collect, including limiting the number of people who have physical access to our database servers, as well as employing electronic security systems and password protections that guard against unauthorized access.
        </p>
        <h2>
          Luma Privacy Policy
        </h2>
        <p>
          To help us achieve our goal of providing the highest quality products and services, we use information from our interactions with you and other customers, as well as from other parties. Because we respect your privacy, we have implemented procedures to ensure that your personal information is handled in a safe, secure, and responsible manner. We have posted this privacy policy in order to explain our information collection practices and the choices you have about the way information is collected and used.
        </p>
        <p>
          As we continue to develop the Luma website and take advantage of advances in technology to improve the services we offer, this privacy policy likely will change. We therefore encourage you to refer to this policy on an ongoing basis so that you understand our current privacy policy.
        </p>
      </div>
    </modal>
  </div>
</template>

<script>
import { sameAs } from 'vuelidate/lib/validators'
import Composite from '@vue-storefront/core/mixins/composite'

import BaseCheckbox from 'theme/components/core/blocks/Form/BaseCheckbox'
import ButtonFull from 'theme/components/theme/ButtonFull'
import CartSummary from 'theme/components/core/blocks/Checkout/CartSummary'
import Modal from 'theme/components/core/Modal'
import { OrderModule } from '@vue-storefront/core/modules/order'
import { registerModule } from '@vue-storefront/core/lib/modules'
import { mapGetters } from 'vuex'
import i18n from '@vue-storefront/i18n'
import { Logger } from '@vue-storefront/core/lib/logger'

export default {
  name: 'OrderReview',
  props: {
    isActive: {
      type: Boolean,
      required: true
    }
  },
  components: {
    BaseCheckbox,
    ButtonFull,
    CartSummary,
    Modal
  },
  data () {
    return {
      isFilled: false,
      orderReview: {
        terms: false
      }
    }
  },
  computed: {
    ...mapGetters({
      isVirtualCart: 'cart/isVirtualCart',
      getShippingDetails: 'checkout/getShippingDetails',
      getPersonalDetails: 'checkout/getPersonalDetails'
    })
  },
  mixins: [Composite],
  validations: {
    orderReview: {
      terms: {
        sameAs: sameAs(() => true)
      }
    }
  },
  beforeCreate () {
    registerModule(OrderModule)
  },
  methods: {
    placeOrder () {
      if (this.getPersonalDetails.createAccount) {
        this.register()
      } else {
        this.$bus.$emit('checkout-before-placeOrder')
      }
    },
    async register () {
      this.$bus.$emit('notification-progress-start', i18n.t('Registering the account ...'))
      try {
        const result = await this.$store.dispatch('user/register', {
          email: this.getPersonalDetails.emailAddress,
          password: this.getPersonalDetails.password,
          firstname: this.getPersonalDetails.firstName,
          lastname: this.getPersonalDetails.lastName,
          addresses: [{
            firstname: this.getShippingDetails.firstName,
            lastname: this.getShippingDetails.lastName,
            street: [this.getShippingDetails.streetAddress, this.getShippingDetails.apartmentNumber],
            city: this.getShippingDetails.city,
            ...(this.getShippingDetails.state ? { region: { region: this.getShippingDetails.state } } : {}),
            country_id: this.getShippingDetails.country,
            postcode: this.getShippingDetails.zipCode,
            ...(this.getShippingDetails.phoneNumber ? { telephone: this.getShippingDetails.phoneNumber } : {}),
            default_shipping: true
          }]
        })

        if (result.code !== 200) {
          this.$bus.$emit('notification-progress-stop')
          this.onFailure(result)
          // If error includes a word 'password', emit event that eventually focuses on a corresponding field
          if (result.result.includes(i18n.t('password'))) {
            this.$bus.$emit('checkout-after-validationError', 'password')
          }
          // If error includes a word 'mail', emit event that eventually focuses on a corresponding field
          if (result.result.includes(i18n.t('email'))) {
            this.$bus.$emit('checkout-after-validationError', 'email-address')
          }
        } else {
          this.$bus.$emit('modal-hide', 'modal-signup')
          await this.$store.dispatch('user/login', {
            username: this.getPersonalDetails.emailAddress,
            password: this.getPersonalDetails.password
          })
          this.$bus.$emit('notification-progress-stop')
          this.$bus.$emit('checkout-before-placeOrder', result.result.id)
          this.onSuccess()
        }
      } catch (err) {
        this.$bus.$emit('notification-progress-stop')
        Logger.error(err, 'checkout')()
      }
    },
    onSuccess () {
    },
    onFailure (result) {
      this.$store.dispatch('notification/spawnNotification', {
        type: 'error',
        message: this.$t(result.result),
        action1: { label: this.$t('OK') }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
  .link {
    text-decoration: underline;
  }

  .cartsummary-wrapper {
    @media (min-width: 767px) {
      display: none;
    }
  }
</style>
