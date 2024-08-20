document.addEventListener('DOMContentLoaded', (event) => {
    var modal = document.getElementById("profileModal");
    var btn = document.getElementById("openModalButton");
    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});

document.addEventListener('DOMContentLoaded', (event) => {
    var modal = document.getElementById("ProModal");
    var btn = document.getElementById("openModalButtonPro");
    var span = document.getElementsByClassName("close2")[0];
    if(modal){
        if(btn){
            btn.onclick = function() {
                modal.style.display = "block";
            }
        }
        if(span){
            span.onclick = function() {
                modal.style.display = "none";
            }
        }   
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
});

document.addEventListener('DOMContentLoaded', (event) => {
    var modal = document.getElementById("deleteModal");
    var btn = document.getElementById("openModalDeleteButton");
    var span = document.getElementsByClassName("close3")[0];
    if(modal){
        if(btn){
            btn.onclick = function() {
                modal.style.display = "block";
            }
        }
        if(span){
            span.onclick = function() {
                modal.style.display = "none";
            }
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
});