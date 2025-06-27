<template>
  <div class="login-view">
    <h2>🔒 Connexion</h2>
    <p class="subtitle">Veuillez vous connecter pour accéder à l'administration</p>

    <!-- Message d'erreur -->
    <div v-if="error" class="alert alert-error">
      <strong>Erreur :</strong> {{ error }}
      <button @click="error = ''" class="alert-close">×</button>
    </div>

    <div class="login-container">
      <form class="login-form" @submit.prevent="login">
        <div class="form-group">
          <label for="username">Nom d'utilisateur</label>
          <input
            v-model="username"
            type="text"
            id="username"
            placeholder="Entrez votre nom d'utilisateur"
            required
            autocomplete="username"
          />
        </div>
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input
            v-model="password"
            type="password"
            id="password"
            placeholder="Entrez votre mot de passe"
            required
            autocomplete="current-password"
          />
        </div>
        <button
          class="btn btn-primary"
          type="submit"
        >
          Se connecter
        </button>
      </form>
    </div>

    <div class="login-footer">
      <p>Accès réservé au personnel autorisé</p>
      <router-link to="/inscription" class="btn btn-secondary">
        Retour à l'inscription
      </router-link>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'LoginView',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    const username = ref('')
    const password = ref('')
    const loading = ref(false)
    const error = ref('')

    async function login() {
      if (!username.value || !password.value) {
        error.value = 'Veuillez remplir tous les champs'
        return
      }

      loading.value = true
      error.value = ''

      try {
        const success = await authStore.login(username.value, password.value)
        if (success) {
          // Rediriger vers la page demandée ou la page de gestion par défaut
          const redirectPath = router.currentRoute.value.query.redirect || '/gestion'
          router.push(redirectPath)
        } else {
          error.value = 'Identifiants incorrects'
        }
      } catch (err) {
        console.error('Erreur de connexion:', err)
        error.value = err.message || 'Erreur lors de la connexion'
      } finally {
        loading.value = false
      }
    }

    return {
      username,
      password,
      loading,
      error,
      login
    }
  }
}
</script>

<style scoped>
.login-view {
  max-width: 500px;
  margin: 0 auto;
  padding: 40px 20px;
}

.login-view h2 {
  text-align: center;
  margin-bottom: 10px;
  color: #2c3e50;
}

.subtitle {
  text-align: center;
  color: #6c757d;
  margin-bottom: 30px;
}

.login-container {
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  padding: 30px;
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.form-group input {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1em;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.login-btn {
  width: 100%;
  padding: 12px;
  font-size: 1.1em;
  margin-top: 10px;
}

.login-footer {
  text-align: center;
  margin-top: 20px;
  color: #6c757d;
}

.login-footer p {
  margin-bottom: 15px;
  font-size: 0.9em;
}

.alert {
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: 500;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
  border-left: 4px solid #dc3545;
}

.alert-close {
  background: none;
  border: none;
  font-size: 1.4em;
  font-weight: bold;
  cursor: pointer;
  color: inherit;
  opacity: 0.7;
  transition: opacity 0.3s;
  margin-left: 15px;
}

.alert-close:hover {
  opacity: 1;
}

.btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, #3498db, #2980b9);
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #5a6268);
}

.spinner-small {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
