const TotemStore = (() => {
  /*
    Este arquivo e o "banco de dados" do projeto enquanto nao tem PHP/MySQL.
    Tudo fica salvo no localStorage do navegador.

    Para mexer manualmente:
    - nome, frase e logo: procure por settings
    - categorias: procure por categories
    - produtos e fotos: procure por products
    - pedidos de exemplo: procure por orders

    Alguns campos estao em ingles porque sao os nomes internos dos dados.
    Eu deixei assim para nao quebrar o que ja esta salvo no navegador.
  */

  // Chaves usadas para salvar tudo no navegador.
  const CHAVE_DADOS_BASE = "gastroTechDadosV1";
  const CHAVE_CONTA_ATUAL = "gastroTechContaAtual";
  const CONTA_PADRAO = "admin";

  // Dados iniciais do sistema. Se limpar o navegador, ele volta para isso aqui.
  const dadosIniciais = {
    next: {
      category: 7,
      product: 7,
      admin: 5,
      order: 105,
      movement: 3
    },
    // Nome, frase e logo que aparecem primeiro no sistema.
    // Pelo dashboard da para trocar isso sem mexer no codigo.
    settings: {
      waitTime: 18,
      storeName: "GastroTech",
      storeSubtitle: "Monte seu pedido com todos os detalhes.",
      storeLogo: "../assets/brand/gastrotech-logo.jpg"
    },
    cash: {
      openingBalance: 150,
      status: "Aberto",
      openedAt: new Date(Date.now() - 1000 * 60 * 60 * 5).toISOString()
    },
    categories: [
      { id: 1, name: "Combos", description: "Pedidos completos com lanche, acompanhamento e bebida", order: 1, status: "Ativo" },
      { id: 2, name: "Hambúrgueres", description: "Lanches artesanais preparados na chapa", order: 2, status: "Ativo" },
      { id: 3, name: "Porções", description: "Acompanhamentos para dividir ou turbinar o pedido", order: 3, status: "Ativo" },
      { id: 4, name: "Bebidas", description: "Bebidas geladas para acompanhar o lanche", order: 4, status: "Ativo" },
      { id: 5, name: "Sobremesas", description: "Doces e shakes para fechar o pedido", order: 5, status: "Ativo" },
      { id: 6, name: "Adicionais", description: "Molhos, extras e complementos", order: 6, status: "Ativo" }
    ],
    /*
      Produtos iniciais do cardapio.
      Para trocar uma foto manualmente, altere o campo image.
      Exemplo: image: "../assets/images/menu/combo 2.jpg"

      Traducao dos campos mais usados:
      name = nome, description = descricao, image = imagem,
      stock = estoque, price = preco, ingredients = ingredientes.
    */
    products: [
      {
        id: 1,
        name: "Combo Smash",
        categoryId: 1,
        description: "Smash burger na chapa, batata sequinha e refrigerante gelado.",
        options: ["Burger", "Batata", "Refri ou suco"],
        customizations: [
          {
            key: "bebida",
            label: "Bebida do combo",
            choices: ["Coca-Cola", "Guarana", "Fanta Laranja", "Suco de laranja", "Suco de uva"]
          },
          {
            key: "gelo",
            label: "Gelo",
            choices: ["Com gelo", "Sem gelo"]
          }
        ],
        ingredients: ["pão brioche", "blend bovino 120g", "cheddar", "picles", "molho especial", "batata palito", "refrigerante lata"],
        tags: ["Mais vendido", "Completo"],
        allergens: ["glúten", "leite"],
        portion: "1 lanche + batata + bebida",
        calories: 920,
        image: "../assets/images/menu/combo 2.jpg",
        stock: 24,
        price: 32.9,
        prepTime: 18,
        icon: "fa-burger",
        status: "Ativo",
        featured: true
      },
      {
        id: 2,
        name: "X-Bacon Artesanal",
        categoryId: 2,
        description: "Pão brioche tostado, bacon crocante, cheddar e molho da casa.",
        options: ["Pão brioche", "Bacon", "Cheddar"],
        ingredients: ["pão brioche", "hambúrguer artesanal", "bacon em tiras", "queijo cheddar", "cebola caramelizada", "molho da casa"],
        tags: ["Artesanal", "Chapa"],
        allergens: ["glúten", "leite"],
        portion: "1 lanche artesanal",
        calories: 680,
        image: "../assets/images/menu/x-bacon-artesanal.jpg",
        stock: 18,
        price: 24.9,
        prepTime: 16,
        icon: "fa-burger",
        status: "Ativo",
        featured: true
      },
      {
        id: 3,
        name: "Batata Suprema",
        categoryId: 3,
        description: "Batata crocante coberta com cheddar cremoso e bacon.",
        options: ["Cheddar", "Bacon", "Média"],
        ingredients: ["batata palito", "creme de cheddar", "bacon crocante", "cebolinha", "tempero da casa"],
        tags: ["Compartilhar", "Crocante"],
        allergens: ["leite"],
        portion: "porção média 350g",
        calories: 540,
        image: "../assets/images/menu/batata-suprema.jpg",
        stock: 12,
        price: 18,
        prepTime: 10,
        icon: "fa-bowl-food",
        status: "Ativo",
        featured: false
      },
      {
        id: 4,
        name: "Bebida Gelada",
        categoryId: 4,
        description: "Refrigerante ou suco gelado para acompanhar qualquer pedido.",
        options: ["Coca-Cola", "Guarana", "Fanta", "Suco"],
        customizations: [
          {
            key: "bebida",
            label: "Sabor da bebida",
            choices: ["Coca-Cola", "Guarana", "Fanta Laranja", "Suco de laranja", "Suco de uva"]
          },
          {
            key: "gelo",
            label: "Gelo",
            choices: ["Com gelo", "Sem gelo"]
          }
        ],
        ingredients: ["bebida escolhida", "gelo opcional"],
        tags: ["Gelado", "Rápido"],
        allergens: [],
        portion: "lata 350ml ou copo 500ml",
        calories: 140,
        image: "../assets/images/menu/bebida-gelada.jpg",
        stock: 42,
        price: 6,
        prepTime: 2,
        icon: "fa-glass-water",
        status: "Ativo",
        featured: false
      },
      {
        id: 5,
        name: "Milkshake Chocolate",
        categoryId: 5,
        description: "Milkshake cremoso de chocolate com chantilly e calda.",
        options: ["400ml", "Chocolate"],
        ingredients: ["sorvete de chocolate", "leite", "calda de chocolate", "chantilly", "raspas de chocolate"],
        tags: ["Cremoso", "Doce"],
        allergens: ["leite"],
        portion: "copo 400ml",
        calories: 460,
        image: "../assets/images/menu/milkshake-chocolate.jpg",
        stock: 7,
        price: 16.9,
        prepTime: 8,
        icon: "fa-ice-cream",
        status: "Ativo",
        featured: true
      },
      {
        id: 6,
        name: "Molho Extra",
        categoryId: 6,
        description: "Pote extra para escolher barbecue defumado ou maionese verde.",
        options: ["Barbecue", "Maionese verde"],
        ingredients: ["barbecue defumado", "maionese verde", "ervas frescas", "especiarias"],
        tags: ["Extra", "Molhos"],
        allergens: ["ovo"],
        portion: "pote 40ml",
        calories: 95,
        image: "../assets/images/menu/molho-extra.jpg",
        stock: 35,
        price: 3.5,
        prepTime: 1,
        icon: "fa-droplet",
        status: "Ativo",
        featured: false
      }
    ],
    admins: [
      { id: 1, name: "Usuário Admin", email: "admin@gastrotech.com", role: "Administrador", status: "Ativo" },
      { id: 2, name: "Marina Costa", email: "marina@gastrotech.com", role: "Cardápio", status: "Ativo" },
      { id: 3, name: "Rafael Lima", email: "rafael@gastrotech.com", role: "Atendimento", status: "Ativo" },
      { id: 4, name: "Ana Souza", email: "ana@gastrotech.com", role: "Leitura", status: "Inativo" }
    ],
    orders: [
      {
        id: 102,
        code: "A102",
        customer: "Cliente Totem",
        payment: "Pix",
        status: "Recebido",
        total: 38.9,
        prepTime: 18,
        createdAt: new Date(Date.now() - 1000 * 60 * 26).toISOString(),
        items: [
          { productId: 1, name: "Combo Smash", quantity: 1, price: 32.9 },
          { productId: 4, name: "Bebida Gelada", quantity: 1, price: 6, notes: "Sabor da bebida: Guarana • Gelo: Sem gelo" }
        ]
      },
      {
        id: 103,
        code: "A103",
        customer: "Pedido Balcão",
        payment: "Cartão",
        status: "Em preparo",
        total: 49.8,
        prepTime: 16,
        createdAt: new Date(Date.now() - 1000 * 60 * 14).toISOString(),
        items: [
          { productId: 2, name: "X-Bacon Artesanal", quantity: 2, price: 24.9 }
        ]
      },
      {
        id: 104,
        code: "A104",
        customer: "Retirada Rápida",
        payment: "Pix",
        status: "Pronto",
        total: 32.9,
        prepTime: 18,
        createdAt: new Date(Date.now() - 1000 * 60 * 6).toISOString(),
        items: [
          { productId: 1, name: "Combo Smash", quantity: 1, price: 32.9 }
        ]
      }
    ],
    movements: [
      {
        id: 1,
        type: "Entrada",
        description: "Troco inicial reforcado",
        amount: 50,
        payment: "Dinheiro",
        createdAt: new Date(Date.now() - 1000 * 60 * 60 * 4).toISOString()
      },
      {
        id: 2,
        type: "Saida",
        description: "Compra de embalagens",
        amount: 22.5,
        payment: "Dinheiro",
        createdAt: new Date(Date.now() - 1000 * 60 * 58).toISOString()
      }
    ]
  };

  function clonar(value) {
    return JSON.parse(JSON.stringify(value));
  }

  // Deixa o nome da conta seguro para virar chave no localStorage.
  function normalizarConta(conta) {
    const texto = String(conta || CONTA_PADRAO).trim().toLowerCase();
    if (texto === "admin@gastrotech.com") return CONTA_PADRAO;
    if (texto === "admin") return CONTA_PADRAO;
    return texto.replace(/[^a-z0-9._-]/g, "-") || CONTA_PADRAO;
  }

  function definirContaAtual(conta) {
    const contaNormalizada = normalizarConta(conta);
    localStorage.setItem(CHAVE_CONTA_ATUAL, contaNormalizada);
    return contaNormalizada;
  }

  function contaAtual() {
    return normalizarConta(localStorage.getItem(CHAVE_CONTA_ATUAL) || CONTA_PADRAO);
  }

  function chaveDadosPorConta(conta = contaAtual()) {
    return `${CHAVE_DADOS_BASE}:${normalizarConta(conta)}`;
  }

  function chaveDadosAtual() {
    return chaveDadosPorConta(contaAtual());
  }

  // Leva os dados antigos para a conta admin sem apagar nada.
  function migrarDadosAntigosParaAdmin() {
    const chaveAdmin = chaveDadosPorConta(CONTA_PADRAO);
    const dadosDaContaAdmin = localStorage.getItem(chaveAdmin);
    const dadosAntigos = localStorage.getItem(CHAVE_DADOS_BASE);

    if (!dadosDaContaAdmin && dadosAntigos) {
      localStorage.setItem(chaveAdmin, dadosAntigos);
    }
  }

  // Confere se a imagem salva no navegador ainda e da versao antiga.
  function imagemAntiga(caminho) {
    const texto = String(caminho || "").toLowerCase();
    return !texto || texto.includes(".svg") || texto.includes("svg+xml") || texto.includes("image/svg") || texto.includes("saborflow") || texto.includes("refrigerante-lata");
  }

  // Atualiza dados antigos que ja estavam salvos no navegador.
  function aplicarMigracoes(data) {
    const produtosPadrao = new Map(dadosIniciais.products.map((produto) => [Number(produto.id), produto]));
    const versaoMigracao = Number(data.migrationVersion || 0);
    const precisaCorrigirImagens = (data.products || []).some((produto) => {
      const padrao = produtosPadrao.get(Number(produto.id));
      return padrao && imagemAntiga(produto.image);
    });
    const precisaAtualizarCombo = versaoMigracao < 3;

    if (versaoMigracao >= 3 && !precisaCorrigirImagens) return data;

    data.settings = {
      ...dadosIniciais.settings,
      ...(data.settings || {})
    };

    if (imagemAntiga(data.settings.storeLogo)) {
      data.settings.storeLogo = dadosIniciais.settings.storeLogo;
    }

    data.products = (data.products || dadosIniciais.products).map((produto) => {
      const padrao = produtosPadrao.get(Number(produto.id));
      if (!padrao) return produto;

      const atualizado = { ...produto };

      // Na entrega, imagens antigas sao trocadas pelas fotos da pasta.
      if (imagemAntiga(atualizado.image)) {
        atualizado.image = padrao.image;
      }

      // Troca especifica do Combo Smash para a imagem combo 2.jpg.
      if (Number(atualizado.id) === 1 && precisaAtualizarCombo && String(atualizado.image || "").includes("combo-smash.jpg")) {
        atualizado.image = padrao.image;
      }

      if (Number(atualizado.id) === 4 && atualizado.name === "Refrigerante Lata") {
        atualizado.name = padrao.name;
        atualizado.description = padrao.description;
        atualizado.options = padrao.options;
        atualizado.ingredients = padrao.ingredients;
        atualizado.tags = padrao.tags;
        atualizado.allergens = padrao.allergens;
        atualizado.portion = padrao.portion;
        atualizado.calories = padrao.calories;
        atualizado.icon = padrao.icon;
      }

      if (!Array.isArray(atualizado.customizations) || !atualizado.customizations.length) {
        atualizado.customizations = padrao.customizations || [];
      }

      return atualizado;
    });

    data.migrationVersion = 3;
    return data;
  }

  // Carrega os dados salvos no navegador.
  function carregarDados() {
    migrarDadosAntigosParaAdmin();

    const chaveConta = chaveDadosAtual();
    const raw = localStorage.getItem(chaveConta);
    if (!raw) {
      const seeded = clonar(dadosIniciais);
      localStorage.setItem(chaveConta, JSON.stringify(seeded));
      return seeded;
    }

    try {
      const data = JSON.parse(raw);
      return normalizarDados(data);
    } catch {
      const seeded = clonar(dadosIniciais);
      localStorage.setItem(chaveConta, JSON.stringify(seeded));
      return seeded;
    }
  }

  function normalizarDados(data) {
    const merged = {
      ...clonar(dadosIniciais),
      ...data,
      next: { ...dadosIniciais.next, ...(data.next || {}) },
      settings: { ...dadosIniciais.settings, ...(data.settings || {}) },
      cash: { ...dadosIniciais.cash, ...(data.cash || {}) }
    };

    aplicarMigracoes(merged);
    localStorage.setItem(chaveDadosAtual(), JSON.stringify(merged));
    return merged;
  }

  // Salva de volta no localStorage.
  function salvarDados(data) {
    localStorage.setItem(chaveDadosAtual(), JSON.stringify(data));
    return data;
  }

  function formatarDinheiro(value) {
    return Number(value || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }

  function iniciais(name) {
    return String(name || "AD")
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part[0])
      .join("")
      .toUpperCase() || "AD";
  }

  function nomeCategoria(categoryId) {
    const data = carregarDados();
    const category = data.categories.find((item) => item.id === Number(categoryId));
    return category ? category.name : "Sem categoria";
  }

  function categoriasAtivas() {
    return carregarDados().categories
      .filter((category) => category.status === "Ativo")
      .sort((a, b) => a.order - b.order || a.name.localeCompare(b.name));
  }

  function produtosAtivos() {
    return carregarDados().products
      .filter((product) => product.status === "Ativo" && Number(product.stock) > 0)
      .sort((a, b) => a.name.localeCompare(b.name));
  }

  function proximoId(data, key) {
    const id = data.next[key];
    data.next[key] += 1;
    return id;
  }

  // Cria ou atualiza categoria.
  function salvarCategoriaDados(payload) {
    const data = carregarDados();
    const id = Number(payload.id);
    const category = {
      id: id || proximoId(data, "category"),
      name: payload.name.trim(),
      description: payload.description?.trim() || "",
      order: Number(payload.order || data.categories.length + 1),
      status: payload.status || "Ativo"
    };

    const index = data.categories.findIndex((item) => item.id === category.id);
    if (index >= 0) data.categories[index] = category;
    else data.categories.push(category);

    salvarDados(data);
    return category;
  }

  function excluirCategoriaDados(id) {
    const data = carregarDados();
    const categoryId = Number(id);
    data.categories = data.categories.filter((category) => category.id !== categoryId);
    data.products = data.products.map((product) =>
      product.categoryId === categoryId ? { ...product, categoryId: null } : product
    );
    salvarDados(data);
  }

  // Cria ou atualiza produto do cardápio.
  function salvarProdutoDados(payload) {
    const data = carregarDados();
    const id = Number(payload.id);
    const antigo = data.products.find((item) => item.id === id);
    const options = Array.isArray(payload.options)
      ? payload.options
      : String(payload.options || "").split(",").map((item) => item.trim()).filter(Boolean);
    const ingredients = Array.isArray(payload.ingredients)
      ? payload.ingredients
      : String(payload.ingredients || "").split(",").map((item) => item.trim()).filter(Boolean);
    const tags = Array.isArray(payload.tags)
      ? payload.tags
      : String(payload.tags || "").split(",").map((item) => item.trim()).filter(Boolean);
    const allergens = Array.isArray(payload.allergens)
      ? payload.allergens
      : String(payload.allergens || "").split(",").map((item) => item.trim()).filter(Boolean);

    const product = {
      id: id || proximoId(data, "product"),
      name: payload.name.trim(),
      categoryId: Number(payload.categoryId) || null,
      description: payload.description?.trim() || "",
      options,
      // Algumas bebidas abrem escolhas no totem, como sabor e gelo.
      customizations: Array.isArray(payload.customizations) ? payload.customizations : (antigo?.customizations || []),
      ingredients,
      tags,
      allergens,
      portion: payload.portion?.trim() || "",
      calories: Math.max(0, Number(payload.calories || 0)),
      image: payload.image?.trim() || "",
      stock: Math.max(0, Number(payload.stock || 0)),
      price: Math.max(0, Number(payload.price || 0)),
      prepTime: Math.max(1, Number(payload.prepTime || 10)),
      icon: payload.icon || "fa-burger",
      status: payload.status || "Ativo",
      featured: Boolean(payload.featured)
    };

    const index = data.products.findIndex((item) => item.id === product.id);
    if (index >= 0) data.products[index] = product;
    else data.products.push(product);

    salvarDados(data);
    return product;
  }

  function excluirProdutoDados(id) {
    const data = carregarDados();
    data.products = data.products.filter((product) => product.id !== Number(id));
    salvarDados(data);
  }

  // Cria ou atualiza usuário do painel.
  function salvarAdminDados(payload) {
    const data = carregarDados();
    const id = Number(payload.id);
    const admin = {
      id: id || proximoId(data, "admin"),
      name: payload.name.trim(),
      email: payload.email.trim().toLowerCase(),
      role: payload.role || "Leitura",
      status: payload.status || "Ativo"
    };

    const index = data.admins.findIndex((item) => item.id === admin.id);
    if (index >= 0) data.admins[index] = admin;
    else data.admins.push(admin);

    salvarDados(data);
    return admin;
  }

  function alternarStatusAdmin(id) {
    const data = carregarDados();
    const admin = data.admins.find((item) => item.id === Number(id));
    if (admin) admin.status = admin.status === "Ativo" ? "Inativo" : "Ativo";
    salvarDados(data);
    return admin;
  }

  function excluirAdminDados(id) {
    const data = carregarDados();
    data.admins = data.admins.filter((admin) => admin.id !== Number(id));
    salvarDados(data);
  }

  // Cria pedido novo vindo do totem.
  function criarPedido(payload) {
    const data = carregarDados();
    const id = proximoId(data, "order");
    const order = {
      id,
      code: `A${id}`,
      customer: payload.customer || "Cliente",
      payment: payload.payment,
      status: "Recebido",
      total: Number(payload.total || 0),
      prepTime: Number(payload.prepTime || 0),
      createdAt: new Date().toISOString(),
      items: payload.items.map((item) => ({
        productId: item.product.id,
        name: item.product.name,
        quantity: item.quantity,
        price: item.product.price,
        options: item.options || {},
        notes: item.notes || ""
      }))
    };

    order.items.forEach((item) => {
      const product = data.products.find((candidate) => candidate.id === item.productId);
      if (product) product.stock = Math.max(0, Number(product.stock) - Number(item.quantity));
    });

    data.orders.unshift(order);
    salvarDados(data);
    return order;
  }

  // Atualiza o status usado pela cozinha.
  function atualizarStatusPedido(id, status) {
    const data = carregarDados();
    const order = data.orders.find((item) => item.id === Number(id));
    if (order) order.status = status;
    salvarDados(data);
    return order;
  }

  function pegarTempoEspera() {
    return Math.max(1, Number(carregarDados().settings?.waitTime || dadosIniciais.settings.waitTime));
  }

  // Configurações da loja: nome, frase, logo e tempo.
  function pegarConfiguracoes() {
    return {
      ...dadosIniciais.settings,
      ...(carregarDados().settings || {})
    };
  }

  // Aplica nome e logo nas telas que tiverem esses elementos.
  function aplicarMarca() {
    const settings = pegarConfiguracoes();

    document.querySelectorAll("[data-store-logo], .brand-card__avatar").forEach((image) => {
      image.src = settings.storeLogo || dadosIniciais.settings.storeLogo;
      image.alt = `Logo ${settings.storeName || "GastroTech"}`;
    });

    document.querySelectorAll("[data-store-name], .brand-card__name").forEach((element) => {
      element.textContent = settings.storeName || dadosIniciais.settings.storeName;
    });

    document.querySelectorAll("[data-store-subtitle]").forEach((element) => {
      element.textContent = settings.storeSubtitle || dadosIniciais.settings.storeSubtitle;
    });
  }

  // Salva configurações gerais da loja.
  function atualizarConfiguracoes(payload) {
    const data = carregarDados();
    data.settings = {
      ...data.settings,
      waitTime: Math.max(1, Number(payload.waitTime || data.settings?.waitTime || dadosIniciais.settings.waitTime)),
      storeName: payload.storeName?.trim() || data.settings?.storeName || dadosIniciais.settings.storeName,
      storeSubtitle: payload.storeSubtitle?.trim() || data.settings?.storeSubtitle || dadosIniciais.settings.storeSubtitle,
      storeLogo: payload.storeLogo?.trim() || data.settings?.storeLogo || dadosIniciais.settings.storeLogo
    };
    salvarDados(data);
    return data.settings;
  }

  function atualizarCaixa(payload) {
    const data = carregarDados();
    data.cash = {
      ...data.cash,
      openingBalance: Math.max(0, Number(payload.openingBalance || 0)),
      status: payload.status || data.cash?.status || "Aberto",
      openedAt: data.cash?.openedAt || new Date().toISOString()
    };
    salvarDados(data);
    return data.cash;
  }

  function adicionarMovimentoCaixa(payload) {
    const data = carregarDados();
    const movement = {
      id: proximoId(data, "movement"),
      type: payload.type === "Saida" ? "Saida" : "Entrada",
      description: payload.description?.trim() || "Movimento de caixa",
      amount: Math.max(0, Number(payload.amount || 0)),
      payment: payload.payment || "Dinheiro",
      createdAt: new Date().toISOString()
    };

    data.movements.unshift(movement);
    salvarDados(data);
    return movement;
  }

  function excluirMovimentoCaixa(id) {
    const data = carregarDados();
    data.movements = data.movements.filter((movement) => movement.id !== Number(id));
    salvarDados(data);
  }

  function reiniciarDados() {
    const seeded = clonar(dadosIniciais);
    localStorage.setItem(chaveDadosAtual(), JSON.stringify(seeded));
    return seeded;
  }

  return {
    CHAVE_DADOS_BASE,
    CHAVE_CONTA_ATUAL,
    CONTA_PADRAO,
    contaAtual,
    definirContaAtual,
    chaveDadosAtual,
    chaveDadosPorConta,
    get CHAVE_DADOS() {
      return chaveDadosAtual();
    },
    // Nomes novos em portugues, usados pelos outros arquivos.
    carregarDados,
    salvarDados,
    reiniciarDados,
    formatarDinheiro,
    iniciais,
    nomeCategoria,
    pegarConfiguracoes,
    aplicarMarca,
    categoriasAtivas,
    produtosAtivos,
    salvarCategoriaDados,
    excluirCategoriaDados,
    salvarProdutoDados,
    excluirProdutoDados,
    salvarAdminDados,
    alternarStatusAdmin,
    excluirAdminDados,
    criarPedido,
    atualizarStatusPedido,
    pegarTempoEspera,
    atualizarConfiguracoes,
    atualizarCaixa,
    adicionarMovimentoCaixa,
    excluirMovimentoCaixa,
    // Apelidos antigos. Deixei aqui para alguma pagina antiga nao quebrar.
    get KEY() {
      return chaveDadosAtual();
    },
    load: carregarDados,
    save: salvarDados,
    reset: reiniciarDados,
    money: formatarDinheiro,
    initials: iniciais,
    categoryName: nomeCategoria,
    getSettings: pegarConfiguracoes,
    applyBranding: aplicarMarca,
    activeCategories: categoriasAtivas,
    activeProducts: produtosAtivos,
    upsertCategory: salvarCategoriaDados,
    deleteCategory: excluirCategoriaDados,
    upsertProduct: salvarProdutoDados,
    deleteProduct: excluirProdutoDados,
    upsertAdmin: salvarAdminDados,
    toggleAdminStatus: alternarStatusAdmin,
    deleteAdmin: excluirAdminDados,
    createOrder: criarPedido,
    updateOrderStatus: atualizarStatusPedido,
    getWaitTime: pegarTempoEspera,
    updateSettings: atualizarConfiguracoes,
    updateCash: atualizarCaixa,
    addCashMovement: adicionarMovimentoCaixa,
    deleteCashMovement: excluirMovimentoCaixa
  };
})();
