import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// https://vitejs.dev/config/
export default defineConfig(({ command, mode }) => {
  // Charge les variables d'environnement en fonction du mode (development, production)
  // Utilise .env, .env.local, .env.[mode], .env.[mode].local
  const env = loadEnv(mode, process.cwd())

  console.log(`Mode: ${mode}, Command: ${command}`)

  return {
    plugins: [vue()],
    resolve: {
      alias: {
        '@': resolve(__dirname, 'src')
      }
    },
    server: {
      port: 3000
    },
    // Expose les variables d'environnement au frontend
    define: {
      'process.env': env
    }
  }
})
