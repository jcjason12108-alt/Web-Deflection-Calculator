<?php
/**
 * Plugin Name: LL706 Web Deflection Calculator
 * Plugin URI: https://github.com/jcjason12108-alt/Web-Deflection-Calculator/
 * Description: Adds EVO and FDL web deflection shim calculators via shortcode.
 * Version: 0.2.0
 * Author: Jason Cox
 * Requires at least: 6.0
 * Tested up to: 6.9.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ll706-web-deflection-calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

$ll706_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/jcjason12108-alt/Web-Deflection-Calculator/',
    __FILE__,
    'evo-fdl-web-deflection'
);
$ll706_update_checker->setBranch('main');

add_filter(
    $ll706_update_checker->getUniqueName('vcs_update_detection_strategies'),
    static function (array $strategies): array {
        return isset($strategies['branch']) ? ['branch' => $strategies['branch']] : $strategies;
    }
);

$ll706_github_token = defined('PLUGIN_UPDATE_GITHUB_TOKEN')
    ? PLUGIN_UPDATE_GITHUB_TOKEN
    : getenv('PLUGIN_UPDATE_GITHUB_TOKEN');

if (!empty($ll706_github_token)) {
    $ll706_update_checker->setAuthentication($ll706_github_token);
}

final class LL706_Web_Deflection_Calculator {
    private const VERSION = '0.2.0';

    public function __construct() {
        add_shortcode('web_deflection_calculator', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets(): void {
        wp_register_style('ll706-web-deflection', false, [], self::VERSION);
        wp_enqueue_style('ll706-web-deflection');

        $css = <<<'CSS'
.ll706-wd-wrap{max-width:980px;width:100%;margin:24px auto;padding:18px;border:1px solid #d7d7d7;border-radius:12px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.05);box-sizing:border-box}
.ll706-wd-title{margin:0 0 12px;font-size:26px;line-height:1.2}
.ll706-wd-subtitle{margin:0 0 16px;color:#444;font-size:14px;line-height:1.45}
.ll706-wd-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.ll706-wd-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.ll706-wd-grid-5{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
.ll706-wd-field{display:flex;flex-direction:column;gap:5px}
.ll706-wd-field label{font-weight:600}
.ll706-wd-field input,.ll706-wd-select,.ll706-wd-sign{padding:8px 9px;border:1px solid #bbb;border-radius:8px;font-size:14px;width:100%;box-sizing:border-box}
.ll706-wd-field input[readonly]{background:#f1f1f1;color:#555}
.ll706-wd-field-note{color:#555;font-size:12px;line-height:1.35}
.ll706-wd-measure-control{display:grid;grid-template-columns:48px minmax(0,1fr);gap:0;border:1px solid #bbb;border-radius:8px;background:#fff;overflow:hidden}
.ll706-wd-measure-control .ll706-wd-sign,.ll706-wd-measure-control input{border:0;border-radius:0}
.ll706-wd-measure-control .ll706-wd-sign{background:#f1f1f1;border-right:1px solid #bbb;font-weight:700;text-align:center}
.ll706-wd-measure-control:focus-within{border-color:#1d4ed8;box-shadow:0 0 0 2px rgba(29,78,216,.16)}
.ll706-wd-tabs{display:flex;gap:8px;flex-wrap:wrap}
.ll706-wd-tab{appearance:none;border:1px solid #bbb;border-radius:999px;background:#f7f7f7;color:#222;cursor:pointer;font-size:14px;font-weight:700;padding:8px 14px}
.ll706-wd-tab.is-active{background:#1d4ed8;border-color:#1d4ed8;color:#fff}
.ll706-wd-clear{appearance:none;border:1px solid #a8a8a8;border-radius:999px;background:#fff;color:#222;cursor:pointer;font-size:12px;font-weight:700;margin-left:auto;padding:5px 9px}
.ll706-wd-clear:hover{background:#f1f1f1}
.ll706-wd-section{margin-top:18px}
.ll706-wd-results{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-top:14px}
.ll706-wd-card{padding:12px;border:1px solid #d7d7d7;border-radius:10px;background:#fafafa}
.ll706-wd-card h4{margin:0 0 6px;font-size:16px}
.ll706-wd-card.tir-ok{background:#eaf9ea;border-color:#8bca8b;color:#1f5f1f}
.ll706-wd-card.tir-caution{background:#fff8db;border-color:#e8c64f;color:#604a00}
.ll706-wd-card.tir-danger{background:#ffe9e9;border-color:#e08484;color:#7a1111}
.ll706-wd-card.shim-add{background:#eaf9ea;border-color:#8bca8b;color:#1f5f1f}
.ll706-wd-card.shim-remove{background:#ffe9e9;border-color:#e08484;color:#7a1111}
.ll706-wd-card.shim-zero{background:#fafafa;border-color:#d7d7d7;color:#222}
.ll706-wd-value{font-size:28px;font-weight:700;line-height:1.1}
.ll706-wd-note{margin-top:14px;padding:12px;border-radius:10px;font-weight:600;font-size:14px}
.ll706-wd-note.ok{background:#eaf9ea;border:1px solid #8bca8b;color:#1f5f1f}
.ll706-wd-note.warn{background:#fff1e8;border:1px solid #ffb487;color:#7a3900}
.ll706-wd-submit-row{margin-top:14px}
.ll706-wd-log{margin-top:14px;padding:12px;border:1px solid #d8e0ee;border-radius:10px;background:#f6f8fb}
.ll706-wd-submit{appearance:none;border:1px solid #a8a8a8;border-radius:999px;background:#f1f1f1;color:#222;cursor:pointer;font-size:14px;font-weight:700;padding:8px 14px}
.ll706-wd-submit:hover{background:#e5e5e5}
.ll706-wd-submit:disabled{background:#a8a8a8;border-color:#a8a8a8;cursor:not-allowed}
.ll706-wd-log-list{margin:10px 0 0;padding-left:20px;font-size:13px;line-height:1.45}
.ll706-wd-log-list:empty{display:none}
.ll706-wd-log-message{margin-top:8px;color:#555;font-size:12px;line-height:1.35}
.ll706-wd-log-message:empty{display:none}
.ll706-wd-help{margin-top:16px;padding:12px;background:#f6f8fb;border:1px solid #d8e0ee;border-radius:10px;font-size:14px;line-height:1.45}
.ll706-wd-help ul{margin:8px 0 0 18px}
.ll706-wd-help p{margin:6px 0 0}
.ll706-wd-reference{margin-top:18px}
.ll706-wd-reference-button{display:block;width:100%;padding:0;border:0;background:transparent;cursor:zoom-in}
.ll706-wd-reference img{display:block;width:100%;height:auto;border:1px solid #d7d7d7;border-radius:10px;background:#fff}
.ll706-wd-wrap [data-engine][hidden]{display:none}
.ll706-wd-image-modal[hidden]{display:none}
.ll706-wd-image-modal{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(0,0,0,.82)}
.ll706-wd-image-modal img{display:block;max-width:96vw;max-width:min(96vw,1200px);max-height:88vh;width:auto;height:auto;border:1px solid #fff;border-radius:10px;background:#fff}
.ll706-wd-image-close{position:absolute;top:16px;right:16px;padding:10px 14px;border:1px solid #fff;border-radius:999px;background:#fff;color:#111;cursor:pointer;font-weight:700}
.ll706-wd-badge{display:inline-block;padding:4px 10px;border-radius:999px;background:#1d4ed8;color:#fff;font-size:12px;font-weight:700;letter-spacing:.02em;text-transform:uppercase}
@media (max-width:800px){.ll706-wd-wrap{padding:14px}.ll706-wd-title{font-size:24px}.ll706-wd-grid,.ll706-wd-grid-3{grid-template-columns:1fr}.ll706-wd-clear{margin-left:0}}
@media (max-width:520px){.ll706-wd-grid-5,.ll706-wd-results{grid-template-columns:1fr}.ll706-wd-tab{flex:1}.ll706-wd-clear,.ll706-wd-submit{width:100%}}
CSS;
        wp_add_inline_style('ll706-web-deflection', $css);

        wp_register_script('ll706-web-deflection', false, [], self::VERSION, true);
        wp_enqueue_script('ll706-web-deflection');

$js = <<<'JS'
(function(){
  var typeStorageKey = 'll706-web-deflection-selected-type';

  function isValidType(type){
    return type === 'fdl' || type === 'evo' || type === 'tier4';
  }

  function getSavedType(){
    try {
      var type = window.sessionStorage.getItem(typeStorageKey);
      return isValidType(type) ? type : '';
    } catch (e) {
      return '';
    }
  }

  function saveType(type){
    if (!isValidType(type)) return;
    try {
      window.sessionStorage.setItem(typeStorageKey, type);
    } catch (e) {}
  }

  function toNumber(value){
    var n = parseFloat(value);
    return Number.isFinite(n) ? n : 0;
  }

  function fmt5(n){ return Number(n).toFixed(5); }
  function fmt3(n){ return Number(n).toFixed(3); }
  function fmtSigned5(n){
    var value = Number(n) || 0;
    if (Math.abs(value) === 0) return '0.00000';
    return (value < 0 ? '-' : '+') + fmt5(Math.abs(value));
  }
  function fmtShimInstruction(value){
    if (Math.abs(value) === 0) return '0.000 no change';
    return (value < 0 ? 'REMOVE ' : 'ADD ') + fmt3(Math.abs(value));
  }
  function labelType(type){
    return type === 'tier4' ? 'Tier 4' : type.toUpperCase();
  }
  function updateShimCard(card, value){
    if (!card) return;
    card.classList.remove('shim-add', 'shim-remove', 'shim-zero');
    card.classList.add(value === 0 ? 'shim-zero' : (value < 0 ? 'shim-remove' : 'shim-add'));
  }

  function computeTIR(values){
    var max = Math.max.apply(null, values);
    var min = Math.min.apply(null, values);
    if (min >= 0) return max;
    if (min < 0 && max > 0) return max - min;
    return Math.abs(min);
  }

  function calculateShimAdjustments(type, values, tir){
    var tl = 0;
    var tr = 0;

    if (tir <= 0.0005) return {tl: tl, tr: tr};

    if (type === 'fdl') {
      tl = (-14 * (values.E || 0)) + (7.8 * (values.C || 0));
      tr = (8.6 * (values.E || 0)) + (8.5 * (values.C || 0));
    } else if (type === 'tier4') {
      tl = (-13.3 * (values.E || 0)) + (5.4 * (values.C || 0));
      tr = (5.29 * (values.E || 0)) + (6.4 * (values.C || 0));
    } else {
      tl = (-10.2 * (values.E || 0)) + (4.72 * (values.C || 0));
      tr = (3.5 * (values.E || 0)) + (5.63 * (values.C || 0));
    }

    return {tl: tl, tr: tr};
  }

  function readCalculatorState(wrapper){
    var type = wrapper.querySelector('.ll706-wd-type').value;
    var activeSection = wrapper.querySelector('[data-engine="' + type + '"]');
    var fields = activeSection ? activeSection.querySelectorAll('[data-measure]') : [];
    var values = {};
    var hasUserEntry = false;

    fields.forEach(function(field){
      var signField = field.closest('.ll706-wd-field').querySelector('[data-sign]');
      var sign = signField ? toNumber(signField.value) : 1;
      values[field.getAttribute('data-measure')] = Math.abs(toNumber(field.value)) * sign;
      if (!field.readOnly && field.value.trim() !== '') hasUserEntry = true;
    });

    var tir = computeTIR([values.A || 0, values.B || 0, values.C || 0, values.D || 0, values.E || 0]);
    var adjustments = calculateShimAdjustments(type, values, tir);

    return {type: type, values: values, tir: tir, tl: adjustments.tl, tr: adjustments.tr, hasUserEntry: hasUserEntry};
  }

  function buildMoveSnapshot(state){
    return [
      state.type,
      fmtSigned5(state.values.A || 0),
      fmtSigned5(state.values.B || 0),
      fmtSigned5(state.values.C || 0),
      fmtSigned5(state.values.D || 0),
      fmtSigned5(state.values.E || 0),
      fmt5(state.tir),
      fmtSigned5(state.tl),
      fmtSigned5(state.tr)
    ].join('|');
  }

  function submitMove(wrapper){
    var log = wrapper.querySelector('.ll706-wd-log');
    var list = wrapper.querySelector('.ll706-wd-log-list');
    var message = wrapper.querySelector('.ll706-wd-log-message');
    if (!list || !message) return;
    if (log) log.hidden = false;

    var state = readCalculatorState(wrapper);
    if (!state.hasUserEntry) {
      message.textContent = 'Enter at least one reading before submitting.';
      return;
    }

    if (list.children.length >= 5) {
      message.textContent = 'Maximum 5 moves logged. Clear All to restart.';
      return;
    }

    var snapshot = buildMoveSnapshot(state);
    if (list.getAttribute('data-last-snapshot') === snapshot) {
      message.textContent = 'This move is already logged.';
      return;
    }

    var item = document.createElement('li');
    item.textContent = labelType(state.type)
      + ' | A ' + fmtSigned5(state.values.A || 0)
      + ', B ' + fmtSigned5(state.values.B || 0)
      + ', C ' + fmtSigned5(state.values.C || 0)
      + ', D ' + fmtSigned5(state.values.D || 0)
      + ', E ' + fmtSigned5(state.values.E || 0)
      + ' | TIR ' + fmt5(state.tir)
      + ' | TL ' + fmtShimInstruction(state.tl)
      + ' | TR ' + fmtShimInstruction(state.tr);
    list.appendChild(item);
    list.setAttribute('data-last-snapshot', snapshot);
    message.textContent = 'Move ' + list.children.length + ' of 5 submitted.';
  }

  function updateCalculator(wrapper){
    var type = wrapper.querySelector('.ll706-wd-type').value;

    var activeSection = wrapper.querySelector('[data-engine="' + type + '"]');
    var fields = activeSection ? activeSection.querySelectorAll('[data-measure]') : [];
    var values = {};
    var cCheck = wrapper.querySelector('.ll706-wd-c-check');

    fields.forEach(function(field){
      var signField = field.closest('.ll706-wd-field').querySelector('[data-sign]');
      var sign = signField ? toNumber(signField.value) : 1;
      values[field.getAttribute('data-measure')] = Math.abs(toNumber(field.value)) * sign;
    });

    var tirValues = [values.A || 0, values.B || 0, values.C || 0, values.D || 0, values.E || 0];

    var tir = computeTIR(tirValues);
    var adjustments = calculateShimAdjustments(type, values, tir);
    var tl = adjustments.tl;
    var tr = adjustments.tr;
    var message = '';
    var noteClass = 'ok';

    if (cCheck) {
      cCheck.style.display = type === 'evo' ? 'block' : 'none';
      if (type !== 'evo') cCheck.innerHTML = '';
    }

    if (type === 'fdl') {
      if (tir > 0.0005) {
        message = 'TIR is above 0.0005. Use the calculated shim values from C and E, then remeasure TIR across A, B, C, D, and E.';
        noteClass = 'warn';
      } else {
        message = 'TIR is 0.0005 or less across A, B, C, D, and E. Deflection is within spec and no further action is required.';
      }
    } else if (type === 'tier4') {
      if (tir > 0.0005) {
        message = 'TIR is above 0.0005. Use the calculated Tier 4 shim values from C and E, then remeasure TIR across A, B, C, D, and E.';
        noteClass = 'warn';
      } else {
        message = 'TIR is 0.0005 or less across A, B, C, D, and E. Continue to compressing the web.';
      }
    } else {
      if (tir > 0.0005) {
        message = 'Initial TIR is above 0.0005. Shim accordingly, then remeasure for TIR.';
        noteClass = 'warn';
      } else {
        message = 'TIR is 0.0005 or less. Add the final 0.015 shims, then verify final readings.';
      }

      if (cCheck) {
        if (tir <= 0.0005) {
          cCheck.innerHTML = 'After the final 0.015 shims are added, final readings should target <strong>A = 0</strong> and <strong>C = -0.030</strong>.';
        } else {
          cCheck.innerHTML = 'After TIR is reached, add the final 0.015 shims and verify final readings.';
        }
      }
    }

    wrapper.querySelector('.ll706-wd-tir').textContent = fmt5(tir);
    var tirCard = wrapper.querySelector('.ll706-wd-tir-card');
    if (tirCard) {
      tirCard.classList.remove('tir-ok', 'tir-caution', 'tir-danger');
      tirCard.classList.add(tir <= 0.0005 ? 'tir-ok' : (tir <= 0.0010 ? 'tir-caution' : 'tir-danger'));
    }
    wrapper.querySelector('.ll706-wd-tl').textContent = fmt3(Math.abs(tl));
    wrapper.querySelector('.ll706-wd-tr').textContent = fmt3(Math.abs(tr));
    wrapper.querySelector('.ll706-wd-tl-dir').textContent = tl === 0 ? '—' : (tl < 0 ? 'REMOVE' : 'ADD');
    wrapper.querySelector('.ll706-wd-tr-dir').textContent = tr === 0 ? '—' : (tr < 0 ? 'REMOVE' : 'ADD');
    updateShimCard(wrapper.querySelector('.ll706-wd-tl-card'), tl);
    updateShimCard(wrapper.querySelector('.ll706-wd-tr-card'), tr);

    var note = wrapper.querySelector('.ll706-wd-note');
    note.className = 'll706-wd-note ' + noteClass;
    note.textContent = message;
  }

  function syncVisibleFields(wrapper){
    var type = wrapper.querySelector('.ll706-wd-type').value;
    var isCalculatorType = isValidType(type);
    wrapper.querySelectorAll('.ll706-wd-tab').forEach(function(tab){
      var isActive = tab.getAttribute('data-type') === type;
      tab.classList.toggle('is-active', isActive);
      tab.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
    wrapper.querySelectorAll('[data-engine]').forEach(function(block){
      block.hidden = block.getAttribute('data-engine') !== type;
    });
    wrapper.querySelectorAll('[data-calculator-only]').forEach(function(block){
      var isEmptyLog = block.classList.contains('ll706-wd-log')
        && !block.querySelector('.ll706-wd-log-list').children.length
        && !block.querySelector('.ll706-wd-log-message').textContent.trim();
      block.hidden = !isCalculatorType || isEmptyLog;
    });
    if (isCalculatorType) updateCalculator(wrapper);
  }

  document.addEventListener('input', function(e){
    var wrapper = e.target.closest('.ll706-wd-wrap');
    if (!wrapper) return;
    if (e.target.matches('[data-measure]')) updateCalculator(wrapper);
  });

  document.addEventListener('change', function(e){
    var wrapper = e.target.closest('.ll706-wd-wrap');
    if (!wrapper) return;
    if (e.target.matches('.ll706-wd-type')) {
      saveType(e.target.value);
      syncVisibleFields(wrapper);
    }
    if (e.target.matches('[data-sign]')) updateCalculator(wrapper);
  });

  document.addEventListener('click', function(e){
    var referenceButton = e.target.closest('.ll706-wd-reference-button');
    if (referenceButton) {
      var reference = referenceButton.closest('.ll706-wd-reference');
      var modal = reference ? reference.querySelector('.ll706-wd-image-modal') : null;
      if (modal) {
        var modalImage = modal.querySelector('img[data-src]');
        if (modalImage && !modalImage.getAttribute('src')) {
          modalImage.setAttribute('src', modalImage.getAttribute('data-src'));
        }
        modal.hidden = false;
      }
      return;
    }

    var closeButton = e.target.closest('.ll706-wd-image-close');
    if (closeButton) {
      closeButton.closest('.ll706-wd-image-modal').hidden = true;
      return;
    }

    if (e.target.matches('.ll706-wd-image-modal')) {
      e.target.hidden = true;
      return;
    }

    var submitButton = e.target.closest('.ll706-wd-submit');
    if (submitButton) {
      var submitWrapper = submitButton.closest('.ll706-wd-wrap');
      if (submitWrapper) submitMove(submitWrapper);
      return;
    }

    var clearButton = e.target.closest('.ll706-wd-clear');
    if (clearButton) {
      var clearWrapper = clearButton.closest('.ll706-wd-wrap');
      clearWrapper.querySelectorAll('[data-engine]').forEach(function(section){
        section.querySelectorAll('[data-measure]').forEach(function(field){
          field.value = field.readOnly ? '0' : '';
        });
        section.querySelectorAll('[data-sign]').forEach(function(field){
          field.value = '1';
        });
      });
      var list = clearWrapper.querySelector('.ll706-wd-log-list');
      var message = clearWrapper.querySelector('.ll706-wd-log-message');
      if (list) {
        list.innerHTML = '';
        list.removeAttribute('data-last-snapshot');
      }
      if (message) message.textContent = '';
      var log = clearWrapper.querySelector('.ll706-wd-log');
      if (log) log.hidden = true;
      updateCalculator(clearWrapper);
      return;
    }

    var tab = e.target.closest('.ll706-wd-tab');
    if (!tab) return;

    var wrapper = tab.closest('.ll706-wd-wrap');
    if (!wrapper) return;

    wrapper.querySelector('.ll706-wd-type').value = tab.getAttribute('data-type');
    saveType(wrapper.querySelector('.ll706-wd-type').value);
    syncVisibleFields(wrapper);
  });

  document.addEventListener('keydown', function(e){
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.ll706-wd-image-modal').forEach(function(modal){
      modal.hidden = true;
    });
  });

  document.querySelectorAll('.ll706-wd-wrap').forEach(function(wrapper){
    var savedType = getSavedType();
    if (savedType) wrapper.querySelector('.ll706-wd-type').value = savedType;
    syncVisibleFields(wrapper);
  });
})();
JS;
        wp_add_inline_script('ll706-web-deflection', $js);
    }

    public function render_shortcode($atts = []): string {
        $atts = shortcode_atts([
            'type' => 'fdl',
            'title' => 'Web Deflection Calculator',
        ], $atts, 'web_deflection_calculator');

        $defaultType = in_array(strtolower($atts['type']), ['fdl', 'evo', 'tier4'], true) ? strtolower($atts['type']) : 'fdl';

        ob_start();
        ?>
        <div class="ll706-wd-wrap">
            <span class="ll706-wd-badge">Shim Calculator</span>
            <h2 class="ll706-wd-title"><?php echo esc_html($atts['title']); ?></h2>
            <p class="ll706-wd-subtitle">A is the zero reference. For the other readings, choose + or -, then enter the decimal amount.</p>

            <div class="ll706-wd-tabs" role="group" aria-label="Engine Type">
                <button type="button" class="ll706-wd-tab<?php echo $defaultType === 'fdl' ? ' is-active' : ''; ?>" data-type="fdl" aria-pressed="<?php echo $defaultType === 'fdl' ? 'true' : 'false'; ?>">FDL</button>
                <button type="button" class="ll706-wd-tab<?php echo $defaultType === 'evo' ? ' is-active' : ''; ?>" data-type="evo" aria-pressed="<?php echo $defaultType === 'evo' ? 'true' : 'false'; ?>">EVO</button>
                <button type="button" class="ll706-wd-tab<?php echo $defaultType === 'tier4' ? ' is-active' : ''; ?>" data-type="tier4" aria-pressed="<?php echo $defaultType === 'tier4' ? 'true' : 'false'; ?>">Tier 4</button>
                <button type="button" class="ll706-wd-clear">Clear All</button>
                <input type="hidden" class="ll706-wd-type" value="<?php echo esc_attr($defaultType); ?>">
            </div>

            <div class="ll706-wd-section" data-engine="fdl"<?php echo $defaultType !== 'fdl' ? ' hidden' : ''; ?>>
                <div class="ll706-wd-grid-5">
                    <div class="ll706-wd-field">
                        <label>A (250°)</label>
                        <input type="number" step="0.00001" min="0" data-measure="A" value="0" placeholder="0" readonly>
                        <div class="ll706-wd-field-note">Zero reference - no entry required.</div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>B (220°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="B reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="B" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>C (190°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="C reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="C" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>D (160°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="D reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="D" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>E (130°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="E reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="E" placeholder="0.00000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ll706-wd-section" data-engine="evo"<?php echo $defaultType !== 'evo' ? ' hidden' : ''; ?>>
                <div class="ll706-wd-grid-5">
                    <div class="ll706-wd-field">
                        <label>A (460°)</label>
                        <input type="number" step="0.00001" min="0" data-measure="A" value="0" placeholder="0" readonly>
                        <div class="ll706-wd-field-note">Zero reference - no entry required.</div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>B (410°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="B reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="B" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>C (330°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="C reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="C" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>D (250°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="D reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="D" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>E (200°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="E reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="E" placeholder="0.00000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ll706-wd-section" data-engine="tier4"<?php echo $defaultType !== 'tier4' ? ' hidden' : ''; ?>>
                <div class="ll706-wd-grid-5">
                    <div class="ll706-wd-field">
                        <label>A (110°)</label>
                        <input type="number" step="0.00001" min="0" data-measure="A" value="0" placeholder="0" readonly>
                        <div class="ll706-wd-field-note">Zero reference - no entry required.</div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>B (70°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="B reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="B" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>C (700°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="C reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="C" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>D (620°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="D reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="D" placeholder="0.00000">
                        </div>
                    </div>
                    <div class="ll706-wd-field">
                        <label>E (560°)</label>
                        <div class="ll706-wd-measure-control">
                            <select class="ll706-wd-sign" data-sign aria-label="E reading sign">
                                <option value="1">+</option>
                                <option value="-1">-</option>
                            </select>
                            <input type="number" step="0.00001" min="0" inputmode="decimal" data-measure="E" placeholder="0.00000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ll706-wd-submit-row" data-calculator-only>
                <button type="button" class="ll706-wd-submit">Submit</button>
            </div>

            <div class="ll706-wd-results" data-calculator-only>
                <div class="ll706-wd-card ll706-wd-tir-card tir-ok">
                    <h4>TIR</h4>
                    <div class="ll706-wd-value ll706-wd-tir">0.00000</div>
                </div>
                <div class="ll706-wd-card ll706-wd-tl-card shim-zero">
                    <h4>Top Left Shim</h4>
                    <div class="ll706-wd-value ll706-wd-tl">0.000</div>
                    <div>Direction: <strong class="ll706-wd-tl-dir">—</strong></div>
                </div>
                <div class="ll706-wd-card ll706-wd-tr-card shim-zero">
                    <h4>Top Right Shim</h4>
                    <div class="ll706-wd-value ll706-wd-tr">0.000</div>
                    <div>Direction: <strong class="ll706-wd-tr-dir">—</strong></div>
                </div>
            </div>

            <div class="ll706-wd-note ok" data-calculator-only>TIR is 0.0005 or less. Deflection is within spec and no further action is required.</div>

            <div class="ll706-wd-log" data-calculator-only hidden>
                <ol class="ll706-wd-log-list"></ol>
                <div class="ll706-wd-log-message" aria-live="polite"></div>
            </div>

            <div class="ll706-wd-help" data-engine="fdl"<?php echo $defaultType !== 'fdl' ? ' hidden' : ''; ?>>
                <strong>NOTES</strong>
                <p>Normal bottom shim thickness: 0.090.</p>
                <p>Adjust top shims, not bottom flange shims.</p>
                <p>Verify thickness of each shim, then add. Document your work and do not use a Vernier blade caliper.</p>
                <p>Top left ear: (-14 x E) + (7.8 x C)</p>
                <p>Top right ear: (8.6 x E) + (8.5 x C)</p>
                <p>Top ear shim packs cannot exceed 0.120.</p>
                <p>Difference between the top two shim packs should not exceed 0.060.</p>
                <p>If shim pack rules cannot be satisfied, an error exists somewhere and must be investigated.</p>
                <p><strong>TIR</strong></p>
                <p>Total Indicator Reading is the spread between the highest and lowest relevant indicator readings. For FDL, TIR uses A, B, C, D, and E, with A fixed at zero.</p>
                <p>Example: A = 0, C = -0.0012, E = +0.0008. TIR = 0.0008 - (-0.0012) = 0.0020.</p>
                <p>All positive example: A = 0, C = +0.0010, E = +0.0018. TIR = 0.0018.</p>
                <p>All negative example: A = 0, C = -0.0010, E = -0.0018. TIR = 0.0018.</p>
                <p><strong>Torque values</strong></p>
                <p>Alternator mounting bolts (8): 625-675 lb-ft.</p>
                <p>Stator bolts: 1049-1160 lb-ft with hardened washers.</p>
                <p>Engine to pedestal bolts (2): 600 lb-ft.</p>
                <p><strong>Jacking bolt clearance</strong></p>
                <p>Engine cold at both ends and side: 0.010 in.</p>
                <p>Jacking plate kit not used: 0.050 in. clearance.</p>
                <p>Tack weld wedges in place to platform foot mounting.</p>
                <p><strong>Dial indicator sign convention</strong></p>
                <p>Observe needle movement as the probe is pressed and released. Regardless of plus, minus, or missing markings on the indicator face, clockwise needle movement as the probe is pressed is increasing compression, and counterclockwise movement as the probe is released is increasing spread.</p>
                <p>After preloading the probe and zeroing the gauge, record face-on indicator readings to the right of zero as negative values and face-on readings to the left of zero as positive values.</p>
                <p><strong>Measurement rules</strong></p>
                <ul>
                    <li>Enter the decimal reading amount as a positive number.</li>
                    <li>Select + or - to set the sign used by the calculator.</li>
                    <li>The movement direction overrides markings or indications on the gauge face.</li>
                </ul>
            </div>

            <div class="ll706-wd-help" data-engine="evo"<?php echo $defaultType !== 'evo' ? ' hidden' : ''; ?>>
                <strong>NOTES</strong>
                <p>Begin with 0.087 shims.</p>
                <p>Top left ear: (-10.2 x E) + (4.72 x C)</p>
                <p>Top right ear: (3.5 x E) + (5.63 x C)</p>
                <p>After TIR is reached, add the final 0.015 shims.</p>
                <p>Final readings target: A = 0, C = -0.030.</p>
                <p><strong>TIR</strong></p>
                <p>Total Indicator Reading is the spread between the highest and lowest relevant indicator readings. For EVO, TIR uses A, B, C, D, and E, with A fixed at zero.</p>
                <p>Example: A = 0, C = -0.0012, E = +0.0008. TIR = 0.0008 - (-0.0012) = 0.0020.</p>
                <p>All positive example: A = 0, B = +0.0004, C = +0.0010, D = +0.0014, E = +0.0018. TIR = 0.0018.</p>
                <p>All negative example: A = 0, B = -0.0004, C = -0.0010, D = -0.0014, E = -0.0018. TIR = 0.0018.</p>
                <p><strong>Dial indicator sign convention</strong></p>
                <p>Observe needle movement as the probe is pressed and released. Regardless of plus, minus, or missing markings on the indicator face, clockwise needle movement as the probe is pressed is increasing compression, and counterclockwise movement as the probe is released is increasing spread.</p>
                <p>After preloading the probe and zeroing the gauge, record face-on indicator readings to the right of zero as negative values and face-on readings to the left of zero as positive values.</p>
                <p><strong>Measurement rules</strong></p>
                <ul>
                    <li>Enter the decimal reading amount as a positive number.</li>
                    <li>Select + or - to set the sign used by the calculator.</li>
                    <li>The movement direction overrides markings or indications on the gauge face.</li>
                </ul>
            </div>

            <div class="ll706-wd-help" data-engine="tier4"<?php echo $defaultType !== 'tier4' ? ' hidden' : ''; ?>>
                <strong>NOTES</strong>
                <p>Crankshaft positions: A = 110°, B = 70°, C = 700°, D = 620°, E = 560°.</p>
                <p>Top left flange shim adjustment: (-13.3 x E) + (5.4 x C)</p>
                <p>Top right flange shim adjustment: (5.29 x E) + (6.4 x C)</p>
                <p>Negative formula results require removing that amount of shim thickness. Positive formula results require adding shims.</p>
                <p>For TIR greater than 0.0005 in., shim pack thickness adjustment is required. For TIR 0.0005 in. or less, continue to compressing the web.</p>
                <p>After TIR is reached, add the final 0.015 shims.</p>
                <p>Final readings target: A = 0, C = -0.030.</p>
                <p><strong>Shim pack rules</strong></p>
                <p>EVO12 cylinder: do not exceed 0.135 in. at either top ear.</p>
                <p>EVO16 cylinder: do not exceed 0.120 in. at either top ear.</p>
                <p>Side-to-side difference in shim pack thickness must be less than 0.060 in.</p>
                <p>Do not use wrinkled shims. Keep thickest shims toward the engine side and sandwich thin shims between thick shims.</p>
                <p>Do not adjust bottom flange shim thickness to achieve deflection TIR.</p>
                <p><strong>Torque and measurement</strong></p>
                <p>Always torque the top flange bolts before taking alignment readings.</p>
                <p>Torque top flange bolts to 650 ± 25 lb-ft.</p>
                <p>Measure shims individually with a 0-1 in. outside-diameter micrometer, not a blade caliper. Add individual shim measurements for total shim pack thickness.</p>
                <p><strong>Measurement rules</strong></p>
                <ul>
                    <li>Enter the decimal reading amount as a positive number.</li>
                    <li>Select + or - to set the sign used by the calculator.</li>
                    <li>The movement direction overrides markings or indications on the gauge face.</li>
                </ul>
            </div>

            <div class="ll706-wd-help ll706-wd-c-check" data-calculator-only style="<?php echo $defaultType === 'evo' ? 'display:block' : 'display:none'; ?>"></div>
            <div class="ll706-wd-reference">
                <button type="button" class="ll706-wd-reference-button" aria-label="Enlarge micrometer reading reference">
                    <img src="<?php echo esc_url(plugins_url('MicrometerReadingReference.png', __FILE__)); ?>" alt="Micrometer reading reference" width="1682" height="882" loading="lazy" decoding="async" fetchpriority="low">
                </button>
                <div class="ll706-wd-image-modal" hidden>
                    <button type="button" class="ll706-wd-image-close">Close</button>
                    <img data-src="<?php echo esc_url(plugins_url('MicrometerReadingReference.png', __FILE__)); ?>" alt="Enlarged micrometer reading reference" width="1682" height="882" decoding="async">
                </div>
            </div>
            <div class="ll706-wd-reference" data-calculator-only>
                <button type="button" class="ll706-wd-reference-button" aria-label="Enlarge dial indicator sign convention reference">
                    <img src="<?php echo esc_url(plugins_url('DialIndicatorSignConvention.png', __FILE__)); ?>" alt="Dial indicator sign convention reference" width="1112" height="836" loading="lazy" decoding="async" fetchpriority="low">
                </button>
                <div class="ll706-wd-image-modal" hidden>
                    <button type="button" class="ll706-wd-image-close">Close</button>
                    <img data-src="<?php echo esc_url(plugins_url('DialIndicatorSignConvention.png', __FILE__)); ?>" alt="Enlarged dial indicator sign convention reference" width="1112" height="836" decoding="async">
                </div>
            </div>
            <div class="ll706-wd-reference" data-engine="evo"<?php echo $defaultType !== 'evo' ? ' hidden' : ''; ?>>
                <button type="button" class="ll706-wd-reference-button" aria-label="Enlarge EVO alignment guidelines reference">
                    <img src="<?php echo esc_url(plugins_url('AlignmentGuidelinesEVO.png', __FILE__)); ?>" alt="EVO alignment guidelines reference" width="685" height="491" loading="lazy" decoding="async" fetchpriority="low">
                </button>
                <div class="ll706-wd-image-modal" hidden>
                    <button type="button" class="ll706-wd-image-close">Close</button>
                    <img data-src="<?php echo esc_url(plugins_url('AlignmentGuidelinesEVO.png', __FILE__)); ?>" alt="Enlarged EVO alignment guidelines reference" width="685" height="491" decoding="async">
                </div>
            </div>
            <div class="ll706-wd-reference" data-engine="tier4"<?php echo $defaultType !== 'tier4' ? ' hidden' : ''; ?>>
                <button type="button" class="ll706-wd-reference-button" aria-label="Enlarge Tier 4 final reading target reference">
                    <img src="<?php echo esc_url(plugins_url('Tier4FinalReadingTarget.png', __FILE__)); ?>" alt="Tier 4 final reading target reference" width="1682" height="930" loading="lazy" decoding="async" fetchpriority="low">
                </button>
                <div class="ll706-wd-image-modal" hidden>
                    <button type="button" class="ll706-wd-image-close">Close</button>
                    <img data-src="<?php echo esc_url(plugins_url('Tier4FinalReadingTarget.png', __FILE__)); ?>" alt="Enlarged Tier 4 final reading target reference" width="1682" height="930" decoding="async">
                </div>
            </div>
            <div class="ll706-wd-reference" data-engine="fdl"<?php echo $defaultType !== 'fdl' ? ' hidden' : ''; ?>>
                <button type="button" class="ll706-wd-reference-button" aria-label="Enlarge Dash 9 alternator mounting reference diagram">
                    <img src="<?php echo esc_url(plugins_url('Dash9Alt_Mounting.png', __FILE__)); ?>" alt="Dash 9 alternator mounting reference diagram" width="688" height="384" loading="lazy" decoding="async" fetchpriority="low">
                </button>
                <div class="ll706-wd-image-modal" hidden>
                    <button type="button" class="ll706-wd-image-close">Close</button>
                    <img data-src="<?php echo esc_url(plugins_url('Dash9Alt_Mounting.png', __FILE__)); ?>" alt="Enlarged Dash 9 alternator mounting reference diagram" width="688" height="384" decoding="async">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new LL706_Web_Deflection_Calculator();
