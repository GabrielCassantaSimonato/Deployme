document.addEventListener("DOMContentLoaded", () => {

    const filtros =
        document.querySelectorAll(".filtro-vaga");

    filtros.forEach(campo => {

        campo.addEventListener("keyup", filtrarVagas);
        campo.addEventListener("change", filtrarVagas);

    });

});

function filtrarVagas(){

    const pesquisa =
        document.querySelectorAll(".filtro-vaga");

    const titulo =
        pesquisa[0].value.toLowerCase().trim();

    const localizacao =
        pesquisa[1].value.toLowerCase().trim();

    const modalidade =
        pesquisa[2].value.toLowerCase().trim();

    document
        .querySelectorAll(".vaga-card")
        .forEach(card => {

            const texto =
                card.innerText.toLowerCase();

            const correspondeTitulo =

                titulo === "" ||

                texto.includes(titulo);

            const correspondeLocalizacao =

                localizacao === "" ||

                texto.includes(localizacao);

            const correspondeModalidade =

                modalidade === "" ||

                texto.includes(modalidade);

            card.style.display =

                (
                    correspondeTitulo &&
                    correspondeLocalizacao &&
                    correspondeModalidade
                )

                ? ""

                : "none";

        });

}
