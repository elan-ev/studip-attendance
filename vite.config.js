import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig(({ mode }) => {
    const isDev = mode === 'development';

    return {
        plugins: [vue()],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url)),
            },
        },
        build: {
            sourcemap: true,
            cssCodeSplit: false,
            rollupOptions: {
                input: {
                    'studip-attendance': 'src/studip-attendance.js',
                    'studip-attendance-admin': 'src/studip-attendance-admin.js',
                    'studip-attendance-widget-teacher': 'src/studip-attendance-widget-teacher.js',
                    'studip-attendance-widget-student': 'src/studip-attendance-widget-student.js',
                },
                output: {
                    entryFileNames: `[name].js`,
                    assetFileNames: (assetInfo) => {
                        if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                            return 'attendance.css';
                        }
                        return '[name].[ext]';
                    },
                },
            },
        },
        define: {
            'process.env.NODE_ENV': JSON.stringify(mode),
            __VUE_PROD_DEVTOOLS__: isDev,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: isDev,
        },
    };
});
