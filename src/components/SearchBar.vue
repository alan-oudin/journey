<template>
  <div class="search-bar">
    <div class="search-icon">🔍</div>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      type="text"
      :placeholder="placeholder"
      class="search-input"
    />
  </div>
</template>

<script>
export default {
  name: 'SearchBar',
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Rechercher...'
    }
  },
  emits: ['update:modelValue']
}
</script>

<style scoped>
.search-bar {
  position: relative;
  margin-bottom: 30px;
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 1.2em;
  z-index: 2;
}

.search-input {
  width: 100%;
  padding: 15px 15px 15px 50px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 1.1em;
  background: #f8f9fa;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #3498db;
  background: white;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}
</style>
