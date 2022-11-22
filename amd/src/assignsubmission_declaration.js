import ModalAddDeclaration from 'assignsubmission_declaration/modal_add_new_declaration';
import ModalFactory from 'core/modal_factory';

const addDeclarationHandler = function (e) {
    console.log(e);
    var trigger = $('#add-new-declaration-btn');
    ModalFactory.create({
        type: ModalAddDeclaration.TYPE
    }, trigger);

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

const changeTextareaHandler = (e) => {
    console.log("changeTextareaHandler");
    // Empty textarea
    if (document.getElementById(e.target.id).value == '') {
        document.getElementById(e.target.id).classList.add('empty-value-error');
        document.getElementById(e.target.id).value = "Declaration description cannot be empty";
    } else {
        document.getElementById(e.target.id).classList.remove('empty-value-error');
    }
    let id = (e.target.id).split('_');
    id = id[id.length - 1];
    const data = JSON.parse(document.getElementById('id_declarationjson').value);
    const updateData = {
        id: id,
        declaration_text: document.getElementById(e.target.id).value
    };

    console.log(updateData);
    data.forEach((d) => {
        console.log(d);
        if (d.id == updateData.id && updateData.declaration_text != "Declaration description cannot be empty") {
            d.declaration_text = updateData.declaration_text;
        }
    }, updateData);

    document.getElementById('id_declarationjson').value = JSON.stringify(data);

}

const makeTitleEditable = () => {

    Array.from(document.querySelectorAll('#fgroup_id_assignsubmission_declaration_group')).forEach((child, index) => {
        Array.from(child.children).forEach((child1, index) => {
          //  console.log(child1);
            Array.from(child1.children).forEach((child, index) => {
                if (index == 0 && child.nodeName == 'P') {
                    child.setAttribute('contenteditable', true);
                    child.addEventListener('input', changeTitleHandler);
                };
            }, child1);
        });

    });

}

const changeTitleHandler = (e) => {
    //title div -> textarea div
    console.log(e.target.id);
    if (document.getElementById(e.target.id).innerHTML == '') {
        document.getElementById(e.target.id).innerHTML = "Title cannot be empty";
        document.getElementById(e.target.id).style.border = "2px solid red";
    } else {
        document.getElementById(e.target.id).style.border = "";
    }

    let id = e.target.id.split('_'); // Get the element that has the textarea nested and that it has the id we need.
    id = id[id.length - 1];
    console.log(e.target.id);
    const data = JSON.parse(document.getElementById('id_declarationjson').value);
    const updateData = {
        id: id,
        declaration_title: document.getElementById(e.target.id).innerHTML.replace(/^\s+|\s+$/g, '')
    };
    console.log(updateData);
    data.forEach((d) => {
        console.log(d);
        if (d.id == updateData.id && updateData.declaration_title != 'Title cannot be empty') {
            d.declaration_title = updateData.declaration_title;
        }
    }, updateData);

    document.getElementById('id_declarationjson').value = JSON.stringify(data);
}

const selectHandler = (e) => {
    console.log("selectHandler");

    let id = e.target.id.split('_');
    id = id[id.length - 2];
    let data = JSON.parse(document.getElementById('id_declarationjson').value);
    let updateData = {
        id: id,
        selected: e.target.checked ? 1 : 0
    }
    data.forEach((d) => {

        if (d.id == updateData.id) {
            d.selected = updateData.selected;
        }
    }, updateData);

    document.getElementById('id_declarationjson').value = JSON.stringify(data);
}
const deleteDeclarationHandler = (e) => {
    let id = (e.target.id).split('_');
    id = id[id.length - 1];
    let data = JSON.parse(document.getElementById('id_declarationjson').value);
    let updateData = {
        id: id,
        deleted: 1
    }
    data.forEach((d) => {

        if (d.id == updateData.id) {
            d.deleted = updateData.deleted;
        }
    }, updateData);

    document.getElementById('id_declarationjson').value = JSON.stringify(data);
}

export const init = () => {
    if (!document.getElementById('id_assignsubmission_declaration_enabled').checked) {
        document.getElementById('add-new-declaration-btn').style.display = 'none';
    } else {
        document.getElementById('add-new-declaration-btn').style.display = 'inline-block';
    }

    document.getElementById('id_assignsubmission_declaration_enabled').addEventListener('change', selectSubmissionHandler);
    document.querySelector('.add-new-declaration').addEventListener('click', addDeclarationHandler);

    // Textarea add event.
    document.querySelectorAll('textarea[id^=id_assignsubmission_declaration_]').forEach((textarea) => {
        textarea.addEventListener('change', changeTextareaHandler);
    });
    // Checkbox add event.
    document.querySelectorAll('input[id^=id_assignsubmission_declaration_]').forEach((checkbox) => {

        let id = checkbox.getAttribute('id').split('_');
        if (id[id.length - 1] == 'check') {
            checkbox.addEventListener('change', selectHandler);
        }

    });
    // Delete event listeners
    delete_declaration_1
    document.querySelectorAll('i[id^=delete_declaration_]').forEach((deleteicon) => {
        deleteicon.addEventListener('click', deleteDeclarationHandler);
    })

    makeTitleEditable();
};