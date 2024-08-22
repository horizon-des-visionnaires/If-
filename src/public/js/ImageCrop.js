let cropper;
let imageElement = document.getElementById('imageToCrop');
let filesToCrop = [];
let currentFileIndex = 0;

function openCropperModal(input) {
    filesToCrop = Array.from(input.files);
    if (filesToCrop.length > 0) {
        loadAndCropImage(filesToCrop[0]);
    }
}

function loadAndCropImage(file) {
    const reader = new FileReader();
    reader.onload = function(event) {
        imageElement.src = event.target.result;
        document.getElementById('cropperModal').style.display = 'block';

        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(imageElement, {
            aspectRatio: 1,
            viewMode: 1,
        });
    };
    reader.readAsDataURL(file);
}

function setAspectRatio(aspectRatio) {
    cropper.setAspectRatio(aspectRatio);
}

function cropImage() {
    const canvas = cropper.getCroppedCanvas();
    canvas.toBlob(function(blob) {
        const file = new File([blob], `croppedImage${currentFileIndex}.jpg`, { type: 'image/jpeg' });
        
        // Remplace le fichier d'origine dans la liste des fichiers
        filesToCrop[currentFileIndex] = file;
        
        // Passer à l'image suivante
        currentFileIndex++;
        if (currentFileIndex < filesToCrop.length) {
            loadAndCropImage(filesToCrop[currentFileIndex]);
        } else {
            finalizeCropping();
        }
    }, 'image/jpeg');
}

function finalizeCropping() {
    const input = document.getElementById('images');
    const dataTransfer = new DataTransfer();
    
    filesToCrop.forEach(file => dataTransfer.items.add(file));
    input.files = dataTransfer.files;
    
    closeCropperModal();
}

function closeCropperModal() {
    document.getElementById('cropperModal').style.display = 'none';
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    filesToCrop = [];
    currentFileIndex = 0;
}





