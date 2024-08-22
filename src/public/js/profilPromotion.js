document.addEventListener("DOMContentLoaded", function() {
    const textarea = document.getElementById("inputFieldPromotion");
    const charCount = document.getElementById("charCount2");
    const maxBytes = 100;

    function getByteLength(str) {
        return new TextEncoder().encode(str).length;
    }

    textarea.addEventListener("input", function() {
        let value = textarea.value;
        let byteLength = getByteLength(value);

        while (byteLength > maxBytes) {
            value = value.substring(0, value.length - 1);
            byteLength = getByteLength(value);
        }

        textarea.value = value;
        charCount.textContent = `${byteLength}/${maxBytes}`;
    });
});