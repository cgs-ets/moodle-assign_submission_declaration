import ModalAddDeclaration from 'assignsubmission_declaration/modal_add_new_declaration';
import ModalFactory from 'core/modal_factory';

const addDeclarationHandler = function (e) {
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

    data.forEach((d) => {
        if (d.id == updateData.id && updateData.declaration_text != "Declaration description cannot be empty") {
            d.declaration_text = updateData.declaration_text;
        }
    }, updateData);

    document.getElementById('id_declarationjson').value = JSON.stringify(data);

}

const makeTitleEditable = () => {

    Array.from(document.querySelectorAll('#fgroup_id_assignsubmission_declaration_group')).forEach((child, index) => {
        let count = index + 1;
        child.setAttribute('id', `fgroup_id_assignsubmission_declaration_group_${count}`);
        Array.from(child.children).forEach((child1, index) => {
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

    if (document.getElementById(e.target.id).innerHTML == '') {
        document.getElementById(e.target.id).innerHTML = "Title cannot be empty";
        document.getElementById(e.target.id).style.border = "2px solid red";
    } else {
        document.getElementById(e.target.id).style.border = "";
    }

    let id = e.target.id.split('_'); // Get the element that has the textarea nested and that it has the id we need.
    id = id[id.length - 1];
    const data = JSON.parse(document.getElementById('id_declarationjson').value);
    const updateData = {
        id: id,
        declaration_title: document.getElementById(e.target.id).innerHTML.replace(/^\s+|\s+$/g, '')
    };

    data.forEach((d) => {
        if (d.id == updateData.id && updateData.declaration_title != 'Title cannot be empty') {
            d.declaration_title = updateData.declaration_title;
        }
    }, updateData);

    document.getElementById('id_declarationjson').value = JSON.stringify(data);
}

const debounce = (callback, wait) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            callback.apply(this, args);
        }, wait);
    };
}
const selectHandler = (e) => {
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
        deleted: 1,
        indextodelete: -1
    }
    if (data.length > 1) {
        data.forEach((d, index) => {
            if (d.id == updateData.id) {
                if (d.sqlid != null) {
                    d.deleted = updateData.deleted;
                } else {
                    updateData.indextodelete = index;
                }
            }
        }, updateData);
        if (updateData.indextodelete > -1) {
            data.splice(updateData.indextodelete, 1);
        }

        document.getElementById('id_declarationjson').value = JSON.stringify(data);
        // remove element from view.
        document.getElementById(`fgroup_id_assignsubmission_declaration_group_${updateData.id}`).remove();
    } else {
        console.log("Just one, cant delete");
    }
}
const refreshDeleteSectionTitle = () => {
    // This function will wait until the user finishes typing the newn title. after, it will refresh the delete title section.
    window.addEventListener('keyup', debounce(() => {
        // code you would like to run 1000ms after the keyup event has stopped firing
        // further keyup events reset the timer, as expected
        let data = JSON.parse(document.getElementById('id_declarationjson').value);
        Array.from(document.querySelectorAll('p[id^=fgroup_id_assignsubmission_declaration_group_label_]')).forEach((p, index) => {
            let id = p.getAttribute('id').split('_');
            id = id[id.length - 1];
            data.forEach((d) => {
                if (d.id == id) {
                    const deletesectiontitle = document.getElementById(`delete_declaration_container_${id}`).innerHTML;
                    const currentTitle = document.getElementById(`delete_declaration_container_${id}`).innerHTML.split('</i>')[1];
                    document.getElementById(`delete_declaration_container_${id}`).innerHTML = deletesectiontitle.replace(currentTitle,`Delete ${d.declaration_title} declaration`);
                    document.getElementById(`fgroup_id_assignsubmission_declaration_group_label_${id}`).setAttribute('data-current-title', d.declaration_title);
                }
            }, id);

        }, data)

    }, 1000))
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
    document.querySelectorAll('i[id^=delete_declaration_]').forEach((deleteicon) => {
        deleteicon.addEventListener('click', deleteDeclarationHandler);
    });

    makeTitleEditable();
    refreshDeleteSectionTitle();


};