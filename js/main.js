// ===== GOOGLE TRANSLATE INIT =====
function googleTranslateElementInit(){
  new google.translate.TranslateElement({pageLanguage:'kn',includedLanguages:'kn,en',autoDisplay:false},'google_translate_element');
}

// ===== LANG SWITCHER =====
let curLang = localStorage.getItem('dc_lang') || 'kn';

function triggerGoogleTranslate(targetLang) {
  var attempts = 0;
  var maxAttempts = 50;
  var timer = setInterval(function() {
    attempts++;
    var combo = document.querySelector('.goog-te-combo');
    if (combo) {
      combo.value = targetLang;
      combo.dispatchEvent(new Event('change'));
      clearInterval(timer);
      document.getElementById('btnEn').classList.toggle('active', targetLang === 'en');
      document.getElementById('btnKn').classList.toggle('active', targetLang !== 'en');
    }
    if (attempts >= maxAttempts) {
      clearInterval(timer);
    }
  }, 200);
}

function setLang(lang, e) {
  localStorage.setItem('dc_lang', lang);
  curLang = lang;
  if (lang === 'en') {
    triggerGoogleTranslate('en');
  } else {
    document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + window.location.hostname;
    localStorage.setItem('dc_lang', 'kn');
    window.location.reload();
  }
}

window.addEventListener('load', function() {
  if (curLang === 'en') {
    triggerGoogleTranslate('en');
  } else {
    document.getElementById('btnKn').classList.add('active');
    document.getElementById('btnEn').classList.remove('active');
  }
  if (sessionStorage.getItem('dc_admin') === '1') document.getElementById('adminLink').style.display = 'flex';
});

// ===== NAV SCROLL =====
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 55);
});

// ===== MOBILE MENU =====
function openMob() { document.getElementById('mobMenu').classList.add('open'); }
function closeMob() { document.getElementById('mobMenu').classList.remove('open'); }

// ===== PARTICLES =====
const pc = document.getElementById('particles');
for (let i = 0; i < 16; i++) {
  const p = document.createElement('div');
  p.className = 'particle';
  const sz = Math.random() * 50 + 15;
  p.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;animation-duration:${Math.random()*14+10}s;animation-delay:${Math.random()*10}s`;
  pc.appendChild(p);
}

// ===== SCROLL REVEAL =====
document.querySelectorAll('.scheme-card').forEach(c => {
  new IntersectionObserver((en) => {
    if (en[0].isIntersecting) {
      setTimeout(() => c.classList.add('vis'), parseInt(c.dataset.delay || 0));
    }
  }, {threshold:.18}).observe(c);
});

// ===== CALCULATOR =====
const RATES = {
  fd: {1:8, 2:9, 3:10.5, 5:10.5},
  rd: {1:8, 2:9, 3:10, 5:10},
  pigmy: {1:3, 2:3, 3:3, 5:3}
};
let cScheme = 'fd';
function switchScheme(s, btn) {
  cScheme = s;
  document.querySelectorAll('.calc-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  calcUpdate();
}
function fmtINR(n) { return '₹' + Math.round(n).toLocaleString('en-IN'); }
function calcUpdate() {
  const amt = parseInt(document.getElementById('cRange').value);
  const ten = parseInt(document.getElementById('cTenure').value);
  const senior = document.getElementById('cSenior').checked;
  let rate = RATES[cScheme][ten] || RATES[cScheme][Object.keys(RATES[cScheme]).filter(k=>k<=ten).sort().pop()] || RATES[cScheme][1];
  if (senior && cScheme === 'fd') rate += 0.5;
  document.getElementById('cAmt').textContent = fmtINR(amt);
  const interest = amt * rate * ten / 100;
  const total = amt + interest;
  const growth = (interest/amt*100).toFixed(1);
  document.getElementById('rPrin').textContent = fmtINR(amt);
  document.getElementById('rInt').textContent = fmtINR(interest);
  document.getElementById('rTotal').textContent = fmtINR(total);
  document.getElementById('rGrowth').textContent = `📈 ಬೆಳವಣಿಗೆ: +${growth}% in ${ten} year${ten>1?'s':''} at ${rate}% p.a.${senior?' (Senior Citizen Rate)':''}`;
}
calcUpdate();

// ===== CAROUSEL =====
let cIdx = 0;
const slides = document.querySelectorAll('.test-card');
const dotsCont = document.getElementById('cDots');
slides.forEach((_,i) => {
  const d = document.createElement('button');
  d.className = 'c-dot' + (i===0?' active':'');
  d.onclick = () => goSlide(i);
  dotsCont.appendChild(d);
});
function goSlide(idx) {
  slides[cIdx].classList.remove('active');
  dotsCont.children[cIdx].classList.remove('active');
  cIdx = idx;
  slides[cIdx].classList.add('active');
  dotsCont.children[cIdx].classList.add('active');
}
setInterval(() => goSlide((cIdx+1) % slides.length), 4500);

// ===== NOTIFICATION CAROUSEL =====
const NOTIFS = [
  {type:'success', icon:'✓', title:'ಬಡ್ಡಿ ದರ ನವೀಕರಣ! / Interest Rate Update', msg:'FD 3 ವರ್ಷ+: 10.5% | ಹಿರಿಯ ನಾಗರಿಕರು: 11% — 13ನೇ ವಾರ್ಷಿಕೋತ್ಸವ ವಿಶೇಷ!', badge:'ಈಗ'},
  {type:'alert', icon:'📅', title:'ಸಾಮಾನ್ಯ ಸಭೆ / Annual Meeting', msg:'14-09-2025, ಬೆಳಿಗ್ಗೆ 10:30 — ಸುವರ್ಣ ಸಹಕಾರ ಭವನ, ತೀರ್ಥಹಳ್ಳಿ', badge:'ಪ್ರಮುಖ ಸೂಚನೆ'},
  {type:'info', icon:'🌿', title:'ಭವಿಷ್ಯ ನಿಧಿ / Future Fund Scheme', msg:'₹500/ತಿಂಗಳು → 60 ತಿಂಗಳಲ್ಲಿ ₹25,000 ಪಡೆಯಿರಿ!', badge:'ಹೊಸ ಯೋಜನೆ'},
];

// Load admin-created notifications too
const storedNotifs = JSON.parse(localStorage.getItem('dc_notifs') || '[]');
const allNotifs = [...NOTIFS, ...storedNotifs.map(n => ({...n, badge: n.time || 'ಹೊಸದು'}))];

const notifSlider = document.getElementById('notifSlider');
const notifDotsC = document.getElementById('notifDots');
let nIdx = 0;

allNotifs.forEach((n, i) => {
  const slide = document.createElement('div');
  slide.className = 'notif-slide' + (i === 0 ? ' active' : '');
  slide.innerHTML = `<div class="notif-card ${n.type}"><div class="nc-icon">${n.icon}</div><div class="nc-body"><div class="nc-title">${n.title}</div><div class="nc-msg">${n.msg}</div><span class="nc-badge">${n.badge}</span></div></div>`;
  notifSlider.appendChild(slide);
  const dot = document.createElement('button');
  dot.className = 'notif-dot' + (i === 0 ? ' active' : '');
  dot.onclick = () => goNotif(i);
  notifDotsC.appendChild(dot);
});

// Add arrows
notifSlider.insertAdjacentHTML('beforeend', '<button class="notif-arrow left" onclick="goNotif((nIdx-1+allNotifs.length)%allNotifs.length)">❮</button><button class="notif-arrow right" onclick="goNotif((nIdx+1)%allNotifs.length)">❯</button>');

function goNotif(idx) {
  const slides = document.querySelectorAll('.notif-slide');
  const dots = document.querySelectorAll('.notif-dot');
  slides[nIdx].classList.remove('active');
  slides[nIdx].classList.add('prev');
  dots[nIdx].classList.remove('active');
  setTimeout(() => slides[nIdx === idx ? nIdx : (nIdx)].classList.remove('prev'), 500);
  nIdx = idx;
  slides[nIdx].classList.add('active');
  dots[nIdx].classList.add('active');
}
setInterval(() => goNotif((nIdx + 1) % allNotifs.length), 5000);

// ===== TOAST =====
function toast(msg, type='ok') {
  const w = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = (type==='ok'?'✓':type==='err'?'✕':'ℹ') + ' ' + msg;
  w.appendChild(t);
  setTimeout(() => { t.style.opacity='0'; t.style.transition='.3s'; setTimeout(()=>t.remove(),300); }, 3500);
}

// ===== FORM =====
function validateF(id, errId, fn) {
  const el = document.getElementById(id);
  const er = document.getElementById(errId);
  const ok = fn(el.value.trim());
  el.classList.toggle('err', !ok);
  er.classList.toggle('show', !ok);
  return ok;
}
function submitForm() {
  const n = validateF('fN','eN', v=>v.length>=3);
  const e = validateF('fE','eE', v=>/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v));
  const p = validateF('fP','eP', v=>/^[6-9]\d{9}$/.test(v.replace(/\D/g,'')));
  const m = validateF('fM','eM', v=>v.length>=10);
  if (!n||!e||!p||!m) return;
  const btn = document.getElementById('fBtn');
  btn.disabled=true; btn.textContent='⏳ ಕಳುಹಿಸಲಾಗುತ್ತಿದೆ...';
  setTimeout(()=>{
    btn.disabled=false; btn.textContent='📨 ಸಂದೇಶ ಕಳುಹಿಸಿ';
    ['fN','fE','fP','fM'].forEach(id=>document.getElementById(id).value='');
    toast('ಸಂದೇಶ ಕಳುಹಿಸಲಾಗಿದೆ! ನಾವು ಶೀಘ್ರದಲ್ಲೇ ಸಂಪರ್ಕಿಸುತ್ತೇವೆ. 🙏','ok');
  },1500);
}
