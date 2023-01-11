describe('Display My courses', () => {
    beforeEach(() => {
        cy.login(Cypress.env('username'), Cypress.env('password'))
        cy.turnEditingOn();
    })
    it('Creates assessment', () => {
        cy.visit('/course/modedit.php?add=assign&type=&course=7&section=0&return=0&sr=0');
        // Name the assessment
        cy.get('input#id_name').type('Dummy assessment');
        // Submission type
        cy.get('input[name=assignsubmission_declaration_enabled]').check();
        // Save the assessment
        cy.get('#region-main .mform').submit();

    })
})