document.addEventListener(keydown, escapeModal, false);

function openModal(){
    var modal = document.getElementById('myModal');
    modal.style.display = block;
}

function closeModal(){
    var modal = document.getElementById('myModal');
    modal.style.display = none;
}

function escapeModal(evt){
    var keyCode = evt.keyCode;

    if(keyCode == 27){  // Escape key.
        closeModal();
    }
}