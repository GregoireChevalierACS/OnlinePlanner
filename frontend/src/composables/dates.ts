/** Date locale au format AAAA-MM-JJ (celui des champs <input type="date"> et de l'API). */
export function isoDate(date: Date): string {
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
}

export function addDays(isoDay: string, days: number): string {
  // Sans « Z », la date est lue en heure locale : pas de décalage d'un jour selon le fuseau.
  const date = new Date(`${isoDay}T00:00:00`)
  date.setDate(date.getDate() + days)
  return isoDate(date)
}

/**
 * Heure HH:MM telle qu'envoyée par le serveur, sans conversion de fuseau
 * (cohérent avec l'API tant que les fuseaux par prestataire n'existent pas, voir ROADMAP).
 */
export function timeOf(isoDateTime: string): string {
  return isoDateTime.slice(11, 16)
}

export function dayOf(isoDateTime: string): string {
  return isoDateTime.slice(0, 10)
}
