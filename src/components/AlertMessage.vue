<template>
  <div :class="alertClass" class="alert">
    {{ message }}
    <button @click="$emit('close')" class="alert-close">×</button>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'AlertMessage',
  props: {
    message: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: 'success',
      validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    }
  },
  emits: ['close'],
  setup(props) {
    const alertClass = computed(() => `alert-${props.type}`)

    return {
      alertClass
    }
  }
}
</script>

<style scoped>
.alert {
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-weight: 500;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-success {
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
  border-left: 5px solid #28a745;
}

.alert-error {
  background: linear-gradient(135deg, #f8d7da, #f5c6cb);
  color: #721c24;
  border-left: 5px solid #dc3545;
}

.alert-warning {
  background: linear-gradient(135deg, #fff3cd, #ffeaa7);
  color: #856404;
  border-left: 5px solid #ffc107;
}

.alert-info {
  background: linear-gradient(135deg, #d1ecf1, #bee5eb);
  color: #0c5460;
  border-left: 5px solid #17a2b8;
}

.alert-close {
  background: none;
  border: none;
  font-size: 1.5em;
  font-weight: bold;
  cursor: pointer;
  color: inherit;
  opacity: 0.7;
  transition: opacity 0.3s ease;
  margin-left: 15px;
}

.alert-close:hover {
  opacity: 1;
}
</style>
