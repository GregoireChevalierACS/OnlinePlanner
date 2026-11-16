import { describe, expect, it } from 'vitest'
import { addDays, dayOf, isoDate, timeOf } from '../dates'

describe('dates', () => {
  it('formate une date locale en AAAA-MM-JJ', () => {
    expect(isoDate(new Date(2026, 0, 5))).toBe('2026-01-05')
    expect(isoDate(new Date(2026, 10, 25))).toBe('2026-11-25')
  })

  it('ajoute des jours en passant les mois et les années', () => {
    expect(addDays('2026-01-30', 3)).toBe('2026-02-02')
    expect(addDays('2026-12-31', 1)).toBe('2027-01-01')
    expect(addDays('2026-03-01', -1)).toBe('2026-02-28')
  })

  it("extrait l'heure et le jour d'une date ISO sans conversion de fuseau", () => {
    expect(timeOf('2026-01-05T09:30:00+00:00')).toBe('09:30')
    expect(dayOf('2026-01-05T09:30:00+00:00')).toBe('2026-01-05')
  })
})
