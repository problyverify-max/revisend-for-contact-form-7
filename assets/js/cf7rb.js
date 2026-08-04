(function () {
	'use strict';

	if (!window.cf7rbConfig || !window.cf7rbConfig.forms) {
		return;
	}

	var cfg = window.cf7rbConfig;
	var INJECTED = 'cf7rb-injected';

	function findFormId(form) {
		var field = form.querySelector('input[name="_wpcf7"]');

		return field ? field.value : '';
	}

	function isEnabled(formId) {
		return Object.prototype.hasOwnProperty.call(cfg.forms, formId);
	}

	function setStartTime(form) {
		var start = form.querySelector('input[name="cf7rb_start"]');

		if (start) {
			start.value = String(Date.now());
		}
	}

	function removeInjected(form) {
		form.querySelectorAll('.' + INJECTED).forEach(function (el) {
			el.parentNode.removeChild(el);
		});
	}

	function clearInvalid(form) {
		form.querySelectorAll('.cf7rb-invalid').forEach(function (el) {
			el.classList.remove('cf7rb-invalid');
		});
	}

	function showInvalid(form, name) {
		var selector = '.wpcf7-form-control-wrap[data-name="' + window.CSS.escape(name) + '"]';
		var wrap = form.querySelector(selector);

		if (!wrap) {
			return;
		}

		wrap.classList.add('cf7rb-invalid');

		var input = wrap.querySelector('input, textarea, select');

		if (input && input.focus) {
			input.focus();
		}
	}

	function makeSummary() {
		var el = document.createElement('div');

		el.className = 'cf7rb-summary';
		el.style.display = 'none';

		return el;
	}

	function showMessage(summary, message, isError) {
		summary.innerHTML = '';

		var p = document.createElement('p');

		p.className = isError ? 'cf7rb-error' : 'cf7rb-message';
		p.textContent = message;

		summary.appendChild(p);
		summary.style.display = 'block';
	}

	function validateForm(form, formId) {
		try {
			if (!window.swv || !window.wpcf7 || !window.wpcf7.schemas) {
				return null;
			}

			var schema = window.wpcf7.schemas.get(String(formId));

			if (!schema || !schema.rules) {
				return null;
			}

			var result = swv.validate(schema, new FormData(form), {});

			if (!result || result.size === 0) {
				return null;
			}

			var first = result.entries().next().value;

			return {
				name: first[0],
				error: first[1] && first[1].error
			};
		} catch (error) {
			return null;
		}
	}

	function doReview(form, summary, state) {
		var formId = state.formId;
		var submitBtn = form.querySelector('.wpcf7-submit');

		if (submitBtn) {
			submitBtn.disabled = true;
		}

		removeInjected(form);
		clearInvalid(form);

		var invalid = validateForm(form, formId);

		if (invalid) {
			if (submitBtn) {
				submitBtn.disabled = false;
			}

			showInvalid(form, invalid.name);
			showMessage(summary, invalid.error || cfg.labels.error, true);

			return;
		}

		var fd = new FormData();
		var original = new FormData(form);

		original.forEach(function (value, key) {
			if (key.indexOf('_') !== 0) {
				fd.append(key, value);
			}
		});

		fd.append('action', 'cf7rb_review');
		fd.append('nonce', cfg.nonce);
		fd.append('cf7rb_form', formId);

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			body: fd,
			credentials: 'same-origin'
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (submitBtn) {
					submitBtn.disabled = false;
				}

				if (!data.success) {
					if (data.data && data.data.fields && data.data.fields.length) {
						showInvalid(form, data.data.fields[0]);
					}

					showMessage(summary, (data.data && data.data.message) || cfg.labels.error, true);

					return;
				}

				state.token = data.data.token;
				state.files = data.data.files || {};

				summary.innerHTML = data.data.summary;
				form.style.display = 'none';
				summary.style.display = 'block';
			})
			.catch(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
				}

				showMessage(summary, cfg.labels.error, true);
			});
	}

	function doConfirm(form, summary, state) {
		stripFileRules(state.formId);

		removeInjected(form);

		var token = document.createElement('input');

		token.type = 'hidden';
		token.name = 'cf7rb_token';
		token.value = state.token;
		token.className = INJECTED;
		form.appendChild(token);

		Object.keys(state.files).forEach(function (field) {
			var input = document.createElement('input');

			input.type = 'hidden';
			input.name = field;
			input.value = state.files[field];
			input.className = INJECTED;
			form.appendChild(input);
		});

		form.style.display = '';
		summary.style.display = 'none';
		form.dataset.cf7rbConfirm = '1';

		if (form.requestSubmit) {
			form.requestSubmit();
		} else {
			form.submit();
		}

		delete form.dataset.cf7rbConfirm;
		state.files = {};
	}

	function stripFileRules(formId) {
		try {
			var schemas = window.wpcf7 && window.wpcf7.schemas;

			if (!schemas) {
				return;
			}

			var schema = schemas.get(String(formId));

			if (!schema) {
				return;
			}

			var skip = {
				requiredfile: 1,
				file: 1,
				maxfilesize: 1,
				minfilesize: 1,
				maxitems: 1,
				minitems: 1
			};

			if (Array.isArray(schema.rules)) {
				var filtered = schema.rules.filter(function (rule) {
					return !skip[rule.rule];
				});

				schema.rules.splice.apply(schema.rules, [0, schema.rules.length].concat(filtered));
			} else if (schema.tree instanceof Map) {
				schema.tree.forEach(function (rules, field) {
					if (Array.isArray(rules)) {
						var kept = rules.filter(function (rule) {
							return !skip[rule.rule];
						});

						schema.tree.set(field, kept);
					}
				});
			}
		} catch (error) {
			return;
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('form.wpcf7-form').forEach(function (form) {
			var formId = findFormId(form);

			if (!formId || !isEnabled(formId)) {
				return;
			}

			var state = { formId: formId, token: '', files: {} };
			var summary = makeSummary();

			form.parentNode.insertBefore(summary, form.nextSibling);
			setStartTime(form);

			summary.addEventListener('click', function (event) {
				if (event.target.closest('.cf7rb-edit')) {
					form.style.display = '';
					summary.style.display = 'none';
				} else if (event.target.closest('.cf7rb-confirm')) {
					doConfirm(form, summary, state);
				}
			});

			form.addEventListener(
				'submit',
				function (event) {
					if (form.dataset.cf7rbConfirm === '1') {
						return;
					}

					event.preventDefault();
					event.stopPropagation();

					doReview(form, summary, state);
				},
				true
			);
		});

		document.addEventListener('wpcf7submit', function (event) {
			var form = event.target.closest ? event.target.closest('form.wpcf7-form') : null;

			if (form) {
				delete form.dataset.cf7rbConfirm;
			}
		});

		document.addEventListener('wpcf7reset', function (event) {
			var form = event.target.closest ? event.target.closest('form.wpcf7-form') : null;

			if (form) {
				delete form.dataset.cf7rbConfirm;
			}
		});
	});
})();
