describe('Display My courses', () => {
    beforeEach(() => {
        cy.login(Cypress.env('username'), Cypress.env('password'))
    })
    it('Access to CGS Connect', () => {
        cy.visit('/my/courses.php');
    })
})