const changeHandler = (e) => {

    let declarations = JSON.parse(document.getElementById('id_declarationjson').value);
    let id = (e.target.id).split('_');
    id = id[id.length - 1]; // The last position has the id.

    declarations.forEach((dec) => {

        if (dec.detail == id) {
            if (e.target.checked) {
                dec.selected = 1;
            } else {
                dec.selected = 0;
            }
        }
    }, e);
    document.getElementById('id_declarationjson').value = JSON.stringify(declarations);
}

const removeRequiredWhenCancelled = function () {
    document.querySelectorAll('[id^=id_declaration_checkbox_]').forEach((cbox) => {
        cbox.removeAttribute('required');
    });
}

const initEventListener = function () {
    document.querySelectorAll('[id^=id_declaration_checkbox_]').forEach((cbox) => {
        cbox.addEventListener('change', changeHandler);
    });

    document.getElementById('id_cancel').addEventListener('click', removeRequiredWhenCancelled);
}

export const init = () => {
    initEventListener();

};