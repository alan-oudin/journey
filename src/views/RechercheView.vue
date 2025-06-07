<template>
  <div class="recherche-view">
    <h2>🔍 Recherche & Pointage - Jour J</h2>
    <p class="subtitle">Interface de recherche rapide et gestion des présences</p>

    <!-- Message d'alerte pour les modifications de statut -->
    <div v-if="messageStatut" :class="['alert', `alert-${typeMessageStatut}`]" class="message-statut">
      {{ messageStatut }}
      <button @click="messageStatut = ''" class="alert-close">×</button>
    </div>

    <div class="search-container">
      <div class="search-bar">
        <div class="search-icon">🔍</div>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Tapez le code personnel (CP) de l'agent..."
          class="search-input"
          @input="validateCodePersonnel"
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
          <span class="example-code" @click="searchTerm = '1234567A'; validateCodePersonnel()">1234567A</span>
          <span class="example-code" @click="searchTerm = '2345678B'; validateCodePersonnel()">2345678B</span>
          <span class="example-code" @click="searchTerm = '3456789C'; validateCodePersonnel()">3456789C</span>
        </div>
      </div>

      <!-- Agent trouvé -->
      <div v-else-if="agentTrouve" class="agent-found">
        <div class="found-header">
          <h3>✅ Agent trouvé!</h3>
          <div class="found-badge" :class="getStatutClass(agentTrouve.statut)">
            {{ getStatutLabel(agentTrouve.statut) }}
          </div>
        </div>

        <div class="agent-card-large">
          <div class="agent-header">
            <div class="agent-name">{{ agentTrouve.prenom }} {{ agentTrouve.nom }}</div>
            <div class="agent-code">CP: {{ agentTrouve.code_personnel }}</div>
          </div>

          <div class="agent-details">
            <div class="detail-row">
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

            <!-- Section Statut avec gestion des présences -->
            <div class="detail-row">
              <div class="detail-item full-width statut-section">
                <span class="detail-icon">📋</span>
                <div class="statut-container">
                  <span class="detail-label">Statut actuel</span>
                  <div class="statut-display">
                    <span class="statut-badge" :class="getStatutClass(agentTrouve.statut)">
                      {{ getStatutLabel(agentTrouve.statut) }}
                    </span>
                    <!-- Affichage de l'heure de pointage si présent -->
                    <div v-if="agentTrouve.statut === 'present' && agentTrouve.heure_validation" class="pointage-info">
                      <span class="pointage-label">⏰ Pointé le :</span>
                      <span class="pointage-heure">{{ formatDate(agentTrouve.heure_validation) }}</span>
                    </div>
                  </div>

                  <!-- Actions rapides de statut -->
                  <div class="actions-rapides-statut">
                    <h4>🚀 Actions rapides - Jour J :</h4>
                    <div class="boutons-statuts">
                      <button
                        @click="modifierStatutRapide('present')"
                        class="btn-statut-rapide btn-present"
                        :disabled="loadingStatut || agentTrouve.statut === 'present'"
                        :class="{ 'active': agentTrouve.statut === 'present' }"
                      >
                        <span v-if="loadingStatut && nouveauStatut === 'present'">⏳</span>
                        <span v-else>✅</span>
                        Marquer PRÉSENT
                      </button>

                      <button
                        @click="modifierStatutRapide('absent')"
                        class="btn-statut-rapide btn-absent"
                        :disabled="loadingStatut || agentTrouve.statut === 'absent'"
                        :class="{ 'active': agentTrouve.statut === 'absent' }"
                      >
                        <span v-if="loadingStatut && nouveauStatut === 'absent'">⏳</span>
                        <span v-else>❌</span>
                        Marquer ABSENT
                      </button>

                      <button
                        @click="modifierStatutRapide('inscrit')"
                        class="btn-statut-rapide btn-inscrit"
                        :disabled="loadingStatut || agentTrouve.statut === 'inscrit'"
                        :class="{ 'active': agentTrouve.statut === 'inscrit' }"
                      >
                        <span v-if="loadingStatut && nouveauStatut === 'inscrit'">⏳</span>
                        <span v-else>📝</span>
                        Remettre INSCRIT
                      </button>

<!--                      <button-->
<!--                        @click="confirmerAnnulation"-->
<!--                        class="btn-statut-rapide btn-annule"-->
<!--                        :disabled="loadingStatut || agentTrouve.statut === 'annule'"-->
<!--                        :class="{ 'active': agentTrouve.statut === 'annule' }"-->
<!--                      >-->
<!--                        <span v-if="loadingStatut && nouveauStatut === 'annule'">⏳</span>-->
<!--                        <span v-else>🚫</span>-->
<!--                        ANNULER inscription-->
<!--                      </button>-->
                    </div>
                  </div>
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

            <!-- Dernière mise à jour si modifié -->
            <div v-if="agentTrouve.updated_at && agentTrouve.updated_at !== agentTrouve.date_inscription" class="detail-row">
              <div class="detail-item full-width">
                <span class="detail-icon">🔄</span>
                <div>
                  <span class="detail-label">Dernière modification</span>
                  <span class="detail-value">{{ formatDate(agentTrouve.updated_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Heure de validation (pointage) si présent -->
            <div v-if="agentTrouve.heure_validation" class="detail-row">
              <div class="detail-item full-width validation-info">
                <span class="detail-icon">✅</span>
                <div>
                  <span class="detail-label">Heure de pointage (présence validée)</span>
                  <span class="detail-value highlight">{{ formatDate(agentTrouve.heure_validation) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="actions-section">
            <button @click="printAgentInfo" class="btn btn-secondary">
              🖨️ Imprimer fiche
            </button>
            <button @click="nouvelleRecherche" class="btn btn-primary">
              🔍 Nouvelle recherche
            </button>
            <router-link to="/gestion" class="btn btn-info">
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
    const loadingStatut = ref(false)
    const nouveauStatut = ref('')
    const messageStatut = ref('')
    const typeMessageStatut = ref('success')

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

    function getStatutClass(statut) {
      const classes = {
        'inscrit': 'statut-inscrit',
        'present': 'statut-present',
        'absent': 'statut-absent',
        'annule': 'statut-annule'
      }
      return classes[statut] || 'statut-inscrit'
    }

    function getStatutLabel(statut) {
      const labels = {
        'inscrit': '📝 Inscrit',
        'present': '✅ Présent',
        'absent': '❌ Absent',
        'annule': '🚫 Annulé'
      }
      return labels[statut] || '📝 Inscrit'
    }

    function formatDate(dateString) {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleString('fr-FR', {
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
      messageStatut.value = ''
    }

    function nouvelleRecherche() {
      clearSearch()
      // Focus sur le champ de recherche
      setTimeout(() => {
        const searchInput = document.querySelector('.search-input')
        if (searchInput) {
          searchInput.focus()
        }
      }, 100)
    }

    function validateCodePersonnel() {
      // Si le champ est vide, réinitialiser
      if (!searchTerm.value.trim()) {
        agentTrouve.value = null
        messageStatut.value = ''
        return
      }

      // Vérifier le format du code personnel (7 chiffres + 1 lettre)
      const codePersonnelRegex = /^[0-9]{7}[A-Za-z]{1}$/

      // Si le format est invalide, afficher un message d'alerte
      if (!codePersonnelRegex.test(searchTerm.value.trim())) {
        messageStatut.value = 'Le code personnel doit contenir exactement 7 chiffres suivis d\'une lettre (ex: 1234567A)'
        typeMessageStatut.value = 'warning'
        agentTrouve.value = null
        return
      }

      // Si le format est valide, lancer la recherche
      rechercherAgent()
    }

    async function rechercherAgent() {
      if (!searchTerm.value.trim()) {
        agentTrouve.value = null
        return
      }

      loading.value = true
      messageStatut.value = ''

      try {
        const result = await agentsStore.rechercherAgent(searchTerm.value.trim())
        agentTrouve.value = result

        // Initialiser le nouveau statut avec le statut actuel
        if (result && result.statut) {
          nouveauStatut.value = result.statut
        }
      } catch (error) {
        console.error('Erreur recherche:', error)
        agentTrouve.value = null
      } finally {
        loading.value = false
      }
    }

    async function modifierStatut() {
      if (!agentTrouve.value || !nouveauStatut.value) return

      if (nouveauStatut.value === agentTrouve.value.statut) {
        messageStatut.value = 'Le statut sélectionné est identique au statut actuel'
        typeMessageStatut.value = 'warning'
        setTimeout(() => { messageStatut.value = '' }, 3000)
        return
      }

      loadingStatut.value = true
      messageStatut.value = ''

      try {
        const agentMisAJour = await agentsStore.modifierStatutAgent(
          agentTrouve.value.code_personnel,
          nouveauStatut.value
        )

        // Mettre à jour l'agent trouvé avec les nouvelles données
        agentTrouve.value = agentMisAJour

        const statutLabel = getStatutLabel(nouveauStatut.value)
        messageStatut.value = `✅ Statut modifié avec succès : ${statutLabel}`
        typeMessageStatut.value = 'success'

        // Effacer le message après 5 secondes
        setTimeout(() => { messageStatut.value = '' }, 5000)

      } catch (error) {
        console.error('Erreur modification statut:', error)
        messageStatut.value = `❌ Erreur lors de la modification : ${error.message}`
        typeMessageStatut.value = 'error'
        setTimeout(() => { messageStatut.value = '' }, 8000)
      } finally {
        loadingStatut.value = false
      }
    }

    async function modifierStatutRapide(statut) {
      if (!agentTrouve.value || statut === agentTrouve.value.statut) return

      nouveauStatut.value = statut
      loadingStatut.value = true
      messageStatut.value = ''

      try {
        const agentMisAJour = await agentsStore.modifierStatutAgent(
          agentTrouve.value.code_personnel,
          statut
        )

        // Mettre à jour l'agent trouvé avec les nouvelles données
        agentTrouve.value = agentMisAJour

        const statutLabel = getStatutLabel(statut)
        let message = `✅ Agent marqué comme : ${statutLabel}`

        // Message spécifique selon le statut
        if (statut === 'present') {
          message += ` • Pointage enregistré automatiquement à ${new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`
        } else if (statut === 'absent') {
          message += ` • L'agent ne s'est pas présenté`
        } else if (statut === 'annule') {
          message += ` • Inscription annulée - places libérées`
        } else if (statut === 'inscrit') {
          message += ` • Statut remis à l'inscription initiale`
        }

        messageStatut.value = message
        typeMessageStatut.value = 'success'

        // Effacer le message après 5 secondes
        setTimeout(() => { messageStatut.value = '' }, 5000)

      } catch (error) {
        console.error('Erreur modification statut rapide:', error)
        messageStatut.value = `❌ Erreur : ${error.message}`
        typeMessageStatut.value = 'error'
        setTimeout(() => { messageStatut.value = '' }, 8000)
      } finally {
        loadingStatut.value = false
      }
    }

    function confirmerAnnulation() {
      if (!agentTrouve.value) return

      const nomComplet = `${agentTrouve.value.prenom} ${agentTrouve.value.nom}`
      const nbPersonnes = parseInt(agentTrouve.value.nombre_proches) + 1

      if (confirm(`⚠️ ATTENTION - Annulation d'inscription !\n\nAgent : ${nomComplet}\nCreéneau : ${agentTrouve.value.heure_arrivee}\nPersonnes concernées : ${nbPersonnes}\n\nCette action va :\n• Annuler définitivement l'inscription\n• Libérer ${nbPersonnes} place${nbPersonnes > 1 ? 's' : ''} dans le créneau\n• Marquer le statut comme "Annulé"\n\nVoulez-vous vraiment continuer ?`)) {
        modifierStatutRapide('annule')
      }
    }

    function printAgentInfo() {
      if (!agentTrouve.value) return

      const agent = agentTrouve.value
      const totalPersonnes = parseInt(agent.nombre_proches) + 1
      const periode = getPeriodeLabel(agent.heure_arrivee)

      const printContent = `
═══════════════════════════════════════════════
     JOURNÉE DES PROCHES - FICHE AGENT
═══════════════════════════════════════════════

Code Personnel: ${agent.code_personnel}
Nom: ${agent.nom}
Prénom: ${agent.prenom}

───────────────────────────────────────────────
INFORMATIONS VISITE
───────────────────────────────────────────────
Nombre de proches: ${agent.nombre_proches}
Total personnes: ${totalPersonnes}
Heure d'arrivée: ${agent.heure_arrivee}
Période: ${periode}

───────────────────────────────────────────────
STATUT ACTUEL
───────────────────────────────────────────────
Statut: ${getStatutLabel(agent.statut)}
${agent.heure_validation ? `Pointage: ${formatDate(agent.heure_validation)}` : 'Pas encore pointé'}

───────────────────────────────────────────────
TRAÇABILITÉ
───────────────────────────────────────────────
Date inscription: ${formatDate(agent.date_inscription)}
${agent.updated_at ? `Dernière modification: ${formatDate(agent.updated_at)}` : ''}

═══════════════════════════════════════════════
Imprimé le: ${new Date().toLocaleString('fr-FR')}
═══════════════════════════════════════════════
      `

      const printWindow = window.open('', '_blank')
      printWindow.document.write(`
        <html>
          <head>
            <title>Fiche Agent - ${agent.prenom} ${agent.nom}</title>
            <style>
              body {
                font-family: 'Courier New', monospace;
                padding: 20px;
                line-height: 1.4;
                font-size: 12px;
              }
              pre {
                font-size: 12px;
                line-height: 1.4;
                white-space: pre-wrap;
              }
              @media print {
                body { margin: 0; padding: 15px; }
              }
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
      loadingStatut,
      nouveauStatut,
      messageStatut,
      typeMessageStatut,
      totalInscriptions,
      groupeMatin,
      groupeApresMidi,
      totalProches,
      getBadgeClass,
      getPeriodeClass,
      getPeriodeLabel,
      getStatutClass,
      getStatutLabel,
      formatDate,
      clearSearch,
      nouvelleRecherche,
      validateCodePersonnel,
      rechercherAgent,
      modifierStatut,
      modifierStatutRapide,
      confirmerAnnulation,
      printAgentInfo
    }
  }
}
</script>

<style scoped>
.recherche-view h2 {
  margin-bottom: 10px;
  color: #2c3e50;
  font-size: 2em;
  text-align: center;
}

.subtitle {
  text-align: center;
  color: #6c757d;
  margin-bottom: 30px;
  font-size: 1.1em;
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
  transition: all 0.3s ease;
  font-weight: 600;
}

.example-code:hover {
  background: #3498db;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
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

/* Styles pour les messages d'alerte */
.message-statut {
  margin-bottom: 20px;
  animation: slideDown 0.3s ease-out;
}

.alert {
  padding: 15px 20px;
  border-radius: 10px;
  margin-bottom: 15px;
  font-weight: 500;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.alert-success {
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
  border-left: 4px solid #28a745;
}

.alert-error {
  background: linear-gradient(135deg, #f8d7da, #f5c6cb);
  color: #721c24;
  border-left: 4px solid #dc3545;
}

.alert-warning {
  background: linear-gradient(135deg, #fff3cd, #ffeaa7);
  color: #856404;
  border-left: 4px solid #ffc107;
}

.alert-close {
  background: none;
  border: none;
  font-size: 1.4em;
  font-weight: bold;
  cursor: pointer;
  color: inherit;
  opacity: 0.7;
  transition: opacity 0.3s ease;
  margin-left: 15px;
  padding: 0;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.alert-close:hover {
  opacity: 1;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Styles pour la section statut améliorée */
.statut-section {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  padding: 25px;
  border-radius: 15px;
  border: 2px solid #e9ecef;
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.statut-container {
  width: 100%;
}

.statut-display {
  margin: 15px 0;
  text-align: center;
}

.statut-badge {
  display: inline-block;
  padding: 10px 20px;
  border-radius: 25px;
  font-size: 1.1em;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 5px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.statut-inscrit {
  background: linear-gradient(135deg, #e3f2fd, #bbdefb);
  color: #1565c0;
  border: 2px solid #2196f3;
}

.statut-present {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  color: #2e7d32;
  border: 2px solid #4caf50;
}

.statut-absent {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  color: #c62828;
  border: 2px solid #f44336;
}

.statut-annule {
  background: linear-gradient(135deg, #f3e5f5, #e1bee7);
  color: #7b1fa2;
  border: 2px solid #9c27b0;
}

.pointage-info {
  margin-top: 15px;
  padding: 12px;
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  border-radius: 10px;
  border: 2px solid #4caf50;
}

.pointage-label {
  display: block;
  font-size: 0.9em;
  color: #2e7d32;
  font-weight: 600;
  margin-bottom: 5px;
}

.pointage-heure {
  display: block;
  font-size: 1.1em;
  color: #1b5e20;
  font-weight: 700;
  font-family: monospace;
}

/* Actions rapides de statut */
.actions-rapides-statut {
  margin: 25px 0;
  padding: 20px;
  background: white;
  border-radius: 12px;
  border: 1px solid #dee2e6;
}

.actions-rapides-statut h4 {
  margin-bottom: 15px;
  color: #2c3e50;
  font-size: 1.1em;
  text-align: center;
}

.boutons-statuts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.btn-statut-rapide {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 15px 20px;
  border: 2px solid;
  border-radius: 10px;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  position: relative;
}

.btn-statut-rapide:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-statut-rapide:not(:disabled):hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.btn-present {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  color: #2e7d32;
  border-color: #4caf50;
}

.btn-present.active {
  background: linear-gradient(135deg, #4caf50, #388e3c);
  color: white;
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
}

.btn-absent {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  color: #c62828;
  border-color: #f44336;
}

.btn-absent.active {
  background: linear-gradient(135deg, #f44336, #d32f2f);
  color: white;
  box-shadow: 0 4px 15px rgba(244, 67, 54, 0.4);
}

.btn-inscrit {
  background: linear-gradient(135deg, #e3f2fd, #bbdefb);
  color: #1565c0;
  border-color: #2196f3;
}

.btn-inscrit.active {
  background: linear-gradient(135deg, #2196f3, #1976d2);
  color: white;
  box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4);
}

.btn-annule {
  background: linear-gradient(135deg, #f3e5f5, #e1bee7);
  color: #7b1fa2;
  border-color: #9c27b0;
}

.btn-annule.active {
  background: linear-gradient(135deg, #9c27b0, #7b1fa2);
  color: white;
  box-shadow: 0 4px 15px rgba(156, 39, 176, 0.4);
}

/* Modification avancée (collapsible) */
.modification-avancee {
  margin-top: 20px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  overflow: hidden;
}

.modification-avancee summary {
  padding: 12px 15px;
  background: #f8f9fa;
  cursor: pointer;
  font-weight: 600;
  color: #6c757d;
  border-bottom: 1px solid #dee2e6;
  transition: background 0.3s ease;
}

.modification-avancee summary:hover {
  background: #e9ecef;
}

.modification-avancee[open] summary {
  background: #e9ecef;
}

.statut-modification {
  padding: 20px;
  background: white;
}

.statut-modifier-label {
  display: block;
  margin-bottom: 10px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 1em;
}

.modification-controls {
  display: flex;
  gap: 15px;
  align-items: flex-end;
}

.modification-controls .statut-select {
  flex: 1;
  margin-bottom: 0;
}

.modification-controls .btn {
  white-space: nowrap;
}

.statut-select {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1em;
  background: white;
  margin-bottom: 15px;
  transition: border-color 0.3s ease;
}

.statut-select:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.statut-select:disabled {
  background-color: #f8f9fa;
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-statut {
  background: linear-gradient(135deg, #17a2b8, #138496);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-statut:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(23, 162, 184, 0.3);
}

.btn-statut:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Styles pour l'info de validation */
.validation-info {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  border: 2px solid #4caf50;
  animation: pulseValidation 2s ease-in-out infinite;
}

@keyframes pulseValidation {
  0% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); }
  100% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
}

.actions-section {
  text-align: center;
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
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

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #5a6268);
}

.btn-primary {
  background: linear-gradient(135deg, #28a745, #218838);
}

.btn-info {
  background: linear-gradient(135deg, #17a2b8, #138496);
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

  .boutons-statuts {
    grid-template-columns: 1fr;
  }

  .btn-statut-rapide {
    font-size: 0.9em;
    padding: 12px 16px;
  }

  .modification-controls {
    flex-direction: column;
    gap: 10px;
  }

  .modification-controls .btn {
    width: 100%;
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

  .statut-section {
    padding: 15px;
  }

  .actions-rapides-statut {
    padding: 15px;
  }

  .btn-statut-rapide {
    padding: 10px 12px;
    font-size: 0.85em;
  }
}
</style>
