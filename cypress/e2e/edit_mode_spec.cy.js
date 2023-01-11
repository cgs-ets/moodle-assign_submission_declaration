describe('Go to course page and turn editing on', () => {
    beforeEach(() => {
        cy.login(Cypress.env('username'), Cypress.env('password'))
    })
    it('Access to Verito VB course', () => {
        cy.visit('/course/view.php?id=7');
        cy.get('input[name=setmode]').check();
        cy.get('form.editmode-switch-form').submit() // Submit a form
    });
})