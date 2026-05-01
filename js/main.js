// ===== GOOGLE TRANSLATE INIT =====
function googleTranslateElementInit(){
  new google.translate.TranslateElement({pageLanguage:'kn',includedLanguages:'kn,en',autoDisplay:false},'google_translate_element');
}

// ===== LOGO SWITCHER =====
function switchLogos(lang) {
  const logos = document.querySelectorAll('.lang-logo');
  logos.forEach(function(logo) {
    logo.src = 'assets/images/logo-english.png';
  });
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
  switchLogos(lang);
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
  switchLogos(curLang);
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

// ===== CALCULATOR & RATES =====
let RATES = {
  fd: {1:8, 2:9, 3:10.5, 5:10.5},
  rd: {1:8, 2:9, 3:10, 5:10}
};

function loadDynamicRates() {
  const stored = localStorage.getItem('dc_rates');
  if (stored) {
    const r = JSON.parse(stored);
    RATES.fd = {
      1: parseFloat(r.fd_1g || 8),
      2: parseFloat(r.fd_2g || 9),
      3: parseFloat(r.fd_3g || 10.5),
      5: parseFloat(r.fd_3g || 10.5) // Fallback for 5yr
    };
    RATES.rd = {
      1: parseFloat(r.rd_12 || 8),
      2: parseFloat(r.rd_24 || 9),
      3: parseFloat(r.rd_36 || 10),
      5: parseFloat(r.rd_36 || 10)
    };
    // Special case for Senior rates in JS logic (handled in calcUpdate)
    window.seniorRates = {
      1: parseFloat(r.fd_1s || 8.5),
      2: parseFloat(r.fd_2s || 9.5),
      3: parseFloat(r.fd_3s || 11)
    };
  }
}
loadDynamicRates();

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
  
  // Apply dynamic senior rates if available
  if (senior && cScheme === 'fd') {
    if (window.seniorRates && window.seniorRates[ten]) {
      rate = window.seniorRates[ten];
    } else {
      rate += 0.5; // Fallback
    }
  }
  
  document.getElementById('cAmt').textContent = fmtINR(amt);
  const interest = amt * rate * ten / 100;
  const total = amt + interest;
  const growth = (interest/amt*100).toFixed(1);
  document.getElementById('rPrin').textContent = fmtINR(amt);
  document.getElementById('rInt').textContent = fmtINR(interest);
  document.getElementById('rTotal').textContent = fmtINR(total);
  document.getElementById('rGrowth').textContent = `📈 Growth: +${growth}% in ${ten} year${ten>1?'s':''} at ${rate}% p.a.${senior?' (Senior Citizen Rate)':''}`;
}
calcUpdate();

// ===== CAROUSEL =====
let cIdx = 0;
const slides = document.querySelectorAll('.test-card');
const dotsCont = document.getElementById('cDots');
if (slides.length > 0 && dotsCont) {
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
}

// ===== NOTIFICATION CAROUSEL =====
const DEFAULT_NOTIFS = [
  {type:'success', icon:'✓', title:'Interest Rate Update', msg:'FD 3 Year+: 10.5% | Senior Citizens: 11% — Anniversary Special!', badge:'Now'},
  {type:'alert', icon:'📅', title:'Annual Meeting', msg:'14-09-2025, 10:30 AM — Suvarna Sahakara Bhavan, Thirthahalli', badge:'Important'},
];

function initNotifications() {
  const storedNotifs = JSON.parse(localStorage.getItem('dc_notifs') || '[]');
  const allNotifs = storedNotifs.length > 0 ? storedNotifs : DEFAULT_NOTIFS;

  const notifSlider = document.getElementById('notifSlider');
  const notifDotsC = document.getElementById('notifDots');
  if (!notifSlider) return;

  notifSlider.innerHTML = '';
  if (notifDotsC) notifDotsC.innerHTML = '';
  let nIdx = 0;

  allNotifs.forEach((n, i) => {
    const slide = document.createElement('div');
    slide.className = 'notif-slide' + (i === 0 ? ' active' : '');
    slide.innerHTML = `<div class="notif-card ${n.type}"><div class="nc-icon">${n.icon}</div><div class="nc-body"><div class="nc-title">${n.title}</div><div class="nc-msg">${n.msg}</div><span class="nc-badge">${n.badge || 'New'}</span></div></div>`;
    notifSlider.appendChild(slide);
    
    if (notifDotsC) {
      const dot = document.createElement('button');
      dot.className = 'notif-dot' + (i === 0 ? ' active' : '');
      dot.onclick = () => goNotif(i);
      notifDotsC.appendChild(dot);
    }
  });

  // Add arrows
  notifSlider.insertAdjacentHTML('beforeend', '<button class="notif-arrow left" onclick="window.goNotif((window.nIdx-1+'+allNotifs.length+')%'+allNotifs.length+')">❮</button><button class="notif-arrow right" onclick="window.goNotif((window.nIdx+1)%'+allNotifs.length+')">❯</button>');

  window.nIdx = 0;
  window.goNotif = function(idx) {
    const slides = document.querySelectorAll('.notif-slide');
    const dots = document.querySelectorAll('.notif-dot');
    if (!slides[window.nIdx]) return;
    slides[window.nIdx].classList.remove('active');
    slides[window.nIdx].classList.add('prev');
    if (dots[window.nIdx]) dots[window.nIdx].classList.remove('active');
    
    const prevIdx = window.nIdx;
    setTimeout(() => { if (slides[prevIdx]) slides[prevIdx].classList.remove('prev') }, 500);
    
    window.nIdx = idx;
    if (slides[window.nIdx]) slides[window.nIdx].classList.add('active');
    if (dots[window.nIdx]) dots[window.nIdx].classList.add('active');
  }
  
  if (allNotifs.length > 1) {
    setInterval(() => window.goNotif((window.nIdx + 1) % allNotifs.length), 5000);
  }
}
initNotifications();


function updateHomepageLabels() {
  const stored = localStorage.getItem('dc_rates');
  if (!stored) return;
  const r = JSON.parse(stored);
  
  // Update Hero Stat
  const heroFd = document.querySelector('.hero-stat .hero-stat-num');
  if (heroFd && heroFd.textContent.includes('%')) {
    heroFd.textContent = (r.fd_3s || 10.5) + '%';
  }

  // Update Scheme Cards (heuristic based on order)
  const rateNums = document.querySelectorAll('.sc-rate-num');
  if (rateNums.length >= 3) {
    rateNums[0].textContent = (r.fd_3g || 10) + '%'; // FD
    rateNums[1].textContent = (r.rd_36 || 10) + '%'; // RD
    // Pigmy remains 3% usually but could be made dynamic too
  }
}
window.addEventListener('load', () => {
  updateHomepageLabels();
});

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
