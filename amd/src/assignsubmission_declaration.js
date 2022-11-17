import ModalAddDeclaration from 'assignsubmission_declaration/modal_add_new_declaration';
import ModalFactory from 'core/modal_factory';

const addDeclarationHandler = function (e) {
    console.log(e);
    var trigger = $('#add-new-declaration-btn');
    ModalFactory.create({type: ModalAddDeclaration.TYPE}, trigger);

}

const selectSubmissionHandler = function (e) {
    if (e.target.checked) {
           // Show elements related to declaration submission
        showSubmissionDeclarationElements();
    } else {
        // Hide elements related to declaration submission
        hideSubmissionDeclarationElements();

    }
}

const hideSubmissionDeclarationElements = function () {
    let match = Array.from(document.querySelectorAll('[id^=id_assignsubmission_declaration_]'));
    document.getElementById('add-new-declaration-btn').style.display = 'none';
    match.forEach(el => {
        if (el.getAttribute('id') != 'id_assignsubmission_declaration_enabled') {
            el.style.display = 'none';
        }
    });
}

const showSubmissionDeclarationElements = function () {
    document.getElementById('add-new-declaration-btn').style.display = 'inline-block';
    let match = Array.from(document.querySelectorAll('[id^=id_assignsubmission_declaration_]'));
    match.forEach(el => {
        if (el.getAttribute('id') != 'id_assignsubmission_declaration_enabled') {
            el.style.display = '';
        }
    });
}


export const init = () => {
    if (!document.getElementById('id_assignsubmission_declaration_enabled').checked) {
        document.getElementById('add-new-declaration-btn').style.display = 'none';
    } else {
        document.getElementById('add-new-declaration-btn').style.display = 'inline-block';
    }

    document.getElementById('id_assignsubmission_declaration_enabled').addEventListener('change', selectSubmissionHandler);
    document.querySelector('.add-new-declaration').addEventListener('click', addDeclarationHandler);
};