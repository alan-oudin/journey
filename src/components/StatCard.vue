<template>
  <div class="stat-card">
    <div class="stat-number">{{ number }}</div>
    <div class="stat-label">{{ label }}</div>
  </div>
</template>

<script>
export default {
  name: 'StatCard',
  props: {
    number: {
      type: Number,
      required: true
    },
    label: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.stat-card {
  background: linear-gradient(135deg, #ffffff, #f8f9fa);
  padding: 25px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 10px 20px rgba(0,0,0,0.05);
  border-top: 4px solid #3498db;
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-number {
  font-size: 2.5em;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 10px;
}

.stat-label {
  font-size: 1em;
  color: #6c757d;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 1px;
}
</style>
