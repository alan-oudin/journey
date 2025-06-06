<template>
  <div class="recherche-view">
    <h2>Recherche rapide - Jour J</h2>

    <div class="search-container">
      <div class="search-bar">
        <div class="search-icon">🔍</div>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Tapez le code personnel (CP) de l'agent..."
          class="search-input"
          @input="rechercherAgent"
          autofocus
        />
        <button v-if="searchTerm" @click="clearSearch" class="clear-button">×</button>
      </div>

      <div class="search-info">
        <p>💡 Saisissez le code personnel pour identifier rapidement un agent</p>
      </div>
    </div>

    <div class="resultats">
      <!-- État initial -->
      <div v-if="!searchTerm" class="no-results">
        <div class="no-results-icon">👤</div>
        <h3>Saisissez un code personnel pour rechercher un agent</h3>
        <p>La recherche s'effectue automatiquement lors de la saisie</p>
        <div class="examples">
          <p><strong>Exemples de codes :</strong></p>
          <span class="example-code">1234</span>
          <span class="example-code">5678</span>
          <span class="example-code">9012</span>
        </div>
      </div>

      <!-- Agent trouvé -->
      <div v-else-if="agentTrouve" class="agent-found">
        <div class="found-header">
          <h3>✅ Agent trouvé!</h3>
          <div class="found-badge">Inscrit</div>
        </div>

        <div class="agent-card-large">
          <div class="agent-header">
            <div class="agent-name">{{ agentTrouve.prenom }} {{ agentTrouve.nom }}</div>
            <div class="agent-code">CP: {{ agentTrouve.code_personnel }}</div>
          </div>

          <div class="agent-details">
            <div class="detail-row">
              <div class="detail-item">
                <span class="detail-icon">🏢</span>
                <div>
                  <span class="detail-label">Service</span>
                  <span class="detail-value">{{ agentTrouve.service }}</span>
                </div>
              </div>

              <div class="detail-item">
                <span class="detail-icon">👥</span>
                <div>
                  <span class="detail-label">Nombre de proches</span>
                  <span class="detail-value large">{{ agentTrouve.nombre_proches }}</span>
                </div>
              </div>
            </div>

            <div class="detail-row">
              <div class="detail-item">
                <span class="detail-icon">👨‍👩‍👧‍👦</span>
                <div>
                  <span class="detail-label">Total personnes</span>
                  <span class="detail-value large highlight">{{ parseInt(agentTrouve.nombre_proches) + 1 }}</span>
                </div>
              </div>

              <div class="detail-item">
                <span class="detail-icon">⏰</span>
                <div>
                  <span class="detail-label">Heure d'arrivée</span>
                  <span class="badge" :class="getBadgeClass(agentTrouve.heure_arrivee)">
                    {{ agentTrouve.heure_arrivee }}
                  </span>
                </div>
              </div>
            </div>

            <div class="detail-row">
              <div class="detail-item full-width">
                <span class="detail-icon">🕐</span>
                <div>
                  <span class="detail-label">Période</span>
                  <span class="periode-badge" :class="getPeriodeClass(agentTrouve.heure_arrivee)">
                    {{ getPeriodeLabel(agentTrouve.heure_arrivee) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="detail-row">
              <div class="detail-item full-width">
                <span class="detail-icon">📅</span>
                <div>
                  <span class="detail-label">Date d'inscription</span>
                  <span class="detail-value">{{ formatDate(agentTrouve.date_inscription) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="actions-section">
            <button @click="printAgentInfo" class="btn btn-secondary">
              🖨️ Imprimer
            </button>
            <router-link to="/gestion" class="btn btn-primary">
              👥 Voir tous les agents
            </router-link>
          </div>
        </div>
      </div>

      <!-- Recherche en cours -->
      <div v-else-if="loading" class="loading-search">
        <div class="spinner"></div>
        <p>Recherche en cours...</p>
      </div>

      <!-- Aucun résultat -->
      <div v-else class="no-results">
        <div class="no-results-icon error">❌</div>
        <h3>Aucun agent trouvé</h3>
        <p>Aucun agent avec le code personnel "<strong>{{ searchTerm }}</strong>" n'est inscrit</p>
        <div class="help-section">
          <p class="help-text">
            💡 Vérifiez le code personnel ou utilisez l'onglet "Inscription" pour enregistrer l'agent
          </p>
          <router-link to="/inscription" class="btn btn-primary">
            ➕ Inscrire cet agent
          </router-link>
        </div>
      </div>
    </div>

    <!-- Statistiques rapides -->
    <div v-if="!searchTerm" class="quick-stats">
      <h4>📊 Statistiques du jour</h4>
      <div class="stats-row">
        <div class="stat-item">
          <span class="stat-number">{{ totalInscriptions }}</span>
          <span class="stat-label">Agents inscrits</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">{{ groupeMatin }}</span>
          <span class="stat-label">Groupe matin</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">{{ groupeApresMidi }}</span>
          <span class="stat-label">Groupe après-midi</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">{{ totalProches }}</span>
          <span class="stat-label">Total proches</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAgentsStore } from '@/stores/agents'
import { storeToRefs } from 'pinia'

export default {
  name: 'RechercheView',
  setup() {
    const agentsStore = useAgentsStore()
    const { totalInscriptions, groupeMatin, groupeApresMidi, totalProches } = storeToRefs(agentsStore)

    const searchTerm = ref('')
    const agentTrouve = ref(null)
    const loading = ref(false)

    // Fonctions utilitaires
    function getBadgeClass(heure) {
      const periode = (heure >= '09:00' && heure <= '11:40') ? 'matin' : 'apres-midi'
      return {
        'badge-morning': periode === 'matin',
        'badge-afternoon': periode === 'apres-midi'
      }
    }

    function getPeriodeClass(heure) {
      const periode = (heure >= '09:00' && heure <= '11:40') ? 'matin' : 'apres-midi'
      return {
        'periode-matin': periode === 'matin',
        'periode-apres-midi': periode === 'apres-midi'
      }
    }

    function getPeriodeLabel(heure) {
      if (!heure) return ''
      return (heure >= '09:00' && heure <= '11:40') ? '🌅 MATIN (9h-12h)' : '🌆 APRÈS-MIDI (13h-16h)'
    }

    function formatDate(dateString) {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (error) {
        return dateString
      }
    }

    function clearSearch() {
      searchTerm.value = ''
      agentTrouve.value = null
    }

    async function rechercherAgent() {
      if (!searchTerm.value.trim()) {
        agentTrouve.value = null
        return
      }

      loading.value = true

      try {
        const result = await agentsStore.rechercherAgent(searchTerm.value.trim())
        agentTrouve.value = result
      } catch (error) {
        console.error('Erreur recherche:', error)
        agentTrouve.value = null
      } finally {
        loading.value = false
      }
    }

    function printAgentInfo() {
      if (!agentTrouve.value) return

      const agent = agentTrouve.value
      const totalPersonnes = parseInt(agent.nombre_proches) + 1
      const periode = getPeriodeLabel(agent.heure_arrivee)

      const printContent = `
        JOURNÉE DES PROCHES - FICHE AGENT

        Code Personnel: ${agent.code_personnel}
        Nom: ${agent.nom}
        Prénom: ${agent.prenom}
        Service: ${agent.service}
        Nombre de proches: ${agent.nombre_proches}
        Total personnes: ${totalPersonnes}
        Heure d'arrivée: ${agent.heure_arrivee}
        Période: ${periode}
        Date d'inscription: ${formatDate(agent.date_inscription)}

        Imprimé le: ${new Date().toLocaleString('fr-FR')}
      `

      const printWindow = window.open('', '_blank')
      printWindow.document.write(`
        <html>
          <head>
            <title>Fiche Agent - ${agent.prenom} ${agent.nom}</title>
            <style>
              body { font-family: Arial, sans-serif; padding: 20px; }
              pre { font-size: 14px; line-height: 1.5; }
            </style>
          </head>
          <body>
            <pre>${printContent}</pre>
          </body>
        </html>
      `)
      printWindow.document.close()
      printWindow.print()
    }

    onMounted(() => {
      // Focus automatique sur le champ de recherche
      const searchInput = document.querySelector('.search-input')
      if (searchInput) {
        searchInput.focus()
      }
    })

    return {
      searchTerm,
      agentTrouve,
      loading,
      totalInscriptions,
      groupeMatin,
      groupeApresMidi,
      totalProches,
      getBadgeClass,
      getPeriodeClass,
      getPeriodeLabel,
      formatDate,
      clearSearch,
      rechercherAgent,
      printAgentInfo
    }
  }
}
</script>

<style scoped>
.recherche-view h2 {
  margin-bottom: 30px;
  color: #2c3e50;
  font-size: 2em;
  text-align: center;
}

.search-container {
  max-width: 600px;
  margin: 0 auto 40px;
}

.search-bar {
  position: relative;
  margin-bottom: 15px;
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
  padding: 20px 20px 20px 55px;
  border: 3px solid #e9ecef;
  border-radius: 15px;
  font-size: 1.2em;
  background: #f8f9fa;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #3498db;
  background: white;
  box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}

.clear-button {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  background: #6c757d;
  color: white;
  border: none;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.2em;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clear-button:hover {
  background: #5a6268;
}

.search-info {
  text-align: center;
  color: #6c757d;
  font-size: 0.9em;
}

.no-results {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.no-results-icon {
  font-size: 4em;
  margin-bottom: 20px;
  opacity: 0.5;
}

.no-results-icon.error {
  color: #dc3545;
}

.examples {
  margin-top: 30px;
}

.example-code {
  display: inline-block;
  background: #e9ecef;
  padding: 8px 12px;
  margin: 5px;
  border-radius: 5px;
  font-family: monospace;
  cursor: pointer;
  transition: background 0.3s ease;
}

.example-code:hover {
  background: #3498db;
  color: white;
}

.loading-search {
  text-align: center;
  padding: 40px;
  color: #6c757d;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.agent-found {
  margin-top: 30px;
}

.found-header {
  text-align: center;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.found-header h3 {
  color: #155724;
  font-size: 1.8em;
  margin: 0;
}

.found-badge {
  background: #28a745;
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.9em;
  font-weight: 600;
  text-transform: uppercase;
}

.agent-card-large {
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  border: 2px solid #28a745;
  border-radius: 20px;
  padding: 30px;
  max-width: 800px;
  margin: 0 auto;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.agent-header {
  text-align: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #28a745;
}

.agent-name {
  font-size: 2.2em;
  font-weight: 700;
  color: #155724;
  margin-bottom: 10px;
}

.agent-code {
  font-size: 1.4em;
  color: #155724;
  font-family: monospace;
  background: rgba(21, 87, 36, 0.1);
  padding: 8px 16px;
  border-radius: 10px;
  display: inline-block;
}

.agent-details {
  margin-bottom: 30px;
}

.detail-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 15px;
  background: rgba(255,255,255,0.7);
  border-radius: 10px;
}

.detail-item.full-width {
  grid-column: 1 / -1;
}

.detail-icon {
  font-size: 1.5em;
  margin-top: 5px;
}

.detail-label {
  display: block;
  font-size: 0.9em;
  color: #155724;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 5px;
}

.detail-value {
  display: block;
  font-size: 1.2em;
  font-weight: 600;
  color: #155724;
}

.detail-value.large {
  font-size: 1.8em;
}

.detail-value.highlight {
  color: #e67e22;
  background: rgba(230, 126, 34, 0.1);
  padding: 5px 10px;
  border-radius: 8px;
  display: inline-block;
}

.badge {
  display: inline-block;
  padding: 10px 20px;
  border-radius: 25px;
  font-size: 1.1em;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 5px;
  font-family: monospace;
}

.badge-morning {
  background: linear-gradient(135deg, #f39c12, #e67e22);
  color: white;
}

.badge-afternoon {
  background: linear-gradient(135deg, #9b59b6, #8e44ad);
  color: white;
}

.periode-badge {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 1em;
  font-weight: 600;
  margin-top: 5px;
}

.periode-matin {
  background: #fff3cd;
  color: #856404;
  border: 2px solid #ffc107;
}

.periode-apres-midi {
  background: #e2e3f3;
  color: #5a5c96;
  border: 2px solid #9b59b6;
}

.actions-section {
  text-align: center;
  display: flex;
  gap: 15px;
  justify-content: center;
}

.btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #5a6268);
}

.btn-primary {
  background: linear-gradient(135deg, #28a745, #218838);
}

.help-section {
  margin-top: 30px;
}

.help-text {
  margin-bottom: 20px;
  font-size: 0.9em;
  color: #6c757d;
}

.quick-stats {
  margin-top: 50px;
  padding: 30px;
  background: #f8f9fa;
  border-radius: 15px;
  border: 1px solid #e9ecef;
}

.quick-stats h4 {
  text-align: center;
  margin-bottom: 20px;
  color: #2c3e50;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 20px;
}

.stat-item {
  text-align: center;
  padding: 15px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.stat-number {
  display: block;
  font-size: 2em;
  font-weight: 700;
  color: #3498db;
  margin-bottom: 5px;
}

.stat-label {
  display: block;
  font-size: 0.9em;
  color: #6c757d;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .detail-row {
    grid-template-columns: 1fr;
  }

  .actions-section {
    flex-direction: column;
    align-items: center;
  }

  .btn {
    width: 100%;
    max-width: 250px;
  }

  .agent-name {
    font-size: 1.8em;
  }

  .agent-code {
    font-size: 1.2em;
  }
}

@media (max-width: 480px) {
  .search-input {
    font-size: 1em;
    padding: 15px 15px 15px 45px;
  }

  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
