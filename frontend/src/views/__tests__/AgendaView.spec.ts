import { describe, expect, it } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { schedulingGatewayKey } from '@/api/SchedulingGateway'
import { appointment, fakeGateway } from '@/__tests__/fakeGateway'
import { createPinia } from 'pinia'
import AgendaView from '../AgendaView.vue'

async function mountAgenda(gateway = fakeGateway()) {
  const wrapper = mount(AgendaView, {
    props: { providerId: 'p1' },
    global: { plugins: [createPinia()], provide: { [schedulingGatewayKey as symbol]: gateway } },
  })
  await flushPromises()
  return { wrapper, gateway }
}

describe('AgendaView', () => {
  it('affiche les rendez-vous avec leur statut', async () => {
    const gateway = fakeGateway()
    gateway.agenda.mockResolvedValue([appointment({ status: 'confirmed' })])

    const { wrapper } = await mountAgenda(gateway)

    expect(wrapper.text()).toContain('Jane Doe')
    expect(wrapper.text()).toContain('09:00–09:30')
    expect(wrapper.text()).toContain('Confirmé')
  })

  it("demande une confirmation et un motif avant d'annuler", async () => {
    const gateway = fakeGateway()
    gateway.agenda.mockResolvedValue([appointment()])
    const { wrapper } = await mountAgenda(gateway)

    await wrapper.get('button.danger').trigger('click')
    const dialog = wrapper.get('[role="dialog"]')
    expect(gateway.cancel).not.toHaveBeenCalled()
    expect(dialog.get('button.danger').attributes('disabled')).toBeDefined()

    await dialog.get('input[name="reason"]').setValue('Empêchement')
    await dialog.get('button.danger').trigger('click')
    await flushPromises()

    expect(gateway.cancel).toHaveBeenCalledWith('a1', 'Empêchement')
    expect(wrapper.find('[role="dialog"]').exists()).toBe(false)
  })

  it("n'annule rien si l'utilisateur revient en arrière", async () => {
    const gateway = fakeGateway()
    gateway.agenda.mockResolvedValue([appointment()])
    const { wrapper } = await mountAgenda(gateway)

    await wrapper.get('button.danger').trigger('click')
    await wrapper.get('[role="dialog"] button:not(.danger)').trigger('click')

    expect(wrapper.find('[role="dialog"]').exists()).toBe(false)
    expect(gateway.cancel).not.toHaveBeenCalled()
  })
})
