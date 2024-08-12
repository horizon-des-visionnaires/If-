document.getElementById("copyLinkButton").addEventListener("click", function() {
    var tempInput = document.createElement("input");
    tempInput.value = window.location.href;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    alert("Lien copié dans le presse-papier!");
});