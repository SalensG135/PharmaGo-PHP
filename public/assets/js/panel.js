// Único JS del panel: confirmación antes de eliminar (equivale al confirm() de controlador.js).
document.addEventListener('submit', (e) => {
  const msg = e.target.dataset && e.target.dataset.confirmar;
  if (msg && !window.confirm(msg)) e.preventDefault();
});
