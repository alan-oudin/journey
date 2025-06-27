// stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // État
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  const error = ref('')

  // Récupérer l'URL de l'API
  function getApiBaseUrl() {
    // Détection automatique de l'environnement
    const hostname = window.location.hostname
    const port = window.location.port

    // Variables d'environnement (si vous utilisez Vite)
    const isDev = import.meta.env.DEV
    const apiUrl = import.meta.env.VITE_API_URL

    // 1. Si une variable d'environnement est définie, l'utiliser
    if (apiUrl) {
      // console.log('🌐 Utilisation de la variable d\'environnement VITE_API_URL:', apiUrl)
      return apiUrl
    }

    // 2. Détection automatique selon l'hôte et le port
    if (hostname === 'localhost' || hostname === '127.0.0.1') {
      if (port === '8080' || port === '80') {
        // WAMPP / XAMPP avec port 8080
        // console.log('🔧 Environnement détecté: WAMPP/XAMPP (port 8080)')
        return 'http://localhost:8080/journey/public/api.php'
      } else if (port === '3000' || port === '5173' || port === '4173') {
        // Développement avec Vite (port par défaut 5173) + Laragon
        // console.log('🔧 Environnement détecté: Développement Vite + Laragon')
        return 'http://localhost/journey/public/api.php'
      } else {
        // Laragon par défaut (port 80)
        // console.log('🔧 Environnement détecté: Laragon (localhost)')
        return 'http://localhost/journey/public/api.php'
      }
    } else {
      // Production (domaine personnalisé)
      // console.log('🚀 Environnement détecté: Production')
      return `${window.location.protocol}//${window.location.host}/api.php`
    }
  }

  // URL de l'API
  const API_BASE = getApiBaseUrl()

  // Getters
  const isAuthenticated = computed(() => !!token.value)

  // Actions
  async function login(username, password) {
    loading.value = true
    error.value = ''

    try {
      console.log('🔑 Tentative de connexion:', username)

      const response = await fetch(`${API_BASE}?path=login`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          username,
          password
        })
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || 'Erreur lors de la connexion')
      }

      if (data.success) {
        // Stocker le token dans le localStorage et dans l'état
        token.value = data.token
        localStorage.setItem('auth_token', data.token)

        // Stocker les informations de l'utilisateur
        user.value = {
          username: data.username,
          role: data.role || 'admin'
        }

        console.log('✅ Connexion réussie')
        return true
      } else {
        throw new Error(data.message || 'Identifiants incorrects')
      }
    } catch (err) {
      console.error('❌ Erreur de connexion:', err)
      error.value = err.message
      return false
    } finally {
      loading.value = false
    }
  }

  function logout() {
    // Supprimer le token du localStorage et de l'état
    localStorage.removeItem('auth_token')
    token.value = null
    user.value = null
    console.log('🚪 Déconnexion réussie')
  }

  // Vérifier si le token est valide
  async function checkAuth() {
    if (!token.value) {
      return false
    }

    try {
      const response = await fetch(`${API_BASE}?path=verify-token`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token.value}`
        }
      })

      const data = await response.json()

      if (response.ok && data.valid) {
        return true
      } else {
        // Token invalide, déconnexion
        logout()
        return false
      }
    } catch (err) {
      console.error('❌ Erreur vérification token:', err)
      return false
    }
  }

  return {
    // État
    user,
    token,
    loading,
    error,

    // Getters
    isAuthenticated,

    // Actions
    login,
    logout,
    checkAuth
  }
})
