<template>
  <div>
    <div style="color: red;">{{ message }}</div>
    <div class="paypal-button" />
  </div>
</template>

<script>
import { PaypalButton } from './PaypalButton'

export default {
  mixins: [PaypalButton]
}
</script>
