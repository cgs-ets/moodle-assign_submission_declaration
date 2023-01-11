describe('Login to connect', () => {
    beforeEach(() => {
        cy.login(Cypress.env('username'), Cypress.env('password'))
    })
    it('Access to CGS Connect', () => {
        cy.visit('/my');
    })
})