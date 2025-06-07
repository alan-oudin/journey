<template>
  <div class="gestion-view">
    <h2>👥 Gestion des inscriptions</h2>
    <p class="subtitle">Journée des Proches - Vue d'ensemble et administration</p>

    <!-- Indicateur de chargement -->
    <div v-if="loading" class="loading-indicator">
      <div class="spinner"></div>
      <p>Chargement des données...</p>
    </div>

    <!-- Message d'erreur -->
    <div v-if="error" class="alert alert-error">
      <strong>Erreur :</strong> {{ error }}
      <button @click="chargerAgents" class="btn-retry">🔄 Réessayer</button>
    </div>

    <!-- Statistiques principales avec les nouveaux statuts -->
    <div class="stats-grid">
      <div class="stat-card primary">
        <div class="stat-number">{{ totalInscriptions }}</div>
        <div class="stat-label">Total agents</div>
      </div>
      <div class="stat-card success">
        <div class="stat-number">{{ statistiquesStatuts.presents }}</div>
        <div class="stat-label">Présents</div>
      </div>
      <div class="stat-card info">
        <div class="stat-number">{{ statistiquesStatuts.inscrits }}</div>
        <div class="stat-label">Inscrits</div>
      </div>
      <div class="stat-card warning">
        <div class="stat-number">{{ statistiquesStatuts.absents }}</div>
        <div class="stat-label">Absents</div>
      </div>
<!--      <div class="stat-card danger">-->
<!--        <div class="stat-number">{{ statistiquesStatuts.annules }}</div>-->
<!--        <div class="stat-label">Annulés</div>-->
<!--      </div>-->
      <div class="stat-card highlight">
        <div class="stat-number">{{ totalPersonnes }}</div>
        <div class="stat-label">Total personnes</div>
        <div class="stat-detail">{{ totalProches }} proches</div>
      </div>
    </div>

    <!-- Actions -->
    <div class="actions">
      <button @click="rafraichirDonnees" class="btn btn-secondary" :disabled="loading">
        🔄 Actualiser
      </button>
      <button @click="exporterDonnees" class="btn btn-primary" :disabled="loading || agentsTries.length === 0">
        📊 Exporter CSV
      </button>
      <button @click="voirDisponibilites" class="btn btn-info" :disabled="loading">
        📈 Disponibilités
      </button>
      <button @click="effacerTout" class="btn btn-danger" :disabled="loading || agentsTries.length === 0">
        🗑️ Effacer tout
      </button>
    </div>

    <!-- Filtres par statut -->
    <div v-if="agentsTries.length > 0" class="filtres-statut">
      <h3>🔍 Filtrer par statut :</h3>
      <div class="boutons-filtres">
        <button
          @click="filtreStatut = 'tous'"
          class="btn-filtre"
          :class="{ 'active': filtreStatut === 'tous' }"
        >
          Tous ({{ agentsTries.length }})
        </button>
        <button
          @click="filtreStatut = 'inscrit'"
          class="btn-filtre btn-filtre-inscrit"
          :class="{ 'active': filtreStatut === 'inscrit' }"
        >
          Inscrits ({{ statistiquesStatuts.inscrits }})
        </button>
        <button
          @click="filtreStatut = 'present'"
          class="btn-filtre btn-filtre-present"
          :class="{ 'active': filtreStatut === 'present' }"
        >
          Présents ({{ statistiquesStatuts.presents }})
        </button>
        <button
          @click="filtreStatut = 'absent'"
          class="btn-filtre btn-filtre-absent"
          :class="{ 'active': filtreStatut === 'absent' }"
        >
          Absents ({{ statistiquesStatuts.absents }})
        </button>
<!--        <button-->
<!--          @click="filtreStatut = 'annule'"-->
<!--          class="btn-filtre btn-filtre-annule"-->
<!--          :class="{ 'active': filtreStatut === 'annule' }"-->
<!--        >-->
<!--          Annulés ({{ statistiquesStatuts.annules }})-->
<!--        </button>-->
      </div>
    </div>

    <!-- Onglets par période -->
    <div v-if="!loading && agents.length > 0" class="onglets-container">
      <div class="onglets-header">
        <button
          @click="ongletActif = 'matin'"
          class="onglet-btn"
          :class="{ 'active': ongletActif === 'matin' }"
        >
          🌅 Matin ({{ agentsMatinFiltres.length }} agents)
        </button>
        <button
          @click="ongletActif = 'apres-midi'"
          class="onglet-btn"
          :class="{ 'active': ongletActif === 'apres-midi' }"
        >
          🌆 Après-midi ({{ agentsApresMidiFiltres.length }} agents)
        </button>
      </div>

      <!-- Contenu onglet Matin -->
      <div v-if="ongletActif === 'matin'" class="onglet-content">
        <div class="periode-header">
          <h3>🌅 Créneaux Matin (9h00 - 11h40)</h3>
          <p class="periode-summary">
            {{ agentsMatinFiltres.length }} agents • {{ personnesMatinFiltrees }} personnes
          </p>
        </div>

        <!-- Créneaux matin déroulables -->
        <div class="creneaux-deroulables">
          <div
            v-for="heure in creneauxMatin"
            :key="`matin-${heure}`"
            class="creneau-section"
          >
            <div
              class="creneau-header"
              @click="toggleCreneau('matin', heure)"
              :class="{
                'complet': getInfoCreneau('matin', heure).complet,
                'limite': getInfoCreneau('matin', heure).limite
              }"
            >
              <div class="creneau-info-principale">
                <span class="creneau-heure">{{ heure }}</span>
                <span class="creneau-chevron" :class="{ 'ouvert': creneauxOuverts[`matin-${heure}`] }">
                  ▼
                </span>
              </div>
              <div class="creneau-stats">
                <span class="stat-agents">{{ creneaux.matin && creneaux.matin[heure] ? creneaux.matin[heure].agents_inscrits : agentsParCreneauMatin[heure].length }} agent{{ (creneaux.matin && creneaux.matin[heure] ? creneaux.matin[heure].agents_inscrits : agentsParCreneauMatin[heure].length) > 1 ? 's' : '' }}</span>
                <span class="stat-personnes">{{ creneaux.matin && creneaux.matin[heure] ? creneaux.matin[heure].personnes_total : getTotalPersonnesCreneau(agentsParCreneauMatin[heure]) }} pers.</span>
                <span class="stat-capacite" :class="getCapaciteClass(creneaux.matin && creneaux.matin[heure] ? creneaux.matin[heure].personnes_total : getTotalPersonnesCreneau(agentsParCreneauMatin[heure]))">
                  {{ creneaux.matin && creneaux.matin[heure] ? creneaux.matin[heure].personnes_total : getTotalPersonnesCreneau(agentsParCreneauMatin[heure]) }}/14
                </span>
              </div>
            </div>

            <!-- Liste agents du créneau (déroulable) -->
            <div v-if="creneauxOuverts[`matin-${heure}`]" class="creneau-agents">
              <div v-if="!agentsParCreneauMatin[heure] || agentsParCreneauMatin[heure].length === 0" class="aucun-agent">
                Aucun agent inscrit sur ce créneau
              </div>
              <div
                v-for="agent in agentsParCreneauMatin[heure]"
                :key="agent.code_personnel"
                class="agent-card-compact"
              >
                <div class="agent-info-compact">
                  <div class="agent-principal">
                    <span class="agent-code">CP: {{ agent.code_personnel }}</span>
                    <span class="agent-nom">Nom: {{ agent.nom }}</span>
                    <span class="agent-prenom">Prénom: {{ agent.prenom }}</span>
                    <span class="agent-proches">Accompagnants: {{ agent.nombre_proches }}</span>
                  </div>
                  <div class="agent-statut">
                    <span class="statut-badge-compact" :class="getStatutClass(agent.statut)">
                      {{ getStatutLabelShort(agent.statut) }}
                    </span>
                    <span v-if="agent.heure_validation" class="pointage-compact">
                      ✅ {{ formatHeure(agent.heure_validation) }}
                    </span>
                  </div>
                </div>
                <div class="agent-actions-compact">
                  <button @click="supprimerAgent(agent.code_personnel)" class="btn-supprimer-compact" :disabled="loading">
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenu onglet Après-midi -->
      <div v-if="ongletActif === 'apres-midi'" class="onglet-content">
        <div class="periode-header">
          <h3>🌆 Créneaux Après-midi (13h00 - 15h40)</h3>
          <p class="periode-summary">
            {{ agentsApresMidiFiltres.length }} agents • {{ personnesApresMidiFiltrees }} personnes
          </p>
        </div>

        <!-- Créneaux après-midi déroulables -->
        <div class="creneaux-deroulables">
          <div
            v-for="heure in creneauxApresMidi"
            :key="`apres-midi-${heure}`"
            class="creneau-section"
          >
            <div
              class="creneau-header"
              @click="toggleCreneau('apres-midi', heure)"
              :class="{
                'complet': getInfoCreneau('apres-midi', heure).complet,
                'limite': getInfoCreneau('apres-midi', heure).limite
              }"
            >
              <div class="creneau-info-principale">
                <span class="creneau-heure">{{ heure }}</span>
                <span class="creneau-chevron" :class="{ 'ouvert': creneauxOuverts[`apres-midi-${heure}`] }">
                  ▼
                </span>
              </div>
              <div class="creneau-stats">
                <span class="stat-agents">{{ creneaux['apres-midi'] && creneaux['apres-midi'][heure] ? creneaux['apres-midi'][heure].agents_inscrits : agentsParCreneauApresMidi[heure].length }} agent{{ (creneaux['apres-midi'] && creneaux['apres-midi'][heure] ? creneaux['apres-midi'][heure].agents_inscrits : agentsParCreneauApresMidi[heure].length) > 1 ? 's' : '' }}</span>
                <span class="stat-personnes">{{ creneaux['apres-midi'] && creneaux['apres-midi'][heure] ? creneaux['apres-midi'][heure].personnes_total : getTotalPersonnesCreneau(agentsParCreneauApresMidi[heure]) }} pers.</span>
                <span class="stat-capacite" :class="getCapaciteClass(creneaux['apres-midi'] && creneaux['apres-midi'][heure] ? creneaux['apres-midi'][heure].personnes_total : getTotalPersonnesCreneau(agentsParCreneauApresMidi[heure]))">
                  {{ creneaux['apres-midi'] && creneaux['apres-midi'][heure] ? creneaux['apres-midi'][heure].personnes_total : getTotalPersonnesCreneau(agentsParCreneauApresMidi[heure]) }}/14
                </span>
              </div>
            </div>

            <!-- Liste agents du créneau (déroulable) -->
            <div v-if="creneauxOuverts[`apres-midi-${heure}`]" class="creneau-agents">
              <div v-if="!agentsParCreneauApresMidi[heure] || agentsParCreneauApresMidi[heure].length === 0" class="aucun-agent">
                Aucun agent inscrit sur ce créneau
              </div>
              <div
                v-for="agent in agentsParCreneauApresMidi[heure]"
                :key="agent.code_personnel"
                class="agent-card-compact"
              >
                <div class="agent-info-compact">
                  <div class="agent-principal">
                    <span class="agent-code">CP: {{ agent.code_personnel }}</span>
                    <span class="agent-nom">Nom: {{ agent.nom }}</span>
                    <span class="agent-prenom">Prénom: {{ agent.prenom }}</span>
                    <span class="agent-proches">Accompagnants: {{ agent.nombre_proches }}</span>
                  </div>
                  <div class="agent-statut">
                    <span class="statut-badge-compact" :class="getStatutClass(agent.statut)">
                      {{ getStatutLabelShort(agent.statut) }}
                    </span>
                    <span v-if="agent.heure_validation" class="pointage-compact">
                      ✅ {{ formatHeure(agent.heure_validation) }}
                    </span>
                  </div>
                </div>
                <div class="agent-actions-compact">
                  <button @click="supprimerAgent(agent.code_personnel)" class="btn-supprimer-compact" :disabled="loading">
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Liste des agents (affichage de fallback si pas d'agents) -->
    <div v-if="!loading && agentsTries.length === 0" class="no-results">
      <div class="no-results-icon">📝</div>
      <h3>Aucune inscription enregistrée</h3>
      <p>Les inscriptions apparaîtront ici une fois saisies</p>
      <router-link to="/inscription" class="btn btn-primary">
        ➕ Ajouter le premier agent
      </router-link>
    </div>

    <!-- Modal disponibilités -->
    <div v-if="showDisponibilites" class="modal-overlay" @click="fermerDisponibilites">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>📈 Disponibilités des créneaux</h3>
          <button @click="fermerDisponibilites" class="modal-close">×</button>
        </div>
        <div class="modal-body">
          <div class="disponibilites-content">
            <!-- Matin -->
            <div class="periode-disponibilites">
              <h4>🌅 Matin (9h00 - 11h40)</h4>
              <div class="creneaux-table">
                <div class="table-header">
                  <span>Heure</span>
                  <span>Agents</span>
                  <span>Personnes</span>
                  <span>Places libres</span>
                  <span>Statut</span>
                </div>
                <div
                  v-for="(info, heure) in creneaux.matin"
                  :key="heure"
                  class="table-row"
                  :class="{ 'complet': info.complet, 'limite': info.places_restantes <= 3 }"
                >
                  <span class="cell-heure">{{ heure }}</span>
                  <span class="cell-agents">{{ info.agents_inscrits }}</span>
                  <span class="cell-personnes">{{ info.personnes_total }}</span>
                  <span class="cell-places">{{ info.places_restantes }}</span>
                  <span class="cell-statut">
                    <span v-if="info.complet" class="statut-complet">COMPLET</span>
                    <span v-else-if="info.places_restantes <= 3" class="statut-limite">LIMITE</span>
                    <span v-else class="statut-libre">LIBRE</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Après-midi -->
            <div class="periode-disponibilites">
              <h4>🌆 Après-midi (13h00 - 15h40)</h4>
              <div class="creneaux-table">
                <div class="table-header">
                  <span>Heure</span>
                  <span>Agents</span>
                  <span>Personnes</span>
                  <span>Places libres</span>
                  <span>Statut</span>
                </div>
                <div
                  v-for="(info, heure) in creneaux['apres-midi']"
                  :key="heure"
                  class="table-row"
                  :class="{ 'complet': info.complet, 'limite': info.places_restantes <= 3 }"
                >
                  <span class="cell-heure">{{ heure }}</span>
                  <span class="cell-agents">{{ info.agents_inscrits }}</span>
                  <span class="cell-personnes">{{ info.personnes_total }}</span>
                  <span class="cell-places">{{ info.places_restantes }}</span>
                  <span class="cell-statut">
                    <span v-if="info.complet" class="statut-complet">COMPLET</span>
                    <span v-else-if="info.places_restantes <= 3" class="statut-limite">LIMITE</span>
                    <span v-else class="statut-libre">LIBRE</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Informations de dernière mise à jour -->
    <div v-if="agentsTries.length > 0" class="footer-info">
      <p>
        <small>
          📊 {{ agentsTries.length }} agent{{ agentsTries.length > 1 ? 's' : '' }} inscrit{{ agentsTries.length > 1 ? 's' : '' }}
          ({{ totalPersonnes }} personnes au total)
          • Dernière actualisation : {{ derniereMiseAJour }}
        </small>
      </p>
    </div>
  </div>
</template>

<script>
import { useAgentsStore } from '@/stores/agents'
import { storeToRefs } from 'pinia'
import { onMounted, ref, computed } from 'vue'
import { RouterLink } from 'vue-router'

export default {
  name: 'GestionView',
  components: {
    RouterLink
  },
  setup() {
    const agentsStore = useAgentsStore()

    // État réactif du store
    const {
      agents,
      creneaux,
      totalInscriptions,
      totalProches,
      totalPersonnes,
      groupeMatin,
      groupeApresMidi,
      personnesMatin,
      personnesApresMidi,
      agentsTries,
      statistiquesStatuts,
      loading,
      error
    } = storeToRefs(agentsStore)

    // État local
    const derniereMiseAJour = ref('')
    const showDisponibilites = ref(false)
    const filtreStatut = ref('tous')
    const ongletActif = ref('matin')
    const creneauxOuverts = ref({})

    // Créneaux définis
    const creneauxMatin = ['09:00', '09:20', '09:40', '10:00', '10:20', '10:40', '11:00', '11:20', '11:40']
    const creneauxApresMidi = ['13:00', '13:20', '13:40', '14:00', '14:20', '14:40', '15:00', '15:20', '15:40']

    // Fonctions du store
    const {
      chargerAgents,
      exporterDonnees: exporterDonneesStore,
      supprimerAgent: supprimerAgentStore,
      chargerCreneaux
    } = agentsStore

    // Computed pour filtrer les agents
    const agentsFiltres = computed(() => {
      if (filtreStatut.value === 'tous') {
        // Exclure les agents avec statut 'annule' quand on affiche tous les agents
        return agentsTries.value.filter(agent => agent.statut !== 'annule')
      }
      return agentsTries.value.filter(agent => agent.statut === filtreStatut.value)
    })

    // Computed pour les agents par période
    const agentsMatinFiltres = computed(() => {
      return agentsFiltres.value.filter(agent =>
        agent.heure_arrivee >= '09:00' && agent.heure_arrivee <= '11:40'
      )
    })

    const agentsApresMidiFiltres = computed(() => {
      return agentsFiltres.value.filter(agent =>
        agent.heure_arrivee >= '13:00' && agent.heure_arrivee <= '15:40'
      )
    })

    // Computed pour grouper les agents par créneau - CORRIGÉ ET AMÉLIORÉ
    const agentsParCreneauMatin = computed(() => {
      console.log('🔄 Recalcul agentsParCreneauMatin')
      const groupes = {}
      // Initialiser tous les créneaux avec un tableau vide
      creneauxMatin.forEach(heure => {
        groupes[heure] = []
      })

      // Vérifier si agentsMatinFiltres est défini et non vide
      if (agentsMatinFiltres.value && agentsMatinFiltres.value.length > 0) {
        console.log(`👥 ${agentsMatinFiltres.value.length} agents matin à grouper`)

        // Remplir avec les agents filtrés
        agentsMatinFiltres.value.forEach(agent => {
          // Vérifier si l'agent a une heure d'arrivée valide
          if (agent && agent.heure_arrivee) {
            // Normaliser le format de l'heure (enlever les secondes si présentes)
            const heureNormalisee = agent.heure_arrivee.substring(0, 5);

            // Vérifier si ce créneau existe dans nos groupes
            if (groupes[heureNormalisee]) {
              groupes[heureNormalisee].push(agent);
              console.log(`➕ Agent ${agent.nom} ajouté au créneau ${heureNormalisee}`);
            } else {
              console.warn(`⚠️ Créneau non reconnu: ${heureNormalisee} pour agent ${agent.nom}`);
              // Trouver le créneau le plus proche
              const creneauProche = creneauxMatin.find(h => h.startsWith(heureNormalisee.substring(0, 2)));
              if (creneauProche) {
                console.log(`🔄 Utilisation du créneau proche ${creneauProche}`);
                groupes[creneauProche].push(agent);
              }
            }
          } else {
            console.warn('⚠️ Agent sans heure d\'arrivée valide:', agent);
          }
        });
      } else {
        console.log('⚠️ Aucun agent matin à grouper');
      }

      return groupes;
    });

    const agentsParCreneauApresMidi = computed(() => {
      console.log('🔄 Recalcul agentsParCreneauApresMidi');
      const groupes = {};
      // Initialiser tous les créneaux avec un tableau vide
      creneauxApresMidi.forEach(heure => {
        groupes[heure] = [];
      });

      // Vérifier si agentsApresMidiFiltres est défini et non vide
      if (agentsApresMidiFiltres.value && agentsApresMidiFiltres.value.length > 0) {
        console.log(`👥 ${agentsApresMidiFiltres.value.length} agents après-midi à grouper`);

        // Remplir avec les agents filtrés
        agentsApresMidiFiltres.value.forEach(agent => {
          // Vérifier si l'agent a une heure d'arrivée valide
          if (agent && agent.heure_arrivee) {
            // Normaliser le format de l'heure (enlever les secondes si présentes)
            const heureNormalisee = agent.heure_arrivee.substring(0, 5);

            // Vérifier si ce créneau existe dans nos groupes
            if (groupes[heureNormalisee]) {
              groupes[heureNormalisee].push(agent);
              console.log(`➕ Agent ${agent.nom} ajouté au créneau ${heureNormalisee}`);
            } else {
              console.warn(`⚠️ Créneau non reconnu: ${heureNormalisee} pour agent ${agent.nom}`);
              // Trouver le créneau le plus proche
              const creneauProche = creneauxApresMidi.find(h => h.startsWith(heureNormalisee.substring(0, 2)));
              if (creneauProche) {
                console.log(`🔄 Utilisation du créneau proche ${creneauProche}`);
                groupes[creneauProche].push(agent);
              }
            }
          } else {
            console.warn('⚠️ Agent sans heure d\'arrivée valide:', agent);
          }
        });
      } else {
        console.log('⚠️ Aucun agent après-midi à grouper');
      }

      return groupes;
    });

    // Computed pour le nombre de personnes par période
    const personnesMatinFiltrees = computed(() => {
      return agentsMatinFiltres.value.reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0) + 1, 0)
    })

    const personnesApresMidiFiltrees = computed(() => {
      return agentsApresMidiFiltres.value.reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0) + 1, 0)
    })

    // Fonctions utilitaires
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

    function formatHeure(dateString) {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleTimeString('fr-FR', {
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (error) {
        return dateString
      }
    }

    function formatDateForTimestamp() {
      const now = new Date()
      return now.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
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

    function getStatutLabelShort(statut) {
      const labels = {
        'inscrit': 'Inscrit',
        'present': 'Présent',
        'absent': 'Absent',
        'annule': 'Annulé'
      }
      return labels[statut] || 'Inscrit'
    }

    function getTotalPersonnesCreneau(agentsCreneau) {
      if (!agentsCreneau || !Array.isArray(agentsCreneau)) return 0
      return agentsCreneau.reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0) + 1, 0)
    }

    function getCapaciteClass(nombrePersonnes) {
      if (nombrePersonnes >= 14) return 'capacite-complet'
      if (nombrePersonnes >= 10) return 'capacite-limite'
      return 'capacite-libre'
    }

    function getInfoCreneau(periode, heure) {
      // Utiliser les données de créneaux du store si disponibles
      if (creneaux.value && creneaux.value[periode] && creneaux.value[periode][heure]) {
        const infoCreneau = creneaux.value[periode][heure];
        return {
          complet: infoCreneau.complet,
          limite: infoCreneau.places_restantes <= 3 && infoCreneau.places_restantes > 0,
          personnes: infoCreneau.personnes_total
        };
      } else {
        // Fallback sur la méthode de calcul précédente
        const agentsCreneau = periode === 'matin' ?
          agentsParCreneauMatin.value[heure] :
          agentsParCreneauApresMidi.value[heure];

        const personnes = getTotalPersonnesCreneau(agentsCreneau);
        return {
          complet: personnes >= 14,
          limite: personnes >= 10 && personnes < 14,
          personnes
        };
      }
    }

    function toggleCreneau(periode, heure) {
      const key = `${periode}-${heure}`
      creneauxOuverts.value[key] = !creneauxOuverts.value[key]
    }

    // Actions
    async function chargerAgentsAvecTimestamp() {
      try {
        await chargerAgents()
        await chargerCreneaux()
        derniereMiseAJour.value = formatDateForTimestamp()
      } catch (error) {
        console.error('Erreur chargement:', error)
      }
    }

    async function rafraichirDonnees() {
      await chargerAgentsAvecTimestamp()
    }

    function exporterDonnees() {
      try {
        exporterDonneesStore()
      } catch (error) {
        alert(error.message)
      }
    }

    function voirDisponibilites() {
      showDisponibilites.value = true
    }

    function fermerDisponibilites() {
      showDisponibilites.value = false
    }

    async function supprimerAgent(codePersonnel) {
      const agent = agentsTries.value.find(a => a.code_personnel === codePersonnel)
      const nomComplet = agent ? `${agent.prenom} ${agent.nom}` : codePersonnel

      if (confirm(`Êtes-vous sûr de vouloir supprimer l'agent ${nomComplet} ?\n\nCette action est irréversible.`)) {
        try {
          await supprimerAgentStore(codePersonnel)
          derniereMiseAJour.value = formatDateForTimestamp()
        } catch (error) {
          alert(`Erreur lors de la suppression : ${error.message}`)
        }
      }
    }

    async function effacerTout() {
      if (confirm('⚠️ ATTENTION: Cette action supprimera définitivement toutes les inscriptions.\n\nVoulez-vous continuer ?')) {
        if (confirm('🚨 CONFIRMATION FINALE 🚨\n\nCette action est IRRÉVERSIBLE et supprimera TOUTES les données.\n\nÊtes-vous absolument certain de vouloir continuer ?')) {
          try {
            const agentsASupprimer = [...agentsTries.value]

            for (const agent of agentsASupprimer) {
              await supprimerAgentStore(agent.code_personnel)
            }

            derniereMiseAJour.value = formatDateForTimestamp()
            alert('✅ Toutes les inscriptions ont été supprimées')
          } catch (error) {
            alert(`Erreur lors de la suppression : ${error.message}`)
          }
        }
      }
    }

    // Charger les données au montage du composant
    onMounted(() => {
      console.log('🔄 Montage du composant GestionView')
      chargerAgentsAvecTimestamp().then(() => {
        console.log('📊 Données après chargement:')
        console.log('- agents:', agents.value.length)
        console.log('- agentsTries:', agentsTries.value.length)
        console.log('- agentsFiltres:', agentsFiltres.value.length)
        console.log('- agentsMatinFiltres:', agentsMatinFiltres.value.length)
        console.log('- agentsApresMidiFiltres:', agentsApresMidiFiltres.value.length)

        // Vérifier les créneaux
        console.log('📅 Créneaux:')
        console.log('- matin:', Object.keys(creneaux.value.matin || {}).length)
        console.log('- après-midi:', Object.keys(creneaux.value['apres-midi'] || {}).length)

        // Vérifier les agents par créneau
        console.log('👥 Agents par créneau:')
        let totalAgentsParCreneauMatin = 0
        Object.values(agentsParCreneauMatin.value).forEach(agents => {
          totalAgentsParCreneauMatin += agents.length
        })
        console.log('- totalAgentsParCreneauMatin:', totalAgentsParCreneauMatin)

        let totalAgentsParCreneauApresMidi = 0
        Object.values(agentsParCreneauApresMidi.value).forEach(agents => {
          totalAgentsParCreneauApresMidi += agents.length
        })
        console.log('- totalAgentsParCreneauApresMidi:', totalAgentsParCreneauApresMidi)
      })
    })

    return {
      // État du store
      agents,
      creneaux,
      totalInscriptions,
      totalProches,
      totalPersonnes,
      groupeMatin,
      groupeApresMidi,
      personnesMatin,
      personnesApresMidi,
      agentsTries,
      statistiquesStatuts,
      loading,
      error,
      // État local
      derniereMiseAJour,
      showDisponibilites,
      filtreStatut,
      ongletActif,
      creneauxOuverts,
      creneauxMatin,
      creneauxApresMidi,
      // Computed
      agentsFiltres,
      agentsMatinFiltres,
      agentsApresMidiFiltres,
      agentsParCreneauMatin,
      agentsParCreneauApresMidi,
      personnesMatinFiltrees,
      personnesApresMidiFiltrees,
      // Fonctions
      formatDate,
      formatHeure,
      getStatutClass,
      getStatutLabelShort,
      getTotalPersonnesCreneau,
      getCapaciteClass,
      getInfoCreneau,
      toggleCreneau,
      chargerAgents,
      rafraichirDonnees,
      exporterDonnees,
      voirDisponibilites,
      fermerDisponibilites,
      supprimerAgent,
      effacerTout
    }
  }
}
</script>
<style scoped>
.gestion-view {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.gestion-view h2 {
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

.loading-indicator {
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

.alert {
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-error {
  background: linear-gradient(135deg, #f8d7da, #f5c6cb);
  color: #721c24;
  border-left: 5px solid #dc3545;
}

.btn-retry {
  background: #dc3545;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9em;
  margin-left: 15px;
}

.btn-retry:hover {
  background: #c82333;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  padding: 25px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 10px 20px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
  border-top: 4px solid;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-card.primary {
  background: linear-gradient(135deg, #e3f2fd, #bbdefb);
  border-top-color: #2196f3;
}

.stat-card.success {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  border-top-color: #4caf50;
}

.stat-card.info {
  background: linear-gradient(135deg, #e1f5fe, #b3e5fc);
  border-top-color: #03a9f4;
}

.stat-card.warning {
  background: linear-gradient(135deg, #fff8e1, #ffecb3);
  border-top-color: #ff9800;
}

.stat-card.danger {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  border-top-color: #f44336;
}

.stat-card.highlight {
  background: linear-gradient(135deg, #f3e5f5, #e1bee7);
  border-top-color: #9c27b0;
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
  margin-bottom: 5px;
}

.stat-detail {
  font-size: 0.9em;
  color: #6c757d;
  font-style: italic;
}

.repartition-section {
  margin: 40px 0;
  padding: 30px;
  background: #f8f9fa;
  border-radius: 15px;
}

.repartition-section h3 {
  margin-bottom: 25px;
  color: #2c3e50;
  text-align: center;
}

.creneaux-overview {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

.periode-overview h4 {
  margin-bottom: 20px;
  color: #2c3e50;
  font-size: 1.2em;
}

.creneaux-bars {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.creneau-bar {
  background: white;
  padding: 12px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.creneau-bar.complet {
  background: #ffebee;
  border-left: 4px solid #f44336;
}

.creneau-bar.limite {
  background: #fff3e0;
  border-left: 4px solid #ff9800;
}

.bar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.bar-heure {
  font-weight: 600;
  color: #2c3e50;
}

.bar-count {
  font-size: 0.9em;
  color: #6c757d;
}

.bar-container {
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #28a745, #20c997);
  transition: width 0.3s ease;
}

.creneau-bar.limite .bar-fill {
  background: linear-gradient(90deg, #ffc107, #ff8f00);
}

.creneau-bar.complet .bar-fill {
  background: linear-gradient(90deg, #dc3545, #c82333);
}

.bar-details {
  font-size: 0.85em;
  color: #6c757d;
}

.actions {
  text-align: center;
  margin-bottom: 30px;
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  padding: 15px 30px;
  border-radius: 10px;
  font-size: 1.1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
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

.btn-success {
  background: linear-gradient(135deg, #28a745, #218838);
}

.btn-danger {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
}

.btn-small {
  padding: 10px 20px;
  font-size: 0.9em;
}

/* Filtres par statut */
.filtres-statut {
  margin: 30px 0;
  padding: 25px;
  background: #f8f9fa;
  border-radius: 15px;
  border: 1px solid #e9ecef;
}

.filtres-statut h3 {
  margin-bottom: 15px;
  color: #2c3e50;
  text-align: center;
}

.boutons-filtres {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-filtre {
  padding: 10px 20px;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  color: #6c757d;
}

.btn-filtre:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-filtre.active {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-filtre-inscrit.active {
  background: #e3f2fd;
  border-color: #2196f3;
  color: #1565c0;
}

.btn-filtre-present.active {
  background: #e8f5e8;
  border-color: #4caf50;
  color: #2e7d32;
}

.btn-filtre-absent.active {
  background: #ffebee;
  border-color: #f44336;
  color: #c62828;
}

.btn-filtre-annule.active {
  background: #f3e5f5;
  border-color: #9c27b0;
  color: #7b1fa2;
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

.agents-count {
  text-align: center;
  margin-bottom: 20px;
  color: #6c757d;
  font-style: italic;
}

.agents-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.agent-card {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  padding: 25px;
  border-radius: 15px;
  border-left: 5px solid #3498db;
  transition: all 0.3s ease;
}

.agent-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.agent-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
  color: #e67e22;
}

.pointage-heure {
  color: #28a745 !important;
  font-family: monospace;
  background: rgba(40, 167, 69, 0.1);
  padding: 3px 6px;
  border-radius: 4px;
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
  padding: 6px 12px;
  border-radius: 15px;
  font-size: 0.9em;
  font-weight: 600;
  margin-top: 5px;
  width: fit-content;
}

.periode-matin {
  background: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
}

.periode-apres-midi {
  background: #e2e3f3;
  color: #5a5c96;
  border: 1px solid #b3b5d1;
}

/* Styles pour les statuts */
.statut-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 15px;
  font-size: 0.85em;
  font-weight: 600;
  margin-top: 5px;
  width: fit-content;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.statut-inscrit {
  background: #e3f2fd;
  color: #1565c0;
  border: 1px solid #2196f3;
}

.statut-present {
  background: #e8f5e8;
  color: #2e7d32;
  border: 1px solid #4caf50;
}

.statut-absent {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #f44336;
}

.statut-annule {
  background: #f3e5f5;
  color: #7b1fa2;
  border: 1px solid #9c27b0;
}

.agent-actions {
  text-align: right;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 15px;
  max-width: 800px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.modal-header {
  padding: 20px 30px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2em;
  cursor: pointer;
  color: #6c757d;
}

.modal-body {
  padding: 30px;
}

.periode-disponibilites {
  margin-bottom: 30px;
}

.periode-disponibilites h4 {
  margin-bottom: 15px;
  color: #2c3e50;
}

.creneaux-table {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.table-header {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1.2fr;
  gap: 10px;
  background: #2c3e50;
  color: white;
  padding: 15px;
  font-weight: 600;
  font-size: 0.9em;
  text-transform: uppercase;
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1.2fr;
  gap: 10px;
  padding: 12px 15px;
  border-bottom: 1px solid #e9ecef;
  transition: background 0.3s ease;
}

.table-row:hover {
  background: #f8f9fa;
}

.table-row.complet {
  background: #ffebee;
}

.table-row.limite {
  background: #fff3e0;
}

.cell-heure {
  font-weight: 600;
  font-family: monospace;
}

.statut-complet {
  color: #d32f2f;
  font-weight: 600;
}

.statut-limite {
  color: #f57c00;
  font-weight: 600;
}

.statut-libre {
  color: #388e3c;
  font-weight: 600;
}

.footer-info {
  text-align: center;
  padding: 20px;
  border-top: 1px solid #e9ecef;
  margin-top: 30px;
  color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .creneaux-overview {
    grid-template-columns: 1fr;
  }

  .actions {
    flex-direction: column;
    align-items: center;
  }

  .btn {
    width: 100%;
    max-width: 300px;
  }

  .boutons-filtres {
    flex-direction: column;
    align-items: center;
  }

  .btn-filtre {
    width: 100%;
    max-width: 200px;
  }

  .agent-info {
    grid-template-columns: 1fr;
  }

  .modal-content {
    width: 95%;
    margin: 20px;
  }

  .table-header, .table-row {
    grid-template-columns: 1fr 0.5fr 0.5fr 0.5fr 0.8fr;
    font-size: 0.85em;
  }
}

@media (max-width: 480px) {
  .gestion-view {
    padding: 15px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .repartition-section {
    padding: 20px;
  }

  .filtres-statut {
    padding: 20px;
  }

  .agent-card {
    padding: 20px;
  }

  .modal-body {
    padding: 20px;
  }

  .actions {
    gap: 10px;
  }

  .btn {
    font-size: 1em;
    padding: 12px 20px;
  }
}

/* Animations et transitions */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.agent-card {
  animation: fadeIn 0.3s ease-out;
}

.stat-card {
  animation: fadeIn 0.5s ease-out;
}

/* Amélioration des contrastes pour l'accessibilité */
.info-label {
  font-weight: 600;
}

.bar-heure {
  font-size: 1em;
}

.bar-count {
  font-weight: 500;
}

/* États hover améliorés */
.agent-card:hover .agent-actions .btn {
  opacity: 1;
}

.agent-actions .btn {
  opacity: 0.8;
  transition: opacity 0.3s ease;
}

/* Indicateurs visuels pour les actions */
.btn-danger:hover {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}

/* Styles pour les tooltips sur les statuts */
.statut-badge[title] {
  cursor: help;
}

.statut-badge:hover {
  transform: scale(1.05);
  transition: transform 0.2s ease;
}

/* Focus states pour l'accessibilité */
.btn:focus {
  outline: 2px solid #3498db;
  outline-offset: 2px;
}

.btn-filtre:focus {
  outline: 2px solid #3498db;
  outline-offset: 2px;
}

/* Amélioration visuelle des badges de période */
.periode-badge {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge {
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Style pour les agents avec pointage récent */
.agent-card:has(.pointage-heure) {
  border-left-color: #28a745;
  background: linear-gradient(135deg, #f8fff8, #e8f5e8);
}

/* Indicateur de dernière modification */
.agent-card[data-recent="true"] {
  position: relative;
}

.agent-card[data-recent="true"]::before {
  content: "🆕";
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 1.2em;
}
/* Styles existants conservés */
.gestion-view {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.gestion-view h2 {
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

.loading-indicator {
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

.alert {
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-error {
  background: linear-gradient(135deg, #f8d7da, #f5c6cb);
  color: #721c24;
  border-left: 5px solid #dc3545;
}

.btn-retry {
  background: #dc3545;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9em;
  margin-left: 15px;
}

.btn-retry:hover {
  background: #c82333;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  padding: 25px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 10px 20px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
  border-top: 4px solid;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-card.primary {
  background: linear-gradient(135deg, #e3f2fd, #bbdefb);
  border-top-color: #2196f3;
}

.stat-card.success {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  border-top-color: #4caf50;
}

.stat-card.info {
  background: linear-gradient(135deg, #e1f5fe, #b3e5fc);
  border-top-color: #03a9f4;
}

.stat-card.warning {
  background: linear-gradient(135deg, #fff8e1, #ffecb3);
  border-top-color: #ff9800;
}

.stat-card.danger {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  border-top-color: #f44336;
}

.stat-card.highlight {
  background: linear-gradient(135deg, #f3e5f5, #e1bee7);
  border-top-color: #9c27b0;
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
  margin-bottom: 5px;
}

.stat-detail {
  font-size: 0.9em;
  color: #6c757d;
  font-style: italic;
}

.actions {
  text-align: center;
  margin-bottom: 30px;
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  padding: 15px 30px;
  border-radius: 10px;
  font-size: 1.1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
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

.btn-danger {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
}

/* Filtres par statut */
.filtres-statut {
  margin: 30px 0;
  padding: 25px;
  background: #f8f9fa;
  border-radius: 15px;
  border: 1px solid #e9ecef;
}

.filtres-statut h3 {
  margin-bottom: 15px;
  color: #2c3e50;
  text-align: center;
}

.boutons-filtres {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-filtre {
  padding: 10px 20px;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  color: #6c757d;
}

.btn-filtre:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-filtre.active {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-filtre-inscrit.active {
  background: #e3f2fd;
  border-color: #2196f3;
  color: #1565c0;
}

.btn-filtre-present.active {
  background: #e8f5e8;
  border-color: #4caf50;
  color: #2e7d32;
}

.btn-filtre-absent.active {
  background: #ffebee;
  border-color: #f44336;
  color: #c62828;
}

.btn-filtre-annule.active {
  background: #f3e5f5;
  border-color: #9c27b0;
  color: #7b1fa2;
}

/* NOUVEAUX STYLES POUR LES ONGLETS */

/* Container des onglets */
.onglets-container {
  margin: 30px 0;
  background: white;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  overflow: hidden;
}

/* Header des onglets */
.onglets-header {
  display: flex;
  background: #f8f9fa;
  border-bottom: 2px solid #e9ecef;
}

.onglet-btn {
  flex: 1;
  padding: 20px 30px;
  background: none;
  border: none;
  font-size: 1.2em;
  font-weight: 600;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.onglet-btn:hover {
  background: #e9ecef;
  color: #2c3e50;
}

.onglet-btn.active {
  background: white;
  color: #2c3e50;
  border-bottom: 3px solid #3498db;
}

.onglet-btn.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #3498db, #2980b9);
}

/* Contenu des onglets */
.onglet-content {
  padding: 30px;
}

.periode-header {
  text-align: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e9ecef;
}

.periode-header h3 {
  color: #2c3e50;
  font-size: 1.8em;
  margin-bottom: 10px;
}

.periode-summary {
  color: #6c757d;
  font-size: 1.1em;
  font-weight: 500;
}

/* Créneaux déroulables */
.creneaux-deroulables {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.creneau-section {
  background: #f8f9fa;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.creneau-section:hover {
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Header du créneau (cliquable) */
.creneau-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 25px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
  border-bottom: 1px solid transparent;
}

.creneau-header:hover {
  background: #f8f9fa;
}

.creneau-header.complet {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  border-left: 5px solid #f44336;
}

.creneau-header.limite {
  background: linear-gradient(135deg, #fff3e0, #ffecb3);
  border-left: 5px solid #ff9800;
}

.creneau-info-principale {
  display: flex;
  align-items: center;
  gap: 15px;
}

.creneau-heure {
  font-size: 1.5em;
  font-weight: 700;
  color: #2c3e50;
  font-family: monospace;
  background: rgba(52, 152, 219, 0.1);
  padding: 8px 16px;
  border-radius: 8px;
}

.creneau-chevron {
  font-size: 1.2em;
  color: #6c757d;
  transition: transform 0.3s ease;
}

.creneau-chevron.ouvert {
  transform: rotate(180deg);
}

.creneau-stats {
  display: flex;
  align-items: center;
  gap: 20px;
}

.stat-agents, .stat-personnes {
  font-size: 1em;
  color: #6c757d;
  font-weight: 500;
}

.stat-capacite {
  font-size: 1.2em;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 20px;
  font-family: monospace;
}

.capacite-libre {
  background: #e8f5e8;
  color: #2e7d32;
  border: 2px solid #4caf50;
}

.capacite-limite {
  background: #fff3e0;
  color: #f57c00;
  border: 2px solid #ff9800;
}

.capacite-complet {
  background: #ffebee;
  color: #d32f2f;
  border: 2px solid #f44336;
}

/* Liste des agents du créneau */
.creneau-agents {
  padding: 0 25px 25px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
}

.aucun-agent {
  text-align: center;
  padding: 30px;
  color: #6c757d;
  font-style: italic;
  background: white;
  border-radius: 8px;
  border: 2px dashed #e9ecef;
}

/* Cartes agents compactes */
.agent-card-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 15px 20px;
  margin-bottom: 10px;
  border-radius: 10px;
  border-left: 4px solid #3498db;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.agent-card-compact:hover {
  transform: translateX(5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.agent-card-compact:last-child {
  margin-bottom: 0;
}

.agent-info-compact {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.agent-principal {
  display: flex;
  align-items: center;
  gap: 15px;
}

.agent-code {
  font-family: monospace;
  font-weight: 700;
  color: #3498db;
  background: rgba(52, 152, 219, 0.1);
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.agent-nom {
  font-weight: 600;
  color: #2c3e50;
  font-size: 1.1em;
}

.agent-prenom {
  font-weight: 600;
  color: #2c3e50;
  font-size: 1.1em;
}

.agent-proches {
  font-weight: 600;
  color: #e67e22;
  background: rgba(230, 126, 34, 0.1);
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.agent-personnes {
  font-weight: 600;
  color: #e67e22;
  background: rgba(230, 126, 34, 0.1);
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.agent-statut {
  display: flex;
  align-items: center;
  gap: 10px;
}

.statut-badge-compact {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8em;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.statut-inscrit {
  background: #e3f2fd;
  color: #1565c0;
  border: 1px solid #2196f3;
}

.statut-present {
  background: #e8f5e8;
  color: #2e7d32;
  border: 1px solid #4caf50;
}

.statut-absent {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #f44336;
}

.statut-annule {
  background: #f3e5f5;
  color: #7b1fa2;
  border: 1px solid #9c27b0;
}

.pointage-compact {
  font-size: 0.8em;
  color: #28a745;
  font-weight: 600;
  background: rgba(40, 167, 69, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
}

.agent-actions-compact {
  display: flex;
  gap: 8px;
}

.btn-supprimer-compact {
  background: #dc3545;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9em;
}

.btn-supprimer-compact:hover:not(:disabled) {
  background: #c82333;
  transform: scale(1.1);
}

.btn-supprimer-compact:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Styles pour les agents avec pointage */
.agent-card-compact:has(.pointage-compact) {
  border-left-color: #28a745;
  background: linear-gradient(135deg, #f8fff8, #ffffff);
}

/* Affichage sans résultats */
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

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 15px;
  max-width: 800px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.modal-header {
  padding: 20px 30px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2em;
  cursor: pointer;
  color: #6c757d;
}

.modal-body {
  padding: 30px;
}

.periode-disponibilites {
  margin-bottom: 30px;
}

.periode-disponibilites h4 {
  margin-bottom: 15px;
  color: #2c3e50;
}

.creneaux-table {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.table-header {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1.2fr;
  gap: 10px;
  background: #2c3e50;
  color: white;
  padding: 15px;
  font-weight: 600;
  font-size: 0.9em;
  text-transform: uppercase;
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1.2fr;
  gap: 10px;
  padding: 12px 15px;
  border-bottom: 1px solid #e9ecef;
  transition: background 0.3s ease;
}

.table-row:hover {
  background: #f8f9fa;
}

.table-row.complet {
  background: #ffebee;
}

.table-row.limite {
  background: #fff3e0;
}

.cell-heure {
  font-weight: 600;
  font-family: monospace;
}

.statut-complet {
  color: #d32f2f;
  font-weight: 600;
}

.statut-limite {
  color: #f57c00;
  font-weight: 600;
}

.statut-libre {
  color: #388e3c;
  font-weight: 600;
}

.footer-info {
  text-align: center;
  padding: 20px;
  border-top: 1px solid #e9ecef;
  margin-top: 30px;
  color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .actions {
    flex-direction: column;
    align-items: center;
  }

  .btn {
    width: 100%;
    max-width: 300px;
  }

  .boutons-filtres {
    flex-direction: column;
    align-items: center;
  }

  .btn-filtre {
    width: 100%;
    max-width: 200px;
  }

  .onglets-header {
    flex-direction: column;
  }

  .onglet-btn {
    border-bottom: 1px solid #e9ecef;
  }

  .onglet-btn.active {
    border-bottom: 3px solid #3498db;
  }

  .creneau-header {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }

  .creneau-stats {
    align-self: stretch;
    justify-content: space-between;
  }

  .agent-principal {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .agent-statut {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }

  .table-header, .table-row {
    grid-template-columns: 1fr 0.5fr 0.5fr 0.5fr 0.8fr;
    font-size: 0.85em;
  }
}

@media (max-width: 480px) {
  .gestion-view {
    padding: 15px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .onglet-content {
    padding: 20px;
  }

  .creneau-section {
    margin-bottom: 10px;
  }

  .creneau-agents {
    padding: 0 15px 15px;
  }

  .agent-card-compact {
    padding: 12px 15px;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .agent-actions-compact {
    align-self: flex-end;
  }

  .modal-body {
    padding: 20px;
  }
}

/* Animations */
@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    max-height: 1000px;
    transform: translateY(0);
  }
}

.creneau-agents {
  animation: slideDown 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.agent-card-compact {
  animation: fadeIn 0.2s ease-out;
}

.stat-card {
  animation: fadeIn 0.5s ease-out;
}

/* États hover améliorés */
.creneau-header:hover .creneau-chevron {
  color: #3498db;
  transform: scale(1.1);
}

.creneau-header.ouvert:hover .creneau-chevron {
  transform: scale(1.1) rotate(180deg);
}

/* Focus states pour l'accessibilité */
.btn:focus, .onglet-btn:focus, .creneau-header:focus {
  outline: 2px solid #3498db;
  outline-offset: 2px;
}

.btn-filtre:focus {
  outline: 2px solid #3498db;
  outline-offset: 2px;
}
</style>
