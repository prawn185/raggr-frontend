<template>
  <div class="notification" v-if="message">
    {{ message }}
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  props: {
    message: String,
  },
})
</script>

<style scoped>
.notification {
  padding: 10px;
  background-color: #4CAF50;
  color: white;
  margin-bottom: 15px;
  text-align: center;
  max-width: 7xl;
  margin-left: auto;
  margin-right: auto;
}
</style>