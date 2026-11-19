import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
export default defineConfig({
  plugins: [laravel({ input: ['resources/js/main.ts'], refresh: true }), vue()],
  server: { host: '127.0.0.1' },
  test: { include: ['resources/js/**/*.test.ts'] },
});
