function showPost() {

    document.getElementById('postForm').style.display = 'block';
    document.getElementById('vagaForm').style.display = 'none';

    document.getElementById('btnPost').classList.add('active');
    document.getElementById('btnVaga').classList.remove('active');

}

function showVaga() {

    document.getElementById('postForm').style.display = 'none';
    document.getElementById('vagaForm').style.display = 'block';

    document.getElementById('btnVaga').classList.add('active');
    document.getElementById('btnPost').classList.remove('active');

}

function previewPostImage(event) {

    const input = event.target;

    if (input.files && input.files[0]) {

        const reader = new FileReader();

        reader.onload = function(e) {

            document
                .getElementById('previewImagem')
                .src = e.target.result;

            document
                .getElementById('previewContainer')
                .classList.remove('d-none');

        }

        reader.readAsDataURL(input.files[0]);

    }

}

function previewVagaImage(event) {

    const input = event.target;

    if (input.files && input.files[0]) {

        const reader = new FileReader();

        reader.onload = function(e) {

            document
                .getElementById('previewImagemVaga')
                .src = e.target.result;

            document
                .getElementById('previewContainerVaga')
                .classList.remove('d-none');

        }

        reader.readAsDataURL(input.files[0]);

    }

}


