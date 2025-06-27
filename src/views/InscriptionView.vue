<template>
  <div class="inscription-view">
    <h2>Inscription d'un agent</h2>
    <p class="subtitle">Journée des Proches - Système d'inscription en amont</p>

    <!-- Message d'alerte -->
    <div v-if="alertMessage" :class="alertClass" class="alert">
      {{ alertMessage }}
      <button @click="clearAlert" class="alert-close">×</button>
    </div>

    <!-- Indicateur de chargement -->
    <div v-if="loading" class="loading-indicator">
      <div class="spinner"></div>
      <p>Inscription en cours...</p>
    </div>

    <form @submit.prevent="inscrireAgent" class="inscription-form">
      <div class="form-grid">
        <!-- Code Personnel -->
        <div class="form-group">
          <label for="codePersonnel">Code Personnel (CP) *</label>
          <input
            v-model="form.codePersonnel"
            type="text"
            id="codePersonnel"
            required
            pattern="[0-9]{7}[A-Za-z]{1}"
            title="7 chiffres suivis d'une lettre"
            placeholder="Ex: 1234567A"
            maxlength="8"
            :disabled="loading"
          >
          <small class="form-help">
            📝 Format requis : 7 chiffres suivis d'une lettre (ex: 1234567A)
          </small>
        </div>

        <!-- Nom -->
        <div class="form-group">
          <label for="nom">Nom *</label>
          <input
            v-model="form.nom"
            type="text"
            id="nom"
            required
            placeholder="Nom de famille"
            :disabled="loading"
          >
        </div>

        <!-- Prénom -->
        <div class="form-group">
          <label for="prenom">Prénom *</label>
          <input
            v-model="form.prenom"
            type="text"
            id="prenom"
            required
            placeholder="Prénom"
            :disabled="loading"
          >
        </div>

        <!-- Adresse email -->
        <div class="form-group">
          <label for="email">Adresse email professionnelle SNCF *</label>
          <div style="display: flex; align-items: center;">
            <input
              v-model="form.email"
              type="text"
              id="email"
              required
              pattern="[a-zA-Z0-9.\-]+\.[a-zA-Z0-9.\-]+"
              placeholder="prenom.nom"
              :disabled="loading"
              style="flex: 1;"
            >
            <span style="margin-left: 4px;">@sncf.fr</span>
          </div>
          <small class="form-help">
            Votre adresse ne sera <b>pas conservée</b>, elle servira uniquement à l'envoi de la confirmation de réservation.<br>
            <b>Seules les adresses professionnelles SNCF sont acceptées.</b>
          </small>
        </div>

        <!-- Nombre de proches (0 à 4) - SPECIFICATION 2.1 -->
        <div class="form-group nombre-proches-group">
          <label for="nombreProches">Nombre de proches accompagnants *</label>
          <select v-model="form.nombreProches" id="nombreProches" required :disabled="loading" class="select-proches">
            <option value="">Sélectionner le nombre</option>
            <option value="0">0 proche (agent seul)</option>
            <option value="1">1 proche</option>
            <option value="2">2 proches</option>
            <option value="3">3 proches</option>
            <option value="4">4 proches (maximum autorisé)</option>
          </select>
          <small class="form-help">
            🔢 <strong>Maximum 4 proches accompagnants</strong><br>
            💡 N'oubliez pas de sélectionner "0" si vous venez seul(e)
          </small>
        </div>

        <!-- Résumé personnes -->
        <div v-if="form.nombreProches !== ''" class="form-group total-personnes">
          <label>Total de personnes</label>
          <div class="total-display">
            <span class="total-number">{{ parseInt(form.nombreProches) + 1 }}</span>
            <span class="total-detail">
              (vous + {{ form.nombreProches }} proche{{ form.nombreProches > 1 ? 's' : '' }})
            </span>
          </div>
        </div>

        <!-- Intéressé par restauration sur place -->
        <div class="form-group">
          <label>
            <input type="checkbox" v-model="form.fast_food_check" :disabled="loading">
            Je suis intéressé(e) par la possibilité de me restaurer sur place (snacking, foodtruck...)
          </label>
          <small class="form-help">
            Cette information est recueillie à titre indicatif pour l'organisation. Elle n'engage à rien.
          </small>
        </div>
      </div>

      <!-- Section créneaux horaires - SPECIFICATION 2.1 -->
      <div class="creneaux-section">
        <h3>⏰ Heure d'arrivée souhaitée *</h3>
        <div class="creneaux-info-box">
          <p class="creneaux-info">
            <strong>📋 Créneaux disponibles :</strong><br>
            🌅 <strong>Matin :</strong> 9h00 à 11h40 (créneaux de 20 minutes)<br>
            🌆 <strong>Après-midi :</strong> 13h00 à 15h40 (créneaux de 20 minutes)
          </p>
          <p class="capacity-warning">
            <strong>⚠️ Capacité maximale :</strong> 14 personnes par créneau<br>
            Le nombre de places restantes est affiché pour chaque créneau.
          </p>
        </div>

        <!-- Chargement des créneaux -->
        <div v-if="loadingCreneaux" class="loading-creneaux">
          <div class="spinner-small"></div>
          <span>Chargement des disponibilités...</span>
        </div>

        <!-- Créneaux matin - 9h00 à 11h40 -->
        <div v-else class="periode-section">
          <h4 class="periode-title">🌅 Créneaux Matin (9h00 - 11h40)</h4>
          <div class="creneaux-grid">
            <label
              v-for="(info, heure) in creneauxMatin"
              :key="heure"
              :class="['creneau-option', {
                'complet': info.complet,
                'selected': form.heureArrivee === heure,
                'limite': info.places_restantes <= 3 && !info.complet,
                'indisponible': !peutInscrire(info)
              }]"
            >
              <input
                type="radio"
                v-model="form.heureArrivee"
                :value="heure"
                :disabled="loading || info.complet || !peutInscrire(info)"
                required
              >
              <div class="creneau-content">
                <div class="creneau-heure">{{ heure }}</div>
                <div class="creneau-info">
                  <span v-if="info.complet" class="status-complet">
                    🚫 COMPLET
                  </span>
                  <span v-else-if="!peutInscrire(info)" class="status-insuffisant">
                    ❌ Pas assez de places<br>
                    ({{ info.places_restantes }} restantes)
                  </span>
                  <span v-else class="status-places">
                    ✅ {{ info.places_restantes }} place{{ info.places_restantes > 1 ? 's' : '' }} libre{{ info.places_restantes > 1 ? 's' : '' }}
                  </span>
                </div>
                <div class="creneau-occupation">
                  👥 {{ info.personnes_total }}/14 personnes
                </div>
                <div v-if="info.places_restantes <= 3 && !info.complet" class="creneau-warning">
                  ⚡ Places limitées !
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Créneaux après-midi - 13h00 à 15h40 -->
        <div class="periode-section">
          <h4 class="periode-title">🌆 Créneaux Après-midi (13h00 - 15h40)</h4>
          <div class="creneaux-grid">
            <label
              v-for="(info, heure) in creneauxApresMidi"
              :key="heure"
              :class="['creneau-option', {
                'complet': info.complet,
                'selected': form.heureArrivee === heure,
                'limite': info.places_restantes <= 3 && !info.complet,
                'indisponible': !peutInscrire(info)
              }]"
            >
              <input
                type="radio"
                v-model="form.heureArrivee"
                :value="heure"
                :disabled="loading || info.complet || !peutInscrire(info)"
                required
              >
              <div class="creneau-content">
                <div class="creneau-heure">{{ heure }}</div>
                <div class="creneau-info">
                  <span v-if="info.complet" class="status-complet">
                    🚫 COMPLET
                  </span>
                  <span v-else-if="!peutInscrire(info)" class="status-insuffisant">
                    ❌ Pas assez de places<br>
                    ({{ info.places_restantes }} restantes)
                  </span>
                  <span v-else class="status-places">
                    ✅ {{ info.places_restantes }} place{{ info.places_restantes > 1 ? 's' : '' }} libre{{ info.places_restantes > 1 ? 's' : '' }}
                  </span>
                </div>
                <div class="creneau-occupation">
                  👥 {{ info.personnes_total }}/14 personnes
                </div>
                <div v-if="info.places_restantes <= 3 && !info.complet" class="creneau-warning">
                  ⚡ Places limitées !
                </div>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Résumé de l'inscription -->
      <div v-if="form.heureArrivee && form.nombreProches !== ''" class="resume-section">
        <h4>📋 Résumé de votre inscription</h4>
        <div class="resume-content">
          <div class="resume-item">
            <span class="resume-label">👤 Agent :</span>
            <span class="resume-value">{{ form.prenom }} {{ form.nom }}</span>
          </div>
          <div class="resume-item">
            <span class="resume-label">👥 Nombre total de personnes :</span>
            <span class="resume-value highlight">
              {{ parseInt(form.nombreProches) + 1 }}
              (vous + {{ form.nombreProches }} proche{{ form.nombreProches > 1 ? 's' : '' }})
            </span>
          </div>
          <div class="resume-item">
            <span class="resume-label">⏰ Créneau sélectionné :</span>
            <span class="resume-value highlight">{{ form.heureArrivee }}</span>
          </div>
          <div class="resume-item">
            <span class="resume-label">🕐 Période :</span>
            <span class="resume-value">{{ getPeriodeLabel(form.heureArrivee) }}</span>
          </div>
        </div>
      </div>

      <!-- Bouton d'inscription -->
      <div class="form-submit">
        <button type="submit" class="btn btn-primary" :disabled="loading || !peutValiderInscription">
          <span v-if="loading">⏳ Inscription en cours...</span>
          <span v-else>✅ Confirmer l'inscription</span>
        </button>
        <p v-if="!peutValiderInscription && (form.codePersonnel || form.nom)" class="validation-help">
          📝 Veuillez remplir tous les champs obligatoires pour continuer
        </p>
      </div>
    </form>

    <!-- Liens de navigation -->
    <div class="footer-links">
      <router-link to="/gestion" class="link">
        👥 Voir tous les agents inscrits
      </router-link>
      <router-link to="/recherche" class="link">
        🔍 Recherche rapide (Jour J)
      </router-link>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAgentsStore } from '@/stores/agents'
import { storeToRefs } from 'pinia'

export default {
  name: 'InscriptionView',
  setup() {
    const agentsStore = useAgentsStore()
    const { loading, creneaux } = storeToRefs(agentsStore)

    const alertMessage = ref('')
    const alertType = ref('success')
    const loadingCreneaux = ref(false)

    const form = reactive({
      codePersonnel: '',
      nom: '',
      prenom: '',
      email: '',
      nombreProches: '',
      heureArrivee: '',
      fast_food_check: false  // Changé de interesseRestauration à fast_food_check
    })

    // Computed properties
    const alertClass = computed(() => ({
      'alert-success': alertType.value === 'success',
      'alert-error': alertType.value === 'error'
    }))

    const creneauxMatin = computed(() => {
      // Créneaux matin : 9h00 à 11h40 (toutes les 20 minutes)
      return creneaux.value.matin || {}
    })

    const creneauxApresMidi = computed(() => {
      // Créneaux après-midi : 13h00 à 15h40 (toutes les 20 minutes)
      return creneaux.value['apres-midi'] || {}
    })

    const peutValiderInscription = computed(() => {
      return form.codePersonnel && form.nom && form.prenom && form.email &&
        form.nombreProches !== '' && form.heureArrivee
    })

    // Fonctions utilitaires
    function peutInscrire(creneauInfo) {
      if (!form.nombreProches && form.nombreProches !== '0') return true
      const personnesAInscrire = parseInt(form.nombreProches) + 1
      return creneauInfo.places_restantes >= personnesAInscrire
    }

    function getPeriodeLabel(heure) {
      if (!heure) return ''
      return (heure >= '09:00' && heure <= '11:40') ? '🌅 Matin (9h-12h)' : '🌆 Après-midi (13h-16h)'
    }

    function clearAlert() {
      alertMessage.value = ''
    }

    function resetForm() {
      Object.keys(form).forEach(key => {
        form[key] = ''
      })
    }

    async function chargerCreneauxDisponibles() {
      loadingCreneaux.value = true
      try {
        await agentsStore.chargerCreneaux()
      } catch (error) {
        console.error('Erreur chargement créneaux:', error)
        alertMessage.value = 'Erreur lors du chargement des créneaux disponibles'
        alertType.value = 'error'
      } finally {
        loadingCreneaux.value = false
      }
    }

    async function inscrireAgent() {
      try {
        console.log("État de la checkbox:", form.fast_food_check);
        // Validation côté client
        if (!peutValiderInscription.value) {
          throw new Error('Veuillez remplir tous les champs obligatoires')
        }

        // Vérifier que le code personnel est valide (7 chiffres + 1 lettre)
        if (!/^[0-9]{7}[A-Za-z]{1}$/.test(form.codePersonnel)) {
          throw new Error('Le code personnel doit contenir exactement 7 chiffres suivis d\'une lettre (ex: 1234567A)')
        }

        // Vérifier que le nombre de proches est valide (0 à 4)
        const nbProches = parseInt(form.nombreProches)
        if (nbProches < 0 || nbProches > 4) {
          throw new Error('Le nombre de proches doit être entre 0 et 4')
        }

        // Vérifier la disponibilité du créneau
        const creneauInfo = creneauxMatin.value[form.heureArrivee] || creneauxApresMidi.value[form.heureArrivee]
        if (!creneauInfo) {
          throw new Error('Créneau invalide')
        }

        if (!peutInscrire(creneauInfo)) {
          throw new Error(`Ce créneau n'a plus assez de places disponibles (${creneauInfo.places_restantes} places restantes, ${nbProches + 1} personnes demandées)`)
        }

        const agent = {
          codePersonnel: form.codePersonnel.trim(),
          nom: form.nom.trim().toUpperCase(),
          prenom: form.prenom.trim(),
          email: form.email.trim(),
          nombreProches: nbProches,
          heureArrivee: form.heureArrivee,
          fast_food_check: form.fast_food_check ? 1 : 0  // Utiliser directement fast_food_check
        }

        console.log("Données envoyées à l'API:", agent);
        await agentsStore.ajouterAgent(agent)

        // Envoi de l'email de confirmation sans stocker l'email
        try {
          await fetch('/send-registration-mail.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: agent.email, nom: agent.prenom })
          })
        } catch (e) {
          // L'échec de l'envoi du mail ne bloque pas l'inscription
          console.error('Erreur lors de l\'envoi de l\'email :', e)
        }

        const nombrePersonnes = agent.nombreProches + 1
        alertMessage.value = `✅ Agent ${agent.prenom} ${agent.nom} inscrit avec succès !

🕐 Créneau : ${agent.heureArrivee} (${getPeriodeLabel(agent.heureArrivee)})
👥 Nombre de personnes : ${nombrePersonnes} (vous + ${agent.nombreProches} proche${agent.nombreProches > 1 ? 's' : ''})

📧 Pensez à noter ces informations pour le jour J !`
        alertType.value = 'success'

        resetForm()

        // Scroll to the top of the page
        window.scrollTo({ top: 0, behavior: 'smooth' })

        // Recharger les créneaux pour mettre à jour les disponibilités
        await chargerCreneauxDisponibles()

        // Supprimer l'alerte après 10 secondes
        setTimeout(clearAlert, 10000)

      } catch (error) {
        console.error('Erreur inscription:', error)
        alertMessage.value = `❌ Erreur lors de l'inscription : ${error.message}`
        alertType.value = 'error'
        setTimeout(clearAlert, 8000)
      }
    }

    // Charger les créneaux au montage
    onMounted(() => {
      chargerCreneauxDisponibles()
    })

    return {
      form,
      alertMessage,
      alertType,
      alertClass,
      loading,
      loadingCreneaux,
      creneauxMatin,
      creneauxApresMidi,
      peutValiderInscription,
      peutInscrire,
      getPeriodeLabel,
      inscrireAgent,
      clearAlert,
      chargerCreneauxDisponibles
    }
  }
}
</script>


<style scoped>
.inscription-view {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.inscription-view h2 {
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

.loading-indicator, .loading-creneaux {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  color: #6c757d;
  gap: 10px;
}

.spinner, .spinner-small {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.spinner {
  width: 30px;
  height: 30px;
}

.spinner-small {
  width: 20px;
  height: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.alert {
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-weight: 500;
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  white-space: pre-line;
  line-height: 1.5;
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
  flex-shrink: 0;
}

.alert-close:hover {
  opacity: 1;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
  margin-bottom: 40px;
}

.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 1em;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group input:disabled,
.form-group select:disabled {
  background-color: #f8f9fa;
  opacity: 0.6;
  cursor: not-allowed;
}

.form-help {
  display: block;
  margin-top: 8px;
  font-size: 0.9em;
  color: #6c757d;
  line-height: 1.4;
}

/* Styles spécifiques pour le nombre de proches */
.nombre-proches-group {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
  border: 2px solid #e9ecef;
}

.select-proches {
  background: white;
  font-weight: 600;
}

.total-personnes {
  background: #e8f5e8;
  padding: 20px;
  border-radius: 12px;
  border: 2px solid #28a745;
}

.total-display {
  display: flex;
  align-items: center;
  gap: 10px;
}

.total-number {
  font-size: 2em;
  font-weight: 700;
  color: #28a745;
  background: white;
  padding: 10px 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.total-detail {
  color: #155724;
  font-weight: 600;
}

.creneaux-section {
  margin: 40px 0;
  padding: 30px;
  background: #f8f9fa;
  border-radius: 15px;
  border: 2px solid #e9ecef;
}

.creneaux-section h3 {
  margin-bottom: 20px;
  color: #2c3e50;
  font-size: 1.5em;
}

.creneaux-info-box {
  margin-bottom: 25px;
  padding: 20px;
  background: #fff;
  border-radius: 10px;
  border-left: 4px solid #3498db;
}

.creneaux-info {
  margin-bottom: 15px;
  color: #2c3e50;
  line-height: 1.6;
}

.capacity-warning {
  color: #856404;
  background: #fff3cd;
  padding: 10px;
  border-radius: 6px;
  border-left: 4px solid #ffc107;
  margin: 0;
  line-height: 1.5;
}

.periode-section {
  margin-bottom: 30px;
}

.periode-title {
  margin-bottom: 20px;
  color: #2c3e50;
  font-size: 1.3em;
  padding-bottom: 10px;
  border-bottom: 2px solid #3498db;
}

.creneaux-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 15px;
}

.creneau-option {
  display: block;
  padding: 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
  position: relative;
}

.creneau-option:hover:not(.complet):not(.indisponible) {
  border-color: #3498db;
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.1);
  transform: translateY(-2px);
}

.creneau-option.selected {
  border-color: #28a745;
  background: #d4edda;
  box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
  transform: translateY(-2px);
}

.creneau-option.complet {
  border-color: #dc3545;
  background: #f8d7da;
  cursor: not-allowed;
  opacity: 0.7;
}

.creneau-option.indisponible {
  border-color: #e67e22;
  background: #fdf2e9;
  cursor: not-allowed;
  opacity: 0.8;
}

.creneau-option.limite:not(.complet):not(.indisponible) {
  border-color: #ffc107;
  background: #fff3cd;
}

.creneau-option input[type="radio"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.creneau-content {
  text-align: center;
}

.creneau-heure {
  font-size: 1.4em;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 8px;
  font-family: monospace;
}

.creneau-info {
  margin-bottom: 8px;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-complet {
  color: #dc3545;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.9em;
}

.status-insuffisant {
  color: #e67e22;
  font-weight: 600;
  font-size: 0.85em;
  text-align: center;
  line-height: 1.3;
}

.status-places {
  color: #28a745;
  font-weight: 600;
  font-size: 0.9em;
  text-align: center;
}

.creneau-occupation {
  font-size: 0.85em;
  color: #6c757d;
  margin-bottom: 5px;
}

.creneau-warning {
  font-size: 0.8em;
  color: #ff8f00;
  font-weight: 600;
  background: rgba(255, 143, 0, 0.1);
  padding: 3px 6px;
  border-radius: 4px;
}

.resume-section {
  margin: 30px 0;
  padding: 25px;
  background: #e8f5e8;
  border-radius: 15px;
  border-left: 5px solid #28a745;
}

.resume-section h4 {
  margin-bottom: 20px;
  color: #155724;
  font-size: 1.3em;
}

.resume-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.resume-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.resume-label {
  font-weight: 500;
  color: #155724;
}

.resume-value {
  font-weight: 600;
  color: #155724;
}

.resume-value.highlight {
  font-size: 1.1em;
  background: rgba(21, 87, 36, 0.1);
  padding: 5px 10px;
  border-radius: 6px;
}

.form-submit {
  text-align: center;
  margin: 40px 0;
}

.btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  padding: 18px 40px;
  border-radius: 10px;
  font-size: 1.2em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  min-width: 300px;
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

.btn-primary {
  background: linear-gradient(135deg, #28a745, #218838);
}

.validation-help {
  margin-top: 15px;
  color: #6c757d;
  font-size: 0.9em;
  font-style: italic;
}

.footer-links {
  text-align: center;
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: center;
  gap: 30px;
}

.link {
  color: #3498db;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
  font-size: 1.1em;
}

.link:hover {
  color: #2980b9;
  text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .creneaux-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  }

  .inscription-view h2 {
    font-size: 1.8em;
  }

  .footer-links {
    flex-direction: column;
    gap: 15px;
  }

  .resume-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }

  .btn {
    width: 100%;
    min-width: auto;
    padding: 15px 20px;
    font-size: 1.1em;
  }

  .total-display {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .total-number {
    font-size: 1.8em;
  }
}

@media (max-width: 480px) {
  .creneaux-grid {
    grid-template-columns: 1fr;
  }

  .inscription-view {
    padding: 15px;
  }

  .creneaux-section {
    padding: 20px;
  }

  .periode-title {
    font-size: 1.1em;
  }

  .creneau-heure {
    font-size: 1.2em;
  }
}

/* Animations pour les états des créneaux */
@keyframes pulse-warning {
  0% { background-color: #fff3cd; }
  50% { background-color: #ffeaa7; }
  100% { background-color: #fff3cd; }
}

.creneau-option.limite:not(.selected) {
  animation: pulse-warning 2s infinite;
}

/* Indicateur visuel pour les créneaux recommandés */
.creneau-option:not(.complet):not(.indisponible):not(.limite)::before {
  content: "✨";
  position: absolute;
  top: 5px;
  right: 5px;
  font-size: 0.8em;
  opacity: 0.6;
}

.creneau-option.selected::before {
  content: "✅";
  opacity: 1;
}

/* Améliorations pour l'accessibilité */
.creneau-option:focus-within {
  outline: 3px solid #3498db;
  outline-offset: 2px;
}

.form-group input:invalid:not(:placeholder-shown) {
  border-color: #dc3545;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.form-group input:valid:not(:placeholder-shown) {
  border-color: #28a745;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

/* Indicateurs de progression dans le formulaire */
.form-progress {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
  gap: 10px;
}

.progress-step {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #e9ecef;
  transition: background 0.3s ease;
}

.progress-step.completed {
  background: #28a745;
}

.progress-step.current {
  background: #3498db;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}
</style>
