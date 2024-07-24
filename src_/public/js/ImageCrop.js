var cropper;
var currentFileInput;

function openCropperModal(input) {
    currentFileInput = input;
    var files = input.files;
    if (files && files.length > 0) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#imageToCrop').attr('src', e.target.result);
            $('#cropperModal').css('display', 'block');
            cropper = new Cropper(document.getElementById('imageToCrop'), {
                aspectRatio: 1, // Change this to your desired aspect ratio
                viewMode: 1
            });
        };
        reader.readAsDataURL(files[0]);
    }
}

$('.close').click(function () {
    $('.modal').css('display', 'none');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

$('#cropButton').click(function () {
    var canvas = cropper.getCroppedCanvas({
        width: 500, // Desired output width
        height: 500 // Desired output height
    });
    canvas.toBlob(function (blob) {
        var file = new File([blob], 'cropped_image.jpg', { type: 'image/jpeg' });

        // Create a new DataTransfer object and add the file to it
        var dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        // Update the file input with the cropped file
        currentFileInput.files = dataTransfer.files;

        // Trigger the change event on the file input
        $(currentFileInput).trigger('change');

        // Close the cropper modal
        $('#cropperModal').css('display', 'none');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });
});