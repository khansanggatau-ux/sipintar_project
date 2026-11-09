function openModal(id){
  var el = document.getElementById(id);
  if(el){ el.classList.add('show'); }
}
function closeModal(id){
  var el = document.getElementById(id);
  if(el){ el.classList.remove('show'); }
}
document.addEventListener('click', function(e){
  if(e.target.classList && e.target.classList.contains('overlay')){
    e.target.classList.remove('show');
  }
});