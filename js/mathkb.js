/**
 * SMFE Math Keyboard
 * Universal math keyboard for all input fields.
 * Usage: Add class="mathkb-trigger" data-target="#inputId" to any button
 *        OR call MathKB.attach(inputEl) manually.
 */

const MathKB = (() => {
  let activeTarget = null;
  let greekUppercase = false;

  // ─── DATA ─────────────────────────────────────────────────────────────────
  const ENGLISH = [
    'a','b','c','d','e','f','g','h','i','j','k','l','m',
    'n','o','p','q','r','s','t','u','v','w','x','y','z',
    'A','B','C','D','E','F','G','H','I','J','K','L','M',
    'N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
  ];

  const GREEK_LOWER = [
    {l:'α',n:'alpha'},{l:'β',n:'beta'},{l:'γ',n:'gamma'},
    {l:'δ',n:'delta'},{l:'ε',n:'epsilon'},{l:'ζ',n:'zeta'},
    {l:'η',n:'eta'},{l:'θ',n:'theta'},{l:'ι',n:'iota'},
    {l:'κ',n:'kappa'},{l:'λ',n:'lambda'},{l:'μ',n:'mu'},
    {l:'ν',n:'nu'},{l:'ξ',n:'xi'},{l:'π',n:'pi'},
    {l:'ρ',n:'rho'},{l:'σ',n:'sigma'},{l:'τ',n:'tau'},
    {l:'υ',n:'upsilon'},{l:'φ',n:'phi'},{l:'χ',n:'chi'},
    {l:'ψ',n:'psi'},{l:'ω',n:'omega'}
  ];

  const GREEK_UPPER = [
    {l:'Α',n:'Alpha'},{l:'Β',n:'Beta'},{l:'Γ',n:'Gamma'},
    {l:'Δ',n:'Delta'},{l:'Ε',n:'Epsilon'},{l:'Ζ',n:'Zeta'},
    {l:'Η',n:'Eta'},{l:'Θ',n:'Theta'},{l:'Ι',n:'Iota'},
    {l:'Κ',n:'Kappa'},{l:'Λ',n:'Lambda'},{l:'Μ',n:'Mu'},
    {l:'Ν',n:'Nu'},{l:'Ξ',n:'Xi'},{l:'Ο',n:'Omicron'},
    {l:'Π',n:'Pi'},{l:'Ρ',n:'Rho'},{l:'Σ',n:'Sigma'},
    {l:'Τ',n:'Tau'},{l:'Υ',n:'Upsilon'},{l:'Φ',n:'Phi'},
    {l:'Χ',n:'Chi'},{l:'Ψ',n:'Psi'},{l:'Ω',n:'Omega'}
  ];

  const FUNCTIONS = [
    'sin','cos','tan','cot','sec','csc',
    'sinh','cosh','tanh','coth','sech','csch',
    'arcsin','arccos','arctan','arccot','arcsec','arccsc',
    'log','ln','log₂','log₁₀',
    'exp','sqrt','cbrt',
    'Γ','ζ','erf','erfc','Ei',
    'max','min','abs','sgn',
    'floor','ceil','round',
    'gcd','lcm','mod',
    'det','tr','rank',
    'lim','sup','inf',
    'arg','Re','Im','conj',
    'curl','div','grad','∇','∂',
    'P','C','n!',
  ];

  const SYMBOLS = [
    { type:'simple', label:'+', val:' + ' },
    { type:'simple', label:'−', val:' − ' },
    { type:'simple', label:'×', val:' × ' },
    { type:'simple', label:'÷', val:' ÷ ' },
    { type:'simple', label:'=', val:' = ' },
    { type:'simple', label:'≠', val:' ≠ ' },
    { type:'simple', label:'<', val:' < ' },
    { type:'simple', label:'>', val:' > ' },
    { type:'simple', label:'≤', val:' ≤ ' },
    { type:'simple', label:'≥', val:' ≥ ' },
    { type:'simple', label:'≈', val:' ≈ ' },
    { type:'simple', label:'≡', val:' ≡ ' },
    { type:'simple', label:'∝', val:' ∝ ' },
    { type:'simple', label:'∞', val:'∞' },
    { type:'simple', label:'±', val:'±' },
    { type:'simple', label:'∈', val:' ∈ ' },
    { type:'simple', label:'∉', val:' ∉ ' },
    { type:'simple', label:'⊂', val:' ⊂ ' },
    { type:'simple', label:'⊃', val:' ⊃ ' },
    { type:'simple', label:'∪', val:' ∪ ' },
    { type:'simple', label:'∩', val:' ∩ ' },
    { type:'simple', label:'∅', val:'∅' },
    { type:'simple', label:'→', val:' → ' },
    { type:'simple', label:'⇒', val:' ⇒ ' },
    { type:'simple', label:'⟺', val:' ⟺ ' },
    { type:'simple', label:'∀', val:'∀' },
    { type:'simple', label:'∃', val:'∃' },
    { type:'simple', label:'∂', val:'∂' },
    { type:'simple', label:'²', val:'²' },
    { type:'simple', label:'³', val:'³' },
    { type:'simple', label:'ⁿ', val:'^n' },
    { type:'simple', label:'√', val:'√(' },
    { type:'simple', label:'∑', val:'Σ' },
    { type:'simple', label:'∏', val:'∏' },
    // Editable templates
    {
      type:'template',
      preview:'d/dx ( f(x) )',
      label:'Derivative',
      fields:[{name:'n',default:'',placeholder:'order'}],
      build:(v) => v[0] ? `d${v[0]}/dx${v[0]}` : 'd/dx'
    },
    {
      type:'template',
      preview:'∫ f(x) dx',
      label:'Indefinite Integral',
      fields:[{name:'f',default:'f(x)',placeholder:'f(x)'}],
      build:(v) => `∫ ${v[0] || 'f(x)'} dx`
    },
    {
      type:'template',
      preview:'∫ₐᵇ f(x) dx',
      label:'Definite Integral',
      fields:[
        {name:'a',default:'a',placeholder:'lower'},
        {name:'b',default:'b',placeholder:'upper'},
        {name:'f',default:'f(x)',placeholder:'f(x)'}
      ],
      build:(v) => `∫[${v[0]||'a'} to ${v[1]||'b'}] ${v[2]||'f(x)'} dx`
    },
    {
      type:'template',
      preview:'Σᵢ₌ₐⁿ f(i)',
      label:'Sigma Sum',
      fields:[
        {name:'i',default:'i',placeholder:'var'},
        {name:'a',default:'1',placeholder:'start'},
        {name:'n',default:'n',placeholder:'end'},
        {name:'f',default:'f(i)',placeholder:'f(i)'}
      ],
      build:(v) => `Σ(${v[0]||'i'}=${v[1]||'1'} to ${v[2]||'n'}) ${v[3]||'f(i)'}`
    },
    {
      type:'template',
      preview:'∏ᵢ₌ₐⁿ f(i)',
      label:'Product ∏',
      fields:[
        {name:'i',default:'i',placeholder:'var'},
        {name:'a',default:'1',placeholder:'start'},
        {name:'n',default:'n',placeholder:'end'}
      ],
      build:(v) => `∏(${v[0]||'i'}=${v[1]||'1'} to ${v[2]||'n'}) f(${v[0]||'i'})`
    },
    {
      type:'template',
      preview:'lim_{x→a}',
      label:'Limit',
      fields:[
        {name:'var',default:'x',placeholder:'var'},
        {name:'to',default:'a',placeholder:'→'}
      ],
      build:(v) => `lim(${v[0]||'x'} → ${v[1]||'a'})`
    },
    {
      type:'template',
      preview:'xⁿ',
      label:'Power',
      fields:[
        {name:'base',default:'x',placeholder:'base'},
        {name:'exp',default:'n',placeholder:'exp'}
      ],
      build:(v) => `(${v[0]||'x'})^(${v[1]||'n'})`
    },
    {
      type:'template',
      preview:'ⁿ√x',
      label:'Nth Root',
      fields:[{name:'n',default:'n',placeholder:'root'},{name:'x',default:'x',placeholder:'expr'}],
      build:(v) => `${v[0]||'n'}√(${v[1]||'x'})`
    },
    {
      type:'template',
      preview:'|x|',
      label:'Absolute Value',
      fields:[{name:'x',default:'x',placeholder:'expr'}],
      build:(v) => `|${v[0]||'x'}|`
    },
    {
      type:'template',
      preview:'a/b',
      label:'Fraction',
      fields:[
        {name:'num',default:'a',placeholder:'num'},
        {name:'den',default:'b',placeholder:'den'}
      ],
      build:(v) => `(${v[0]||'a'})/(${v[1]||'b'})`
    },
    {
      type:'template',
      preview:'∂f/∂x',
      label:'Partial Deriv.',
      fields:[
        {name:'f',default:'f',placeholder:'func'},
        {name:'x',default:'x',placeholder:'var'},
        {name:'n',default:'',placeholder:'order'}
      ],
      build:(v) => v[2] ? `∂${v[2]}${v[0]||'f'}/∂${v[1]||'x'}${v[2]}` : `∂${v[0]||'f'}/∂${v[1]||'x'}`
    },
    {
      type:'template',
      preview:'∮ F·dr',
      label:'Line Integral',
      fields:[{name:'C',default:'C',placeholder:'curve'}],
      build:(v) => `∮_{${v[0]||'C'}} F·dr`
    },
    {
      type:'template',
      preview:'ₙCₖ',
      label:'Combination',
      fields:[
        {name:'n',default:'n',placeholder:'n'},
        {name:'k',default:'k',placeholder:'k'}
      ],
      build:(v) => `C(${v[0]||'n'}, ${v[1]||'k'})`
    },
    {
      type:'template',
      preview:'ₙPₖ',
      label:'Permutation',
      fields:[
        {name:'n',default:'n',placeholder:'n'},
        {name:'k',default:'k',placeholder:'k'}
      ],
      build:(v) => `P(${v[0]||'n'}, ${v[1]||'k'})`
    },
    {
      type:'template',
      preview:'[a,b]',
      label:'Interval',
      fields:[
        {name:'a',default:'a',placeholder:'a'},
        {name:'b',default:'b',placeholder:'b'}
      ],
      build:(v) => `[${v[0]||'a'}, ${v[1]||'b'}]`
    },
    {
      type:'template',
      preview:'Matrix 2×2',
      label:'Matrix 2×2',
      fields:[
        {name:'a',default:'a',placeholder:'a₁₁'},
        {name:'b',default:'b',placeholder:'a₁₂'},
        {name:'c',default:'c',placeholder:'a₂₁'},
        {name:'d',default:'d',placeholder:'a₂₂'}
      ],
      build:(v) => `[[${v[0]||'a'}, ${v[1]||'b'}], [${v[2]||'c'}, ${v[3]||'d'}]]`
    },
  ];

  // ─── TEXT → LaTeX CONVERTER ────────────────────────────────────────────────
  function textToLatex(text) {
    if (!text || !text.trim()) return '';
    let t = text;

    // Matrix [[a,b],[c,d]]
    t = t.replace(/\[\[([^,\]]+),\s*([^\]]+)\],\s*\[([^,\]]+),\s*([^\]]+)\]\]/g,
      '\\begin{pmatrix}$1&$2\\\\$3&$4\\end{pmatrix}');

    // C(n,k) → \binom
    t = t.replace(/C\((\w+),\s*(\w+)\)/g, '\\binom{$1}{$2}');
    // P(n,k)
    t = t.replace(/P\((\w+),\s*(\w+)\)/g, '\\frac{$1!}{($1-$2)!}');

    // lim(x → a)
    t = t.replace(/lim\(([^→)]+)\s*→\s*([^)]+)\)/g, '\\lim_{$1\\to $2}');

    // Definite integral: ∫[a to b] f dx
    t = t.replace(/∫\[([^\]]+)\s+to\s+([^\]]+)\]\s*([^d\n]*)\s*dx/g,
      (_, a, b, f) => `\\int_{${a.trim()}}^{${b.trim()}} ${f.trim()} \\,dx`);

    // Indefinite integral: ∫ f dx
    t = t.replace(/∫\s*([^d\n]*)\s*dx/g,
      (_, f) => `\\int ${f.trim()} \\,dx`);

    // Remaining ∫
    t = t.replace(/∫/g, '\\int');

    // Sigma: Σ(i=1 to n) f(i)
    t = t.replace(/Σ\((\w+)=([^\s]+)\s+to\s+([^)]+)\)\s*([^\n]*)/g,
      (_, v, a, b, f) => `\\sum_{${v}=${a}}^{${b}} ${f.trim()}`);

    // Product: ∏(i=1 to n) f(i)
    t = t.replace(/∏\((\w+)=([^\s]+)\s+to\s+([^)]+)\)\s*([^\n]*)/g,
      (_, v, a, b, f) => `\\prod_{${v}=${a}}^{${b}} ${f.trim()}`);

    // Remaining Σ ∏
    t = t.replace(/Σ/g, '\\sum');
    t = t.replace(/∏/g, '\\prod');

    // Line integral ∮_{C}
    t = t.replace(/∮_\{([^}]+)\}/g, '\\oint_{$1}');
    t = t.replace(/∮/g, '\\oint');

    // Partial derivative ∂nf/∂xn
    t = t.replace(/∂(\d*)([^/∂\s]+)\/∂(\w+)(\d*)/g, (m, n1, f, x, n2) => {
      if (n1) return `\\frac{\\partial^{${n1}} ${f}}{\\partial ${x}^{${n2||n1}}}`;
      return `\\frac{\\partial ${f}}{\\partial ${x}}`;
    });

    // Derivative d²/dx²  or  d/dx
    t = t.replace(/d(\d*)\/dx(\d*)/g, (m, n1, n2) => {
      if (n1) return `\\frac{d^{${n1}}}{dx^{${n2||n1}}}`;
      return '\\frac{d}{dx}';
    });

    // Fraction (a)/(b)
    t = t.replace(/\(([^)]+)\)\/\(([^)]+)\)/g, '\\frac{$1}{$2}');

    // Nth root: n√(x)
    t = t.replace(/(\d+)√\(([^)]*)\)/g, '\\sqrt[$1]{$2}');

    // Square root √(x)
    t = t.replace(/√\(([^)]*)\)/g, '\\sqrt{$1}');
    t = t.replace(/√/g, '\\sqrt');

    // Power (x)^(n)
    t = t.replace(/\^\(([^)]+)\)/g, '^{$1}');
    t = t.replace(/\^([a-zA-Z0-9]+)/g, '^{$1}');

    // Subscripts unicode
    t = t.replace(/₀/g,'_0').replace(/₁/g,'_1').replace(/₂/g,'_2')
         .replace(/₃/g,'_3').replace(/₄/g,'_4').replace(/₅/g,'_5')
         .replace(/₆/g,'_6').replace(/₇/g,'_7').replace(/₈/g,'_8')
         .replace(/₉/g,'_9').replace(/ᵢ/g,'_i').replace(/ⱼ/g,'_j')
         .replace(/ₙ/g,'_n').replace(/ₖ/g,'_k');

    // Superscripts unicode
    t = t.replace(/⁻¹/g,'^{-1}').replace(/⁻²/g,'^{-2}')
         .replace(/²/g,'^{2}').replace(/³/g,'^{3}').replace(/ⁿ/g,'^{n}')
         .replace(/⁰/g,'^{0}').replace(/¹/g,'^{1}').replace(/⁴/g,'^{4}')
         .replace(/⁵/g,'^{5}').replace(/⁶/g,'^{6}').replace(/⁷/g,'^{7}')
         .replace(/⁸/g,'^{8}').replace(/⁹/g,'^{9}');

    // Greek letters
    t = t.replace(/α/g,'\\alpha').replace(/β/g,'\\beta').replace(/γ/g,'\\gamma')
         .replace(/δ/g,'\\delta').replace(/ε/g,'\\epsilon').replace(/ζ/g,'\\zeta')
         .replace(/η/g,'\\eta').replace(/θ/g,'\\theta').replace(/ι/g,'\\iota')
         .replace(/κ/g,'\\kappa').replace(/λ/g,'\\lambda').replace(/μ/g,'\\mu')
         .replace(/ν/g,'\\nu').replace(/ξ/g,'\\xi').replace(/π/g,'\\pi')
         .replace(/ρ/g,'\\rho').replace(/σ/g,'\\sigma').replace(/τ/g,'\\tau')
         .replace(/υ/g,'\\upsilon').replace(/φ/g,'\\phi').replace(/χ/g,'\\chi')
         .replace(/ψ/g,'\\psi').replace(/ω/g,'\\omega');

    // Uppercase Greek with LaTeX commands
    t = t.replace(/Γ/g,'\\Gamma').replace(/Δ/g,'\\Delta').replace(/Θ/g,'\\Theta')
         .replace(/Λ/g,'\\Lambda').replace(/Ξ/g,'\\Xi').replace(/Π/g,'\\Pi')
         .replace(/Σ/g,'\\Sigma').replace(/Υ/g,'\\Upsilon').replace(/Φ/g,'\\Phi')
         .replace(/Ψ/g,'\\Psi').replace(/Ω/g,'\\Omega');
    // Uppercase Greek that map to Latin letters in LaTeX
    t = t.replace(/Α/g,'A').replace(/Β/g,'B').replace(/Ε/g,'E')
         .replace(/Ζ/g,'Z').replace(/Η/g,'H').replace(/Ι/g,'I')
         .replace(/Κ/g,'K').replace(/Μ/g,'M').replace(/Ν/g,'N')
         .replace(/Ο/g,'O').replace(/Ρ/g,'P').replace(/Τ/g,'T')
         .replace(/Χ/g,'X');

    // & used as "and" conjunction (not an alignment &)
    t = t.replace(/ & /g, ' \\text{ and } ').replace(/&/g, '\\text{\\&}');

    // Operators
    t = t.replace(/×/g,'\\times').replace(/÷/g,'\\div')
         .replace(/≤/g,'\\leq').replace(/≥/g,'\\geq')
         .replace(/≠/g,'\\neq').replace(/≈/g,'\\approx')
         .replace(/≡/g,'\\equiv').replace(/∝/g,'\\propto')
         .replace(/∞/g,'\\infty').replace(/±/g,'\\pm')
         .replace(/∈/g,'\\in').replace(/∉/g,'\\notin')
         .replace(/⊂/g,'\\subset').replace(/⊃/g,'\\supset')
         .replace(/∪/g,'\\cup').replace(/∩/g,'\\cap')
         .replace(/∅/g,'\\emptyset').replace(/→/g,'\\to ')
         .replace(/⇒/g,'\\Rightarrow ').replace(/⟺/g,'\\Leftrightarrow ')
         .replace(/∀/g,'\\forall').replace(/∃/g,'\\exists')
         .replace(/∂/g,'\\partial').replace(/∇/g,'\\nabla');

    // Math functions
    const fns = ['sin','cos','tan','cot','sec','csc',
                 'sinh','cosh','tanh','coth','sech','csch',
                 'arcsin','arccos','arctan','arccot',
                 'log','ln','exp','max','min','gcd','lcm',
                 'det','sup','inf','arg','lim'];
    fns.forEach(fn => {
      t = t.replace(new RegExp(`\\b${fn}\\b`, 'g'), `\\${fn}`);
    });
    t = t.replace(/\bsqrt\b/g,'\\sqrt');
    t = t.replace(/\btr\b/g,'\\operatorname{tr}');
    t = t.replace(/\brank\b/g,'\\operatorname{rank}');
    t = t.replace(/\berf\b/g,'\\operatorname{erf}');
    t = t.replace(/\berfc\b/g,'\\operatorname{erfc}');
    t = t.replace(/\bRe\b/g,'\\operatorname{Re}');
    t = t.replace(/\bIm\b/g,'\\operatorname{Im}');
    t = t.replace(/\bcurl\b/g,'\\operatorname{curl}');
    t = t.replace(/\bdiv\b/g,'\\operatorname{div}');
    t = t.replace(/\bgrad\b/g,'\\nabla');
    t = t.replace(/\bsgn\b/g,'\\operatorname{sgn}');
    t = t.replace(/\bfloor\b/g,'\\lfloor');
    t = t.replace(/\bceil\b/g,'\\lceil');
    t = t.replace(/\babs\b/g,'\\left|');

    // log base
    t = t.replace(/\\log_2\b/g,'\\log_2');
    t = t.replace(/\\log_{10}\b/g,'\\log_{10}');

    // Wrap free-standing English words (4+ letters, not a LaTeX command) in \text{}
    // Lookbehind: not preceded by \, {, or a letter (so \Gamma, \text{}, {pmatrix} are skipped)
    t = t.replace(/(?<![\\{a-zA-Z])([A-Za-z]{4,})(?![a-zA-Z{])/g, '\\text{ $1 }');

    return t;
  }

  // ─── MATH PREVIEW ──────────────────────────────────────────────────────────
  function renderMathPreview(el) {
    if (!el) return;
    const preview = document.querySelector(`[data-math-preview-for="${el.id}"]`);
    if (!preview) return;
    const text = el.value || '';
    if (!text.trim()) {
      preview.style.display = 'none';
      return;
    }
    preview.style.display = 'block';
    const latex = textToLatex(text);
    if (typeof katex !== 'undefined') {
      try {
        katex.render(latex, preview, {
          throwOnError: false,
          displayMode: true,
          output: 'html',
          trust: false,
        });
      } catch (e) {
        preview.textContent = text;
      }
    } else {
      preview.textContent = text;
    }
  }

  function attachPreviewTo(el) {
    if (!el || !el.id || el.dataset.mathPreviewAttached) return;
    el.dataset.mathPreviewAttached = '1';

    // Reuse an existing preview element if one is already in the DOM
    let preview = document.querySelector(`[data-math-preview-for="${el.id}"]`);
    if (!preview) {
      preview = document.createElement('div');
      preview.className = 'math-preview-box';
      preview.dataset.mathPreviewFor = el.id;
      preview.style.display = 'none';
      el.parentElement.insertAdjacentElement('afterend', preview);
    }

    el.addEventListener('input', () => renderMathPreview(el));
  }

  // ─── BUILD UI ─────────────────────────────────────────────────────────────
  function buildPanel() {
    const panel = document.createElement('div');
    panel.id = 'mathkb-panel';
    panel.className = 'mathkb-panel';
    panel.innerHTML = `
      <div class="mathkb-header">
        <div class="mathkb-title">
          <span>🧮</span> Math Keyboard
        </div>
        <button class="mathkb-close" id="mathkb-close">✕</button>
      </div>

      <!-- Target input preview (PC: shown for Functions/Symbols tabs) -->
      <div class="mathkb-input-preview" id="mathkb-input-preview" style="display:none;">
        <span class="mathkb-input-preview-label">Editing:</span>
        <div class="mathkb-input-preview-value" id="mathkb-preview-value"></div>
      </div>

      <div class="mathkb-tabs">
        <button class="mathkb-tab active" data-tab="english">Abc</button>
        <button class="mathkb-tab" data-tab="greek">Greek</button>
        <button class="mathkb-tab" data-tab="functions">Functions</button>
        <button class="mathkb-tab" data-tab="symbols">Symbols</button>
      </div>

      <!-- English -->
      <div class="mathkb-section active" data-section="english">
        <div class="kb-grid" id="kb-english"></div>
      </div>

      <!-- Greek -->
      <div class="mathkb-section" data-section="greek">
        <div class="greek-toggle-row">
          <span class="greek-toggle-label" id="greek-case-label">Lowercase (α)</span>
          <button class="greek-case-btn" id="greek-case-btn" title="Toggle uppercase/lowercase">Aa ⇅</button>
        </div>
        <div class="kb-grid" id="kb-greek"></div>
      </div>

      <!-- Functions -->
      <div class="mathkb-section" data-section="functions">
        <div class="kb-scroll-wrap">
          <div class="kb-grid" id="kb-functions"></div>
        </div>
      </div>

      <!-- Symbols -->
      <div class="mathkb-section" data-section="symbols">
        <div class="kb-scroll-wrap">
          <div class="symbols-grid" id="kb-symbols"></div>
        </div>
      </div>
    `;
    return panel;
  }

  function populatePanel(panel) {
    // English
    const engGrid = panel.querySelector('#kb-english');
    ENGLISH.forEach(ch => {
      const btn = document.createElement('button');
      btn.className = 'kb-key';
      btn.textContent = ch;
      btn.addEventListener('click', () => insert(ch));
      engGrid.appendChild(btn);
    });

    // Greek
    renderGreekKeys(panel);

    // Toggle button
    panel.querySelector('#greek-case-btn').addEventListener('click', () => {
      greekUppercase = !greekUppercase;
      panel.querySelector('#greek-case-label').textContent =
        greekUppercase ? 'Uppercase (Γ)' : 'Lowercase (α)';
      renderGreekKeys(panel);
    });

    // Functions
    const fnGrid = panel.querySelector('#kb-functions');
    FUNCTIONS.forEach(fn => {
      const btn = document.createElement('button');
      btn.className = 'kb-key fn';
      btn.textContent = fn;
      btn.addEventListener('click', () => insert(fn + (fn.match(/[a-zA-Z]$/) ? '(' : '')));
      fnGrid.appendChild(btn);
    });

    // Symbols
    const symGrid = panel.querySelector('#kb-symbols');
    SYMBOLS.forEach(sym => {
      if (sym.type === 'simple') {
        const btn = document.createElement('button');
        btn.className = 'kb-key symbol';
        btn.textContent = sym.label;
        btn.addEventListener('click', () => insert(sym.val));
        symGrid.appendChild(btn);
      } else {
        const card = document.createElement('div');
        card.className = 'kb-sym-card';
        let fieldsHtml = sym.fields.map((f,i) =>
          `<input type="text" data-field="${i}" value="${f.default}" placeholder="${f.placeholder}" style="min-width:${Math.min(f.placeholder.length*9+28,80)}px;max-width:90px">`
        ).join('');
        card.innerHTML = `
          <div class="kb-sym-preview">${sym.preview}</div>
          <div style="font-size:.72rem;color:#6b7280;margin-bottom:4px">${sym.label}</div>
          ${sym.fields.length ? `<div class="kb-sym-inputs">${fieldsHtml}</div>` : ''}
          <button class="kb-sym-insert">Insert ↵</button>
        `;
        card.querySelector('.kb-sym-insert').addEventListener('click', () => {
          const vals = sym.fields.map((_,i) => card.querySelector(`[data-field="${i}"]`)?.value || '');
          insert(sym.build(vals));
        });
        card.querySelectorAll('input').forEach(inp => inp.addEventListener('click', e => e.stopPropagation()));
        symGrid.appendChild(card);
      }
    });
  }

  function renderGreekKeys(panel) {
    const grGrid = panel.querySelector('#kb-greek');
    grGrid.innerHTML = '';
    const list = greekUppercase ? GREEK_UPPER : GREEK_LOWER;
    list.forEach(g => {
      const btn = document.createElement('button');
      btn.className = 'kb-key greek';
      btn.textContent = g.l;
      btn.title = g.n;
      btn.addEventListener('click', () => insert(g.l));
      grGrid.appendChild(btn);
    });
  }

  // ─── INSERT ────────────────────────────────────────────────────────────────
  function insert(text) {
    if (!activeTarget) return;
    const el = activeTarget;
    if (el.tagName === 'TEXTAREA' || el.tagName === 'INPUT') {
      const start = el.selectionStart;
      const end   = el.selectionEnd;
      el.value    = el.value.slice(0, start) + text + el.value.slice(end);
      const pos   = start + text.length;
      el.setSelectionRange(pos, pos);
      el.focus();
      el.dispatchEvent(new Event('input', { bubbles: true }));
    }
    // Update input-preview bar in panel
    updateInputPreviewBar();
    // Render math preview
    renderMathPreview(el);
  }

  function updateInputPreviewBar() {
    const bar   = document.getElementById('mathkb-input-preview');
    const val   = document.getElementById('mathkb-preview-value');
    const panel = document.getElementById('mathkb-panel');
    if (!bar || !val) return;
    const activeTab = panel?.querySelector('.mathkb-tab.active')?.dataset?.tab;
    const isDesktop = window.innerWidth >= 768;
    if (isDesktop && (activeTab === 'functions' || activeTab === 'symbols')) {
      const text = activeTarget ? activeTarget.value : '';
      val.textContent = text || '(empty)';
      bar.style.display = 'flex';
    } else {
      bar.style.display = 'none';
    }
  }

  // ─── INIT ─────────────────────────────────────────────────────────────────
  function init() {
    const overlay = document.createElement('div');
    overlay.id = 'mathkb-overlay';
    overlay.className = 'mathkb-overlay';
    overlay.addEventListener('click', close);
    document.body.appendChild(overlay);

    const panel = buildPanel();
    document.body.appendChild(panel);
    populatePanel(panel);

    // Tab switching
    panel.querySelectorAll('.mathkb-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        panel.querySelectorAll('.mathkb-tab').forEach(t => t.classList.remove('active'));
        panel.querySelectorAll('.mathkb-section').forEach(s => s.classList.remove('active'));
        tab.classList.add('active');
        panel.querySelector(`[data-section="${tab.dataset.tab}"]`).classList.add('active');
        updateInputPreviewBar();
      });
    });

    panel.querySelector('#mathkb-close').addEventListener('click', close);

    // Update preview bar on resize
    window.addEventListener('resize', updateInputPreviewBar);

    wireAllTriggers();

    const observer = new MutationObserver(() => wireAllTriggers());
    observer.observe(document.body, { childList: true, subtree: true });
  }

  function wireAllTriggers() {
    document.querySelectorAll('.mathkb-trigger:not([data-kb-wired])').forEach(btn => {
      btn.dataset.kbWired = '1';
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const targetSel = btn.dataset.target;
        if (targetSel) {
          activeTarget = document.querySelector(targetSel);
        } else {
          activeTarget = btn.parentElement.querySelector('input, textarea');
        }
        // Attach math preview to this target
        if (activeTarget) attachPreviewTo(activeTarget);
        open();
      });
    });

    // Also attach previews to all inputs/textareas that already have triggers nearby
    document.querySelectorAll('[data-target]').forEach(btn => {
      const sel = btn.dataset.target;
      if (sel) {
        const el = document.querySelector(sel);
        if (el && el.id) attachPreviewTo(el);
      }
    });
  }

  function open() {
    document.getElementById('mathkb-overlay').classList.add('open');
    document.getElementById('mathkb-panel').classList.add('open');
    updateInputPreviewBar();
  }

  function close() {
    document.getElementById('mathkb-overlay').classList.remove('open');
    document.getElementById('mathkb-panel').classList.remove('open');
  }

  function attach(inputEl) {
    activeTarget = inputEl;
    if (inputEl) attachPreviewTo(inputEl);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return { open, close, attach, insert, textToLatex, renderMathPreview };
})();
