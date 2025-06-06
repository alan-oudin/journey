<template>
  <div id="app">
    <header class="app-header">
      <h1>🏢 Journée des Proches</h1>
      <p>Système d'inscription et de gestion des visites</p>
    </header>

    <nav class="app-nav">
      <RouterLink to="/inscription" class="nav-link">📝 Inscription</RouterLink>
      <RouterLink to="/gestion" class="nav-link">👥 Gestion</RouterLink>
      <RouterLink to="/recherche" class="nav-link">🔍 Recherche</RouterLink>
    </nav>

    <main class="app-main">
      <RouterView />
    </main>
  </div>
</template>

<script>
import { RouterLink, RouterView } from 'vue-router'

export default {
  name: 'App',
  components: {
    RouterLink,
    RouterView
  }
}
</script>

<style scoped>
.app-header {
  background: linear-gradient(135deg, #2c3e50, #3498db);
  color: white;
  padding: 30px;
  text-align: center;
}

.app-header h1 {
  font-size: 2.5em;
  margin-bottom: 10px;
  font-weight: 300;
}

.app-header p {
  font-size: 1.2em;
  opacity: 0.9;
}

.app-nav {
  display: flex;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.nav-link {
  flex: 1;
  padding: 20px;
  text-align: center;
  text-decoration: none;
  font-size: 1.1em;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
}

.nav-link:hover {
  background: #e9ecef;
  color: #2c3e50;
}

.nav-link.router-link-active {
  background: white;
  color: #2c3e50;
  border-bottom-color: #3498db;
}

.app-main {
  padding: 40px;
  max-width: 1200px;
  margin: 0 auto;
}
</style>
