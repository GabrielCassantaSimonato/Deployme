document
.getElementById("confirmarDesativacao")
.addEventListener("click", function () {

    fetch("/deactivateAccount", {

        method: "POST",

        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }

    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {
            window.location.href = "/";
        }

    });

});