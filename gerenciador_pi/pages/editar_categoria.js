function alterarCategoria () {
    const nome = document.getElementById('nome-categoria').value;
    if (!nome.trim()) {
        alert ('Digite um nome para a categoria!');
        return;

    }
    //chamar futuramente para api/php
    console.log('Categoria alterada para:', nome);
    alert('Categoria adicionada com sucesso!');
}

const categorias = [
    {id: 0, nome: "Vestidos de Gala"},
    {id: 1, nome: "Bolsas de Couro"},
    {id: 2, nome: "Sapatos de Salto"},
    {id: 3, nome: "Joalheria Fina"},
    {id: 4, nome: "Acessórios de Verão"} 
];
//busca pelo índice
function carregarCategoria () {
    const categoria =categorias [id];
    //caso não encontrado, avisa e volta p lista
    if (!categoria) {
        alert('Categoria não encontrada!');
        window.location.href = './lista-de-categoria.html';
        return;
    }
    document.getElementById('nome-categoria').value = categoria.nome;

    // mostra o ID na tela
    document.querySelector('.cartao__subtitulo').textContent =
        `ID da Categoria: #${categoria.id}`;

    // atualiza o topo
    document.querySelector('.caminho__item--atual').textContent =
        `Editar: ${categoria.nome}`;
        
        function  alterarCategoria () {
            const novoNome = document.getElementeById('nome-categoria').value;
            if (!novoNome.trim()) {
                alert('Digite um nome para a categoria!');
                return;
            }
            alert(Categoria)
        }
}