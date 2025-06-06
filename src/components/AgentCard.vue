<template>
  <div class="agent-card">
    <div class="agent-info">
      <div class="info-item">
        <span class="info-label">Code Personnel</span>
        <span class="info-value highlight">{{ agent.codePersonnel }}</span>
      </div>
      <div class="info-item">
        <span class="info-label">Agent</span>
        <span class="info-value highlight">{{ agent.prenom }} {{ agent.nom }}</span>
      </div>
      <div class="info-item">
        <span class="info-label">Service</span>
        <span class="info-value">{{ agent.service }}</span>
      </div>
      <div class="info-item">
        <span class="info-label">Proches</span>
        <span class="info-value large">{{ agent.nombreProches }}</span>
      </div>
      <div class="info-item">
        <span class="info-label">Créneau</span>
        <span class="badge" :class="badgeClass">
          {{ creneauText }}
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Inscription</span>
        <span class="info-value">{{ agent.dateInscription }}</span>
      </div>
    </div>

    <div v-if="showActions" class="agent-actions">
      <button @click="$emit('supprimer', agent.codePersonnel)" class="btn btn-danger">
        🗑️ Supprimer
      </button>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'AgentCard',
  props: {
    agent: {
      type: Object,
      required: true
    },
    showActions: {
      type: Boolean,
      default: true
    }
  },
  emits: ['supprimer'],
  setup(props) {
    const badgeClass = computed(() => ({
      'badge-morning': props.agent.creneauPrefere === 'matin',
      'badge-afternoon': props.agent.creneauPrefere === 'apres-midi'
    }))

    const creneauText = computed(() =>
      props.agent.creneauPrefere === 'matin'
        ? '🌅 MATIN (9h-12h)'
        : '🌆 APRÈS-MIDI (14h-17h)'
    )

    return {
      badgeClass,
      creneauText
    }
  }
}
</script>

<style scoped>
.agent-card {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  padding: 25px;
  border-radius: 15px;
  margin-bottom: 20px;
  border-left: 5px solid #3498db;
  transition: all 0.3s ease;
}

.agent-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.agent-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 15px;
}

.info-item {
  display: flex;
  flex-direction: column;
}

.info-label {
  font-size: 0.9em;
  color: #6c757d;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 1.1em;
  font-weight: 600;
  color: #2c3e50;
  margin-top: 5px;
}

.info-value.highlight {
  font-size: 1.3em;
  color: #155724;
}

.info-value.large {
  font-size: 1.2em;
}

.badge {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 1em;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 5px;
  width: fit-content;
}

.badge-morning {
  background: linear-gradient(135deg, #f39c12, #e67e22);
  color: white;
}

.badge-afternoon {
  background: linear-gradient(135deg, #9b59b6, #8e44ad);
  color: white;
}

.agent-actions {
  text-align: right;
}

.btn {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 0.9em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);
}

@media (max-width: 768px) {
  .agent-info {
    grid-template-columns: 1fr;
  }
}
</style>
