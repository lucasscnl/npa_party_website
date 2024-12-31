let cropper;

document.getElementById('news-img').addEventListener('change', function (event) {
    const file = event.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const imagePreview = document.getElementById('image-preview');
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';

            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(imagePreview, {
                aspectRatio: 300 / 200,  // Forcer un rapport de 3:2 pour le rognage
                viewMode: 1,
            });

            document.getElementById('crop-btn').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('crop-btn').addEventListener('click', function () {
    if (cropper) {
        // Rognage de l'image à 300x200px
        const croppedCanvas = cropper.getCroppedCanvas({
            width: 300,
            height: 200,
        });

        // Obtenir l'image rognée en base64
        const croppedImageData = croppedCanvas.toDataURL('image/jpeg');
        document.getElementById('cropped-image-data').value = croppedImageData;

        alert("L'image a été rognée avec succès !");
    }
});
