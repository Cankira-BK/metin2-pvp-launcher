document.addEventListener('DOMContentLoaded', async () => {
  const grid = document.querySelector('.vehicles-grid');
  if (!grid) return;

  try {
    // Tam yol ile dene
    const response = await fetch('/admin/vehicles.json');
    
    if (!response.ok) {
      throw new Error('JSON dosyası yüklenemedi');
    }
    
    const vehicles = await response.json();
    
    if (!vehicles || vehicles.length === 0) {
      grid.innerHTML = '<p style="text-align: center; grid-column: 1 / -1; color: #666;">Yakında yeni araçlar eklenecek...</p>';
      return;
    }
    
    // En yeni ilanlar üstte
    vehicles.sort((a, b) => new Date(b.date) - new Date(a.date));
    
    // İlk 3 aracı al
    const latest = vehicles.slice(0, 3);
    
    grid.innerHTML = latest.map(v => `
      <div class="vehicle-card">
        <div class="vehicle-image">
          <img src="${v.image}" alt="${v.title}">
        </div>
        <div class="vehicle-info">
          <h3>${v.title}</h3>
          <div class="vehicle-price">${v.price}</div>
          <div class="vehicle-details">
            <span>${v.year}</span>
            <span>${v.km}</span>
            <span>${v.fuel}</span>
          </div>
          <a href="${v.link}" target="_blank" class="btn" style="width: 100%; text-align: center;">Sahibinden'de Gör</a>
        </div>
      </div>
    `).join('');
  } catch (error) {
    console.error('Araçlar yüklenirken hata:', error);
    grid.innerHTML = '<p style="text-align: center; grid-column: 1 / -1; color: #666;">Araçlar yüklenirken bir sorun oluştu. Lütfen sayfayı yenileyin.</p>';
  }
});