document.addEventListener('DOMContentLoaded', () => {
  const categoryChecks = Array.from(document.querySelectorAll('.filter-category'));
  const brandChecks = Array.from(document.querySelectorAll('.filter-brand'));
  const cards = Array.from(document.querySelectorAll('#productGrid .product-card'));
  const clearBtn = document.getElementById('clearFilters');
  const searchInput = document.getElementById('searchInput');
  const minPrice = document.getElementById('minPrice');
  const maxPrice = document.getElementById('maxPrice');
  const applyPrice = document.getElementById('applyPrice');
  const sortSelect = document.getElementById('sortSelect');
  const grid = document.getElementById('productGrid');
  const loadMoreBtn = document.getElementById('loadMore');
  const qvModalEl = document.getElementById('quickViewModal');
  let qvModal;
  if (qvModalEl) { qvModal = new bootstrap.Modal(qvModalEl); }

  const applyFilters = () => {
    const activeCats = categoryChecks.filter(c => c.checked).map(c => c.value.toLowerCase());
    const activeBrands = brandChecks.filter(b => b.checked).map(b => b.value.toLowerCase());
    const q = (searchInput?.value || '').trim().toLowerCase();
    const min = parseFloat(minPrice?.value || '');
    const max = parseFloat(maxPrice?.value || '');

    cards.forEach(card => {
      const cat = card.getAttribute('data-category').toLowerCase();
      const brand = card.getAttribute('data-brand').toLowerCase();
      const name = card.getAttribute('data-name').toLowerCase();
      const price = parseFloat(card.getAttribute('data-price'));

      const catOk = activeCats.length === 0 || activeCats.includes(cat);
      const brandOk = activeBrands.length === 0 || activeBrands.includes(brand);
      const textOk = !q || name.includes(q) || brand.includes(q) || cat.includes(q);
      const minOk = isNaN(min) || price >= min;
      const maxOk = isNaN(max) || price <= max;

      const show = catOk && brandOk && textOk && minOk && maxOk;
      card.style.display = show ? '' : 'none';
    });
  };

  [...categoryChecks, ...brandChecks].forEach(el => el.addEventListener('change', applyFilters));
  searchInput?.addEventListener('input', applyFilters);
  applyPrice?.addEventListener('click', applyFilters);

  clearBtn?.addEventListener('click', () => {
    [...categoryChecks, ...brandChecks].forEach(el => { el.checked = false; });
    if (searchInput) searchInput.value = '';
    if (minPrice) minPrice.value = '';
    if (maxPrice) maxPrice.value = '';
    applyFilters();
  });

  // Ordenamiento
  sortSelect?.addEventListener('change', () => {
    const val = sortSelect.value;
    const visibleCards = Array.from(grid.children).filter(c => c.style.display !== 'none');
    const sortFn = {
      'price_asc': (a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price),
      'price_desc': (a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price),
      'name_asc': (a, b) => a.dataset.name.localeCompare(b.dataset.name),
      'name_desc': (a, b) => b.dataset.name.localeCompare(a.dataset.name),
      'brand_asc': (a, b) => a.dataset.brand.localeCompare(b.dataset.brand),
      'brand_desc': (a, b) => b.dataset.brand.localeCompare(a.dataset.brand),
    }[val];
    if (!sortFn) return;
    visibleCards.sort(sortFn).forEach(card => grid.appendChild(card));
  });

  // Vista rápida
  document.querySelectorAll('.btn-quickview').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const card = e.target.closest('.product-card');
      if (!card || !qvModal) return;
      const name = card.dataset.name;
      const brand = card.dataset.brand;
      const cat = card.dataset.category;
      const price = parseFloat(card.dataset.price);
      const image = card.dataset.image;
      const stock = parseInt(card.dataset.stock, 10);
      const id = card.dataset.id;

      document.getElementById('qvTitle').textContent = name;
      const imgEl = document.getElementById('qvImage');
      imgEl.src = image || 'assets/placeholder.svg';
      document.getElementById('qvMeta').textContent = `Marca: ${brand} • Cat: ${cat}`;
      document.getElementById('qvPrice').textContent = `S/ ${price.toFixed(2)}`;
      document.getElementById('qvStock').textContent = `Stock: ${stock}`;
      document.getElementById('qvId').value = id;
      const addBtn = document.getElementById('qvAddBtn');
      addBtn.disabled = stock <= 0 || card.dataset.status === 'agotado';
      qvModal.show();
    });
  });

  // Cargar más
  const INITIAL_SHOW = 6;
  const allCards = Array.from(grid.children);
  let hidden = false;
  if (allCards.length > INITIAL_SHOW) {
    allCards.slice(INITIAL_SHOW).forEach(c => { c.style.display = 'none'; c.classList.add('hidden-initial'); });
    hidden = true;
  }
  loadMoreBtn?.addEventListener('click', () => {
    if (!hidden) return;
    document.querySelectorAll('#productGrid .hidden-initial').forEach(c => { c.style.display = ''; c.classList.remove('hidden-initial'); });
    hidden = false;
    loadMoreBtn.style.display = 'none';
  });
});