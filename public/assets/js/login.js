// Mejora progresiva: resalta el rol elegido (el servidor valida el rol real).
const radios = document.querySelectorAll('.rol input');
const pintar = () => {
  radios.forEach((r) => r.closest('.rol').classList.toggle('activo', r.checked));
  const marcado = document.querySelector('.rol input:checked');
  if (marcado) document.getElementById('textoRol').textContent = 'Ingresando como ' + marcado.value;
};
radios.forEach((r) => r.addEventListener('change', pintar));
pintar();
