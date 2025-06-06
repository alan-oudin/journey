// stores/agents.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAgentsStore = defineStore('agents', () => {
  // État
  const agents = ref([])
  const creneaux = ref({
    matin: {},
    'apres-midi': {}
  })
  const loading = ref(false)
  const error = ref('')

  // Configuration de l'API - CORRECTION: URL complète vers votre API
  const API_BASE = 'http://localhost:8080/journee-proches/public/api.php'

  // Fonction utilitaire pour parser les réponses API
  async function parseApiResponse(response) {
    const contentType = response.headers.get('content-type')

    if (!response.ok) {
      let errorMessage = `HTTP ${response.status}`
      try {
        if (contentType && contentType.includes('application/json')) {
          const errorData = await response.json()
          errorMessage = errorData.error || errorData.message || errorMessage
        } else {
          const textData = await response.text()
          errorMessage = textData || errorMessage
        }
      } catch (parseError) {
        console.warn('Impossible de parser l\'erreur:', parseError)
      }
      throw new Error(errorMessage)
    }

    try {
      if (contentType && contentType.includes('application/json')) {
        return await response.json()
      } else {
        const textData = await response.text()
        if (textData.trim().startsWith('{') || textData.trim().startsWith('[')) {
          return JSON.parse(textData)
        }
        throw new Error('Réponse non-JSON reçue')
      }
    } catch (parseError) {
      console.error('Erreur parsing réponse:', parseError)
      throw new Error(`Réponse serveur invalide: ${parseError.message}`)
    }
  }

  // Getters (computed)
  const agentsTries = computed(() => {
    return [...agents.value].sort((a, b) => {
      if (a.nom !== b.nom) return a.nom.localeCompare(b.nom)
      return a.prenom.localeCompare(b.prenom)
    })
  })

  const totalInscriptions = computed(() => agents.value.length)

  const totalProches = computed(() => {
    return agents.value.reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0), 0)
  })

  const totalPersonnes = computed(() => {
    return agents.value.reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0) + 1, 0)
  })

  const groupeMatin = computed(() => {
    return agents.value.filter(agent =>
      agent.heure_arrivee >= '09:00' && agent.heure_arrivee <= '11:40'
    ).length
  })

  const groupeApresMidi = computed(() => {
    return agents.value.filter(agent =>
      agent.heure_arrivee >= '13:00' && agent.heure_arrivee <= '15:40'
    ).length
  })

  const personnesMatin = computed(() => {
    return agents.value
      .filter(agent => agent.heure_arrivee >= '09:00' && agent.heure_arrivee <= '11:40')
      .reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0) + 1, 0)
  })

  const personnesApresMidi = computed(() => {
    return agents.value
      .filter(agent => agent.heure_arrivee >= '13:00' && agent.heure_arrivee <= '15:40')
      .reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0) + 1, 0)
  })

  // Fonctions utilitaires
  function generateDefaultCreneaux(heures) {
    const creneauxDefault = {}

    heures.forEach(heure => {
      const agentsCreneau = agents.value.filter(agent => agent.heure_arrivee === heure)
      const personnesTotal = agentsCreneau.reduce((sum, agent) => sum + parseInt(agent.nombre_proches || 0) + 1, 0)

      creneauxDefault[heure] = {
        agents_inscrits: agentsCreneau.length,
        personnes_total: personnesTotal,
        places_restantes: Math.max(0, 14 - personnesTotal),
        complet: personnesTotal >= 14
      }
    })

    return creneauxDefault
  }

  // Actions
  async function testConnexion() {
    try {
      console.log('Test de connexion API...')
      const response = await fetch(`${API_BASE}?path=test`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      const data = await parseApiResponse(response)
      console.log('Test connexion réussi:', data)
      return data
    } catch (err) {
      console.error('Erreur test connexion:', err)
      throw new Error(`Impossible de se connecter à l'API: ${err.message}`)
    }
  }

  async function chargerAgents() {
    loading.value = true
    error.value = ''

    try {
      console.log('Chargement des agents...')
      const response = await fetch(`${API_BASE}?path=agents`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      const data = await parseApiResponse(response)
      console.log('Données agents reçues:', data)

      // Adapter selon le format de votre API
      if (Array.isArray(data)) {
        agents.value = data
      } else if (data.agents && Array.isArray(data.agents)) {
        agents.value = data.agents
      } else if (data.data && Array.isArray(data.data)) {
        agents.value = data.data
      } else {
        agents.value = []
      }

      console.log(`${agents.value.length} agents chargés`)

    } catch (err) {
      console.error('Erreur chargement agents:', err)
      error.value = err.message
      agents.value = []
    } finally {
      loading.value = false
    }
  }

  async function chargerCreneaux() {
    try {
      console.log('Chargement des créneaux...')
      const response = await fetch(`${API_BASE}?path=creneaux`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      const data = await parseApiResponse(response)
      console.log('Données créneaux reçues:', data)

      // Adapter selon le format de votre API
      if (data.creneaux) {
        creneaux.value = data.creneaux
      } else if (data.matin && data['apres-midi']) {
        creneaux.value = {
          matin: data.matin,
          'apres-midi': data['apres-midi']
        }
      } else {
        // Format par défaut basé sur les agents actuels
        creneaux.value = {
          matin: generateDefaultCreneaux(['09:00', '09:20', '09:40', '10:00', '10:20', '10:40', '11:00', '11:20', '11:40']),
          'apres-midi': generateDefaultCreneaux(['13:00', '13:20', '13:40', '14:00', '14:20', '14:40', '15:00', '15:20', '15:40'])
        }
      }

    } catch (err) {
      console.error('Erreur chargement créneaux:', err)
      // Créneaux par défaut en cas d'erreur
      console.log('Génération des créneaux par défaut après erreur')
      creneaux.value = {
        matin: generateDefaultCreneaux(['09:00', '09:20', '09:40', '10:00', '10:20', '10:40', '11:00', '11:20', '11:40']),
        'apres-midi': generateDefaultCreneaux(['13:00', '13:20', '13:40', '14:00', '14:20', '14:40', '15:00', '15:20', '15:40'])
      }
    }
  }

  async function ajouterAgent(agent) {
    loading.value = true
    error.value = ''

    try {
      // Validation côté client
      if (!agent.codePersonnel || !agent.nom || !agent.prenom || !agent.service ||
        agent.nombreProches === undefined || !agent.heureArrivee) {
        throw new Error('Tous les champs obligatoires doivent être remplis')
      }

      if (agent.nombreProches < 0 || agent.nombreProches > 4) {
        throw new Error('Le nombre de proches doit être entre 0 et 4')
      }

      // Vérifier si l'agent existe déjà
      const existant = agents.value.find(a => a.code_personnel === agent.codePersonnel)
      if (existant) {
        throw new Error(`L'agent avec le code ${agent.codePersonnel} est déjà inscrit`)
      }

      // Adapter les données pour l'API
      const agentData = {
        code_personnel: agent.codePersonnel,
        nom: agent.nom,
        prenom: agent.prenom,
        service: agent.service,
        nombre_proches: agent.nombreProches,
        heure_arrivee: agent.heureArrivee
      }

      console.log('Envoi agent à l\'API:', agentData)

      const response = await fetch(`${API_BASE}?path=agents`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(agentData)
      })

      const result = await parseApiResponse(response)
      console.log('Réponse ajout agent:', result)

      // Recharger les agents pour avoir les données à jour
      await chargerAgents()
      await chargerCreneaux()

    } catch (err) {
      console.error('Erreur ajout agent:', err)
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function rechercherAgent(codePersonnel) {
    loading.value = true
    error.value = ''

    try {
      console.log('Recherche agent:', codePersonnel)
      const response = await fetch(`${API_BASE}?path=search&q=${encodeURIComponent(codePersonnel)}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok && response.status === 404) {
        return null // Agent non trouvé
      }

      const data = await parseApiResponse(response)
      console.log('Résultat recherche:', data)

      // Adapter selon le format de votre API
      if (data.agent) {
        return data.agent
      } else if (Array.isArray(data) && data.length > 0) {
        return data[0]
      } else if (data.code_personnel) {
        return data
      } else {
        return null
      }

    } catch (err) {
      console.error('Erreur recherche agent:', err)
      if (err.message.includes('404') || err.message.includes('non trouvé')) {
        return null
      }
      error.value = err.message
      return null
    } finally {
      loading.value = false
    }
  }

  async function supprimerAgent(codePersonnel) {
    loading.value = true
    error.value = ''

    try {
      console.log('Suppression agent:', codePersonnel)
      const response = await fetch(`${API_BASE}?path=agents&code=${encodeURIComponent(codePersonnel)}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      await parseApiResponse(response)

      // Recharger les données après suppression
      await chargerAgents()
      await chargerCreneaux()

    } catch (err) {
      console.error('Erreur suppression agent:', err)
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function chargerStatistiques() {
    try {
      console.log('Chargement statistiques...')
      const response = await fetch(`${API_BASE}?path=stats`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      const data = await parseApiResponse(response)
      return data

    } catch (err) {
      console.error('Erreur chargement statistiques:', err)
      // Retourner les stats calculées localement en cas d'erreur
      return {
        total_agents: totalInscriptions.value,
        total_proches: totalProches.value,
        total_personnes: totalPersonnes.value,
        agents_matin: groupeMatin.value,
        agents_apres_midi: groupeApresMidi.value,
        personnes_matin: personnesMatin.value,
        personnes_apres_midi: personnesApresMidi.value
      }
    }
  }

  function exporterDonnees() {
    if (agents.value.length === 0) {
      throw new Error('Aucune donnée à exporter')
    }

    // Préparer les données CSV
    const headers = [
      'Code Personnel',
      'Nom',
      'Prénom',
      'Service',
      'Nb Proches',
      'Total Personnes',
      'Heure Arrivée',
      'Période',
      'Date Inscription'
    ]

    const rows = agents.value.map(agent => [
      agent.code_personnel,
      agent.nom,
      agent.prenom,
      agent.service,
      agent.nombre_proches,
      parseInt(agent.nombre_proches) + 1,
      agent.heure_arrivee,
      (agent.heure_arrivee >= '09:00' && agent.heure_arrivee <= '11:40') ? 'Matin' : 'Après-midi',
      new Date(agent.date_inscription).toLocaleString('fr-FR')
    ])

    // Créer le contenu CSV
    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(field => `"${field}"`).join(','))
    ].join('\n')

    // Télécharger le fichier
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')

    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', `inscriptions_journee_proches_${new Date().toISOString().split('T')[0]}.csv`)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }
  }

  // Initialisation
  async function initialiser() {
    try {
      console.log('Initialisation du store...')
      await testConnexion()
      await chargerAgents()
      await chargerCreneaux()
      console.log('Initialisation terminée')
    } catch (err) {
      console.error('Erreur initialisation:', err)
      error.value = `Impossible de se connecter à l'API: ${err.message}`

      // Continuer avec des données par défaut
      agents.value = []
      creneaux.value = {
        matin: generateDefaultCreneaux(['09:00', '09:20', '09:40', '10:00', '10:20', '10:40', '11:00', '11:20', '11:40']),
        'apres-midi': generateDefaultCreneaux(['13:00', '13:20', '13:40', '14:00', '14:20', '14:40', '15:00', '15:20', '15:40'])
      }
    }
  }

  return {
    // État
    agents,
    creneaux,
    loading,
    error,

    // Getters
    agentsTries,
    totalInscriptions,
    totalProches,
    totalPersonnes,
    groupeMatin,
    groupeApresMidi,
    personnesMatin,
    personnesApresMidi,

    // Actions
    testConnexion,
    chargerAgents,
    chargerCreneaux,
    ajouterAgent,
    rechercherAgent,
    supprimerAgent,
    chargerStatistiques,
    exporterDonnees,
    initialiser
  }
})
