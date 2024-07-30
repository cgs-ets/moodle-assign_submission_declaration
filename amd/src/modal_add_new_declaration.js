import Modal from 'core/modal';
import CustomEvents from 'core/custom_interaction_events';

const SELECTORS = {
    ADD_BUTTON: '[data-action="add"]',
    CANCEL_BUTTON: '[data-action="cancel"]',
};

export default class NewDeclarationModal extends Modal {
    static TYPE = 'assignsubmission_declaration-addnewdeclaration';
    static TEMPLATE = 'assignsubmission_declaration/assignsubmission_declaration_modal';

    registerEventListeners() {
        // Apply parent event listeners.
        super.registerEventListeners(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.ADD_BUTTON, function (e, data) {
            // Add your logic for when the login button is clicked.

            let match = Array.from(document.querySelectorAll('[id^=id_assignsubmission_declaration_]')); // Get the ids that start with this string
            const currentIds = match.filter(id => { // The format of the ids is id_assignsubmission_declaration_1. This returns an array of elements
                if (id.getAttribute('id').match(/(\d+)\D*$/)) return id;
            });

            let lastNumber = (currentIds[currentIds.length - 2]).getAttribute('id'); // We get the textarea and checkbox id, the textarea is easier to work with.
            lastNumber = lastNumber.split('_');
            lastNumber = lastNumber.filter(val => {
                if (!isNaN(val)) return val;
            });
            const newID = (JSON.parse(document.getElementById('id_declarationjson').value).length + 1); //parseInt(lastNumber[lastNumber.length - 1]) + 1;

            const newDec = {
                declaration_title: document.getElementById('inputTitle').value,
                declaration_text: document.getElementById('inputContent').value,
                id: newID,
                assignment: document.getElementById('id_declarationjson').getAttribute('assignmentid'),
                selected: 0
            };

            document.getElementById('inputTitle').classList.remove('input-error');
            document.getElementById('inputContent').classList.remove('input-error');

            if (newDec.declaration_title == '') {
                document.getElementById('inputTitle').classList.add('input-error');
                return;
            }

            if (newDec.declaration_text == '') {
                document.getElementById('inputContent').classList.add('input-error');
                return;
            }

            const declaration = document.getElementById('fgroup_id_assignsubmission_declaration_group_1'); // Its the first oneof all.
            const declarationSubmissionContainer = document.getElementById('id_submissiontypescontainer');
            const btnContainer = document.querySelector('.add-new-declaration-container');

            let newDeclaration = declaration.cloneNode(true);
            newDeclaration.setAttribute('id', `fgroup_id_assignsubmission_declaration_group_${newDec.id}`);

            Array.from(newDeclaration.children).forEach((child, index) => {

                if (index == 0) {
                    // Title section
                    const newid = `fgroup_id_assignsubmission_declaration_group_label_${(JSON.parse(document.getElementById('id_declarationjson').value).length + 1)}`; //_${newDec.declaration_title}
                    child.children[index].setAttribute('id', newid);
                    newDec.old_title = child.children[index].innerHTML.replace(/^\s+|\s+$/g, '');
                    child.children[index].setAttribute('data-current-title', newDec.declaration_title);
                    child.children[index].innerHTML = newDec.declaration_title;

                    child.setAttribute('contenteditable', true);
                    child.addEventListener('input', function (e) {
                        //title div -> textarea div
                        let id = document.getElementById(e.target.id).nextElementSibling.children[0].children[1].children[1].getAttribute('id'); // Get the element that has the textarea nested and that it has the id we need.
                        id = id[id.length - 1];

                        const data = JSON.parse(document.getElementById('id_declarationjson').value);
                        const updateData = {
                            id: id,
                            declaration_title: document.getElementById(e.target.id).children[0].innerHTML.replace(/^\s+|\s+$/g, '')
                        };
                        data.forEach((d) => {
                            if (d.id == updateData.id) {
                                d.declaration_title = updateData.declaration_title;
                            }
                        }, updateData);
                        document.getElementById('id_declarationjson').value = JSON.stringify(data);
                    });
                } else {

                    Array.from(child.children).forEach((child, index) => {
                        if (index == 0) {

                            Array.from(child.children).forEach((child, index) => {
                                if (index == 0) {
                                    child.innerHTML = newDec.declaration_title; //Legend
                                } else {

                                    Array.from(child.children).forEach((child, index) => {
                                        if (index == 0) {
                                            child.setAttribute('for', `id_assignsubmission_declaration_${newDec.id}`);
                                        } else if (index == 1) { // Textarea.
                                            child.innerHTML = newDec.declaration_text;

                                            child.removeAttribute('placeholder');
                                            child.setAttribute('value', newDec.declaration_text);
                                            child.setAttribute('id', `id_assignsubmission_declaration_${newDec.id}`)
                                            child.setAttribute('name', `assignsubmission_declaration_${newDec.id}`)

                                        } else if (index == 2) { // Checkbox.

                                            Array.from(child.children).forEach((child, index) => {
                                                child.value = 0;
                                                child.removeAttribute('checked');
                                                child.setAttribute('id', `id_assignsubmission_declaration_${newDec.id}_check`);
                                                child.setAttribute('name', `assignsubmission_declaration_${newDec.id}_check`);
                                                child.addEventListener('change', function (e) {
                                                    if (e.target.checked) {
                                                        let declaration = JSON.parse(document.getElementById('id_declarationjson').value);
                                                        declaration.forEach(decl => {
                                                            if (decl.id == newDec.id) {
                                                                decl.selected = 1;
                                                            }
                                                        }, newDeclaration);

                                                        document.getElementById('id_declarationjson').value = JSON.stringify(declaration);
                                                    }
                                                })
                                            })

                                        } else if (index == 3) { // Delete section.
                                            child.setAttribute('data-delete-icon', 1);
                                            child.removeAttribute('hidden');

                                            Array.from(child.children).forEach((child, index) => {
                                                child.setAttribute('id', `delete_declaration_container_${newDec.id}`);
                                                Array.from(child.children).forEach((child, index) => {
                                                    child.setAttribute('id', `delete_declaration_${newDec.id}`); // x icon
                                                })

                                            });

                                            // This descriptor is not saved in the DB, so its safe to just remove it from the JSON.
                                            child.addEventListener('click', function (e) {
                                                let id = e.target.id.split('_');
                                                id = id[id.length - 1];
                                                let data = JSON.parse(document.getElementById('id_declarationjson').value);
                                                data = data.filter((d) => {
                                                    if (d.id != id) {
                                                        return d;
                                                    }
                                                }, id);
                                                document.getElementById('id_declarationjson').value = JSON.stringify(data);
                                                // remove element from view.
                                                document.getElementById(`fgroup_id_assignsubmission_declaration_group_${id}`).remove();

                                            });
                                            const spanText = child.innerHTML;
                                            child.innerHTML = spanText.replace(newDec.old_title, newDec.declaration_title);


                                        } else {
                                            child.setAttribute('id', `id_assignsubmission_declaration_${newDec.id}_check`);

                                        }
                                    })
                                }
                            });

                        }

                    });
                }
                this.destroy(); // Remove the modal.
            }, newDec);

            // Add the descriptor to the json
            let decl = JSON.parse(document.getElementById('id_declarationjson').value);
            decl.push(newDec);
            document.getElementById('id_declarationjson').value = JSON.stringify(decl);
            declarationSubmissionContainer.insertBefore(newDeclaration, btnContainer)


        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, function (e, data) {
            // Add your logic for when the cancel button is clicked.
            this.destroy();

        }.bind(this));
    };


}

NewDeclarationModal.registerModalType()