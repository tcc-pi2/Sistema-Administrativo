function adicionarCategoria() {
    const nome = document.getElementById ('nome-categoria').value
    if ( nome ==='') {
    window.alert ('Por favor, preencha o nome da categoria!');
    return
     }
     else {
        window.alert ('Parabéns! Categoria adicionada.');
     }
}