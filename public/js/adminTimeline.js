let modalExcluir =
    new bootstrap.Modal(
        document.getElementById("modalExcluirPost")
    );

document
.querySelectorAll(".btnExcluirPost")
.forEach(botao=>{

    botao.onclick=function(){

        document
        .getElementById("btnConfirmarExcluir")
        .href="/deletePostAdmin?id="+this.dataset.id;

        modalExcluir.show();

    }

});
let modalBloquear =
    new bootstrap.Modal(
        document.getElementById("modalBloquearUsuario")
    );

document
.querySelectorAll(".btnBloquearUsuario")
.forEach(botao=>{

    botao.onclick=function(){

        document
        .getElementById("nomeUsuarioBloquear")
        .innerText=this.dataset.nome;

        document
        .getElementById("btnConfirmarBloqueio")
        .href="/blockUser?id="+this.dataset.id;

        modalBloquear.show();

    }

});