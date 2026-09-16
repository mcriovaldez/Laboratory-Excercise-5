const PRODUCTS = [
    { id: 1, name: "[Product]", category: "T-Shirts", price: 24.99, color: "clay", code: "01" },
    { id: 2, name: "[Product]", category: "Hoodies", price: 59.99, color: "forest", code: "02" },
    { id: 3, name: "[Product]", category: "Pants", price: 64.99, color: "olive", code: "03" },
    { id: 4, name: "[Product]", category: "Jackets", price: 89.99, color: "ink", code: "04" },
    { id: 5, name: "[Product]", category: "Shorts", price: 34.99, color: "sky", code: "05" },
    { id: 6, name: "[Product]", category: "Accessories", price: 19.99, color: "oat", code: "06" }
];

const CART_KEY = "store-cart";
const getCart = () => JSON.parse(localStorage.getItem(CART_KEY) || "[]");
const saveCart = (cart) => localStorage.setItem(CART_KEY, JSON.stringify(cart));

function updateCartCount() {
    const count = getCart().reduce((total, item) => total + item.quantity, 0);
    document.querySelectorAll(".cart-count").forEach((element) => { element.textContent = count; });
}

function showNotice(message) {
    const notice = document.querySelector(".notice");
    if (!notice) return;
    notice.textContent = message;
    notice.classList.add("visible");
    window.setTimeout(() => notice.classList.remove("visible"), 1800);
}

function addToCart(id) {
    const cart = getCart();
    const item = cart.find((entry) => entry.id === id);
    if (item) item.quantity += 1;
    else cart.push({ id, quantity: 1 });
    saveCart(cart);
    updateCartCount();
    showNotice("Added to your bag");
}

function productMarkup(product) {
    return `<article class="product-card">
        <div class="product-art ${product.color}"><span>${product.category}</span></div>
        <div class="product-info"><p class="eyebrow">${product.category}</p><h3>${product.name}</h3><strong>$${product.price.toFixed(2)}</strong></div>
        <button class="product-action" type="button" data-add="${product.id}">Add to bag <span aria-hidden="true">+</span></button>
    </article>`;
}

function renderProducts(container, products) {
    container.innerHTML = products.map(productMarkup).join("");
    container.querySelectorAll("[data-add]").forEach((button) => {
        button.addEventListener("click", () => addToCart(Number(button.dataset.add)));
    });
}

function initCatalog() {
    const grid = document.querySelector("[data-products]");
    if (!grid) return;
    const requestedCategory = new URLSearchParams(window.location.search).get("category");
    const initialCategory = PRODUCTS.some((product) => product.category === requestedCategory) ? requestedCategory : "All";
    const initialButton = document.querySelector(`[data-filter="${initialCategory}"]`);
    if (initialButton) initialButton.classList.add("selected");
    renderProducts(grid, initialCategory === "All" ? PRODUCTS : PRODUCTS.filter((product) => product.category === initialCategory));
    document.querySelectorAll("[data-filter]").forEach((button) => button.addEventListener("click", () => {
        document.querySelectorAll("[data-filter]").forEach((item) => item.classList.remove("selected"));
        button.classList.add("selected");
        const category = button.dataset.filter;
        renderProducts(grid, category === "All" ? PRODUCTS : PRODUCTS.filter((product) => product.category === category));
    }));
}

function renderCart() {
    const list = document.querySelector(".cart-list");
    const summary = document.querySelector(".cart-summary");
    if (!list || !summary) return;
    const entries = getCart().map((item) => Object.assign({}, item, { product: PRODUCTS.find((product) => product.id === item.id) })).filter((item) => item.product);
    if (!entries.length) {
        list.innerHTML = '<div class="empty-state"><span class="empty-mark">+</span><h2>Empty bag</h2><p>Add a product.</p><a class="solid-button" href="dashboard.html">Shop</a></div>';
        summary.innerHTML = "";
        return;
    }
    list.innerHTML = entries.map(({ product, quantity }) => `<article class="cart-item">
        <div class="product-art mini ${product.color}"><span>${product.code}</span></div>
        <div class="cart-item-info"><p class="eyebrow">${product.category}</p><h3>${product.name}</h3><p>$${product.price.toFixed(2)}</p><button class="text-button" type="button" data-remove="${product.id}">Remove</button></div>
        <label class="quantity-label" for="quantity-${product.id}">Qty <input id="quantity-${product.id}" class="quantity" type="number" min="1" value="${quantity}" data-quantity="${product.id}"></label>
    </article>`).join("");
    const subtotal = entries.reduce((total, item) => total + item.product.price * item.quantity, 0);
    summary.innerHTML = `<div class="summary-head"><h2>Summary</h2><span>${entries.length} item${entries.length === 1 ? "" : "s"}</span></div><p><span>Subtotal</span><strong>$${subtotal.toFixed(2)}</strong></p><p><span>Shipping</span><span>Free</span></p><hr><p class="summary-total"><span>Total</span><strong>$${subtotal.toFixed(2)}</strong></p><button class="solid-button full" type="button" data-checkout>Checkout</button><small>Taxes calculated at checkout.</small>`;
    list.querySelectorAll("[data-remove]").forEach((button) => button.addEventListener("click", () => {
        saveCart(getCart().filter((item) => item.id !== Number(button.dataset.remove)));
        renderCart();
        updateCartCount();
    }));
    list.querySelectorAll("[data-quantity]").forEach((input) => input.addEventListener("change", () => {
        const cart = getCart();
        const item = cart.find((entry) => entry.id === Number(input.dataset.quantity));
        if (item) item.quantity = Math.max(1, Number(input.value) || 1);
        saveCart(cart);
        renderCart();
        updateCartCount();
    }));
    summary.querySelector("[data-checkout]").addEventListener("click", () => showNotice("Checkout is ready for your next step"));
}

function initNavigation() {
    const header = document.querySelector(".site-header");
    if (!header) return;
    let lastScroll = window.scrollY;
    window.addEventListener("scroll", () => {
        const currentScroll = window.scrollY;
        if (currentScroll <= 10 || currentScroll < lastScroll) header.classList.remove("nav-hidden");
        else if (currentScroll > lastScroll + 8) header.classList.add("nav-hidden");
        lastScroll = currentScroll;
    }, { passive: true });
}

function initBackToTop() {
    const button = document.createElement("button");
    button.className = "back-to-top";
    button.type = "button";
    button.setAttribute("aria-label", "Back to top");
    button.innerHTML = "&uarr;";
    document.body.appendChild(button);
    window.addEventListener("scroll", () => {
        button.classList.toggle("visible", window.scrollY > 260);
    }, { passive: true });
    button.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
}

document.addEventListener("DOMContentLoaded", () => {
    updateCartCount();
    initCatalog();
    renderCart();
    initNavigation();
    initBackToTop();
});
