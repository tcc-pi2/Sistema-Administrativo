const TotemStore = (() => {
  // Nome da chave usada para salvar tudo no navegador.
  const KEY = "saborFlowDadosV1";

  // Dados iniciais do sistema. Se limpar o navegador, ele volta para isso aqui.
  const initialData = {
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
      storeName: "SaborFlow",
      storeSubtitle: "Monte seu pedido com todos os detalhes.",
      storeLogo: "../assets/brand/saborflow-logo.svg"
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
    // Produtos iniciais. O campo "image" é a imagem que aparece no card.
    products: [
      {
        id: 1,
        name: "Combo Smash",
        categoryId: 1,
        description: "Smash burger na chapa, batata sequinha e refrigerante gelado.",
        options: ["Burger", "Batata", "Refri"],
        ingredients: ["pão brioche", "blend bovino 120g", "cheddar", "picles", "molho especial", "batata palito", "refrigerante lata"],
        tags: ["Mais vendido", "Completo"],
        allergens: ["glúten", "leite"],
        portion: "1 lanche + batata + bebida",
        calories: 920,
        image: "../assets/images/menu/combo-smash.svg",
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
        image: "../assets/images/menu/x-bacon-artesanal.svg",
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
        image: "../assets/images/menu/batata-suprema.svg",
        stock: 12,
        price: 18,
        prepTime: 10,
        icon: "fa-bowl-food",
        status: "Ativo",
        featured: false
      },
      {
        id: 4,
        name: "Refrigerante Lata",
        categoryId: 4,
        description: "Lata 350ml bem gelada para acompanhar qualquer pedido.",
        options: ["350ml", "Gelado"],
        ingredients: ["bebida gaseificada", "gelo opcional"],
        tags: ["Gelado", "Rápido"],
        allergens: [],
        portion: "lata 350ml",
        calories: 140,
        image: "../assets/images/menu/refrigerante-lata.svg",
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
        image: "../assets/images/menu/milkshake-chocolate.svg",
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
        image: "../assets/images/menu/molho-extra.svg",
        stock: 35,
        price: 3.5,
        prepTime: 1,
        icon: "fa-droplet",
        status: "Ativo",
        featured: false
      }
    ],
    admins: [
      { id: 1, name: "Usuário Admin", email: "admin@saborflow.com", role: "Administrador", status: "Ativo" },
      { id: 2, name: "Marina Costa", email: "marina@saborflow.com", role: "Cardápio", status: "Ativo" },
      { id: 3, name: "Rafael Lima", email: "rafael@saborflow.com", role: "Atendimento", status: "Ativo" },
      { id: 4, name: "Ana Souza", email: "ana@saborflow.com", role: "Leitura", status: "Inativo" }
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
          { productId: 4, name: "Refrigerante Lata", quantity: 1, price: 6 }
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

  function clone(value) {
    return JSON.parse(JSON.stringify(value));
  }

  // Carrega os dados salvos no navegador.
  function load() {
    const raw = localStorage.getItem(KEY);
    if (!raw) {
      const seeded = clone(initialData);
      localStorage.setItem(KEY, JSON.stringify(seeded));
      return seeded;
    }

    try {
      const data = JSON.parse(raw);
      return normalize(data);
    } catch {
      const seeded = clone(initialData);
      localStorage.setItem(KEY, JSON.stringify(seeded));
      return seeded;
    }
  }

  function normalize(data) {
    const merged = {
      ...clone(initialData),
      ...data,
      next: { ...initialData.next, ...(data.next || {}) },
      settings: { ...initialData.settings, ...(data.settings || {}) },
      cash: { ...initialData.cash, ...(data.cash || {}) }
    };

    localStorage.setItem(KEY, JSON.stringify(merged));
    return merged;
  }

  // Salva de volta no localStorage.
  function save(data) {
    localStorage.setItem(KEY, JSON.stringify(data));
    return data;
  }

  function money(value) {
    return Number(value || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }

  function initials(name) {
    return String(name || "AD")
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part[0])
      .join("")
      .toUpperCase() || "AD";
  }

  function categoryName(categoryId) {
    const data = load();
    const category = data.categories.find((item) => item.id === Number(categoryId));
    return category ? category.name : "Sem categoria";
  }

  function activeCategories() {
    return load().categories
      .filter((category) => category.status === "Ativo")
      .sort((a, b) => a.order - b.order || a.name.localeCompare(b.name));
  }

  function activeProducts() {
    return load().products
      .filter((product) => product.status === "Ativo" && Number(product.stock) > 0)
      .sort((a, b) => a.name.localeCompare(b.name));
  }

  function nextId(data, key) {
    const id = data.next[key];
    data.next[key] += 1;
    return id;
  }

  // Cria ou atualiza categoria.
  function upsertCategory(payload) {
    const data = load();
    const id = Number(payload.id);
    const category = {
      id: id || nextId(data, "category"),
      name: payload.name.trim(),
      description: payload.description?.trim() || "",
      order: Number(payload.order || data.categories.length + 1),
      status: payload.status || "Ativo"
    };

    const index = data.categories.findIndex((item) => item.id === category.id);
    if (index >= 0) data.categories[index] = category;
    else data.categories.push(category);

    save(data);
    return category;
  }

  function deleteCategory(id) {
    const data = load();
    const categoryId = Number(id);
    data.categories = data.categories.filter((category) => category.id !== categoryId);
    data.products = data.products.map((product) =>
      product.categoryId === categoryId ? { ...product, categoryId: null } : product
    );
    save(data);
  }

  // Cria ou atualiza produto do cardápio.
  function upsertProduct(payload) {
    const data = load();
    const id = Number(payload.id);
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
      id: id || nextId(data, "product"),
      name: payload.name.trim(),
      categoryId: Number(payload.categoryId) || null,
      description: payload.description?.trim() || "",
      options,
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

    save(data);
    return product;
  }

  function deleteProduct(id) {
    const data = load();
    data.products = data.products.filter((product) => product.id !== Number(id));
    save(data);
  }

  // Cria ou atualiza usuário do painel.
  function upsertAdmin(payload) {
    const data = load();
    const id = Number(payload.id);
    const admin = {
      id: id || nextId(data, "admin"),
      name: payload.name.trim(),
      email: payload.email.trim().toLowerCase(),
      role: payload.role || "Leitura",
      status: payload.status || "Ativo"
    };

    const index = data.admins.findIndex((item) => item.id === admin.id);
    if (index >= 0) data.admins[index] = admin;
    else data.admins.push(admin);

    save(data);
    return admin;
  }

  function toggleAdminStatus(id) {
    const data = load();
    const admin = data.admins.find((item) => item.id === Number(id));
    if (admin) admin.status = admin.status === "Ativo" ? "Inativo" : "Ativo";
    save(data);
    return admin;
  }

  function deleteAdmin(id) {
    const data = load();
    data.admins = data.admins.filter((admin) => admin.id !== Number(id));
    save(data);
  }

  // Cria pedido novo vindo do totem.
  function createOrder(payload) {
    const data = load();
    const id = nextId(data, "order");
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
        price: item.product.price
      }))
    };

    order.items.forEach((item) => {
      const product = data.products.find((candidate) => candidate.id === item.productId);
      if (product) product.stock = Math.max(0, Number(product.stock) - Number(item.quantity));
    });

    data.orders.unshift(order);
    save(data);
    return order;
  }

  // Atualiza o status usado pela cozinha.
  function updateOrderStatus(id, status) {
    const data = load();
    const order = data.orders.find((item) => item.id === Number(id));
    if (order) order.status = status;
    save(data);
    return order;
  }

  function getWaitTime() {
    return Math.max(1, Number(load().settings?.waitTime || initialData.settings.waitTime));
  }

  // Configurações da loja: nome, frase, logo e tempo.
  function getSettings() {
    return {
      ...initialData.settings,
      ...(load().settings || {})
    };
  }

  // Aplica nome e logo nas telas que tiverem esses elementos.
  function applyBranding() {
    const settings = getSettings();

    document.querySelectorAll("[data-store-logo], .brand-card__avatar").forEach((image) => {
      image.src = settings.storeLogo || initialData.settings.storeLogo;
      image.alt = `Logo ${settings.storeName || "SaborFlow"}`;
    });

    document.querySelectorAll("[data-store-name], .brand-card__name").forEach((element) => {
      element.textContent = settings.storeName || initialData.settings.storeName;
    });

    document.querySelectorAll("[data-store-subtitle]").forEach((element) => {
      element.textContent = settings.storeSubtitle || initialData.settings.storeSubtitle;
    });
  }

  // Salva configurações gerais da loja.
  function updateSettings(payload) {
    const data = load();
    data.settings = {
      ...data.settings,
      waitTime: Math.max(1, Number(payload.waitTime || data.settings?.waitTime || initialData.settings.waitTime)),
      storeName: payload.storeName?.trim() || data.settings?.storeName || initialData.settings.storeName,
      storeSubtitle: payload.storeSubtitle?.trim() || data.settings?.storeSubtitle || initialData.settings.storeSubtitle,
      storeLogo: payload.storeLogo?.trim() || data.settings?.storeLogo || initialData.settings.storeLogo
    };
    save(data);
    return data.settings;
  }

  function updateCash(payload) {
    const data = load();
    data.cash = {
      ...data.cash,
      openingBalance: Math.max(0, Number(payload.openingBalance || 0)),
      status: payload.status || data.cash?.status || "Aberto",
      openedAt: data.cash?.openedAt || new Date().toISOString()
    };
    save(data);
    return data.cash;
  }

  function addCashMovement(payload) {
    const data = load();
    const movement = {
      id: nextId(data, "movement"),
      type: payload.type === "Saida" ? "Saida" : "Entrada",
      description: payload.description?.trim() || "Movimento de caixa",
      amount: Math.max(0, Number(payload.amount || 0)),
      payment: payload.payment || "Dinheiro",
      createdAt: new Date().toISOString()
    };

    data.movements.unshift(movement);
    save(data);
    return movement;
  }

  function deleteCashMovement(id) {
    const data = load();
    data.movements = data.movements.filter((movement) => movement.id !== Number(id));
    save(data);
  }

  function reset() {
    const seeded = clone(initialData);
    localStorage.setItem(KEY, JSON.stringify(seeded));
    return seeded;
  }

  return {
    KEY,
    load,
    save,
    reset,
    money,
    initials,
    categoryName,
    getSettings,
    applyBranding,
    activeCategories,
    activeProducts,
    upsertCategory,
    deleteCategory,
    upsertProduct,
    deleteProduct,
    upsertAdmin,
    toggleAdminStatus,
    deleteAdmin,
    createOrder,
    updateOrderStatus,
    getWaitTime,
    updateSettings,
    updateCash,
    addCashMovement,
    deleteCashMovement
  };
})();
