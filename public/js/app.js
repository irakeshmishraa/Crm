// SmartLead CRM Pro - Main JS
const csrfToken=document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if(typeof $!=='undefined')$.ajaxSetup({headers:{'X-CSRF-TOKEN':csrfToken}});

document.getElementById('sidebarToggle')?.addEventListener('click',()=>document.getElementById('sidebar').classList.toggle('show'));

const themeToggle=document.getElementById('themeToggle');
if(themeToggle){const saved=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',saved);themeToggle.querySelector('i').className=saved==='dark'?'bi bi-sun':'bi bi-moon-stars';themeToggle.addEventListener('click',()=>{const cur=document.documentElement.getAttribute('data-bs-theme');const nw=cur==='dark'?'light':'dark';document.documentElement.setAttribute('data-bs-theme',nw);localStorage.setItem('theme',nw);themeToggle.querySelector('i').className=nw==='dark'?'bi bi-sun':'bi bi-moon-stars'})}

document.getElementById('selectAll')?.addEventListener('change',function(){document.querySelectorAll('.lead-checkbox,.item-checkbox').forEach(cb=>cb.checked=this.checked)});

document.querySelectorAll('.alert-dismissible').forEach(a=>setTimeout(()=>{try{new bootstrap.Alert(a).close()}catch(e){}},5000));

function showToast(msg,type='success'){let c=document.getElementById('toast-container');if(!c){c=document.createElement('div');c.id='toast-container';c.className='toast-container position-fixed top-0 end-0 p-3';c.style.zIndex='9999';document.body.appendChild(c)}c.insertAdjacentHTML('beforeend',`<div class="toast align-items-center text-bg-${type} border-0" role="alert"><div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);const t=c.lastElementChild;new bootstrap.Toast(t,{delay:4000}).show();t.addEventListener('hidden.bs.toast',()=>t.remove())}

if('serviceWorker' in navigator)window.addEventListener('load',()=>navigator.serviceWorker.register('/sw.js').catch(()=>{}));
