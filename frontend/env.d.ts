/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** Origine de l'API (vide en développement : le proxy Vite redirige /api). */
  readonly VITE_API_URL?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
