/**
 * Клиентская часть ItCube.
 *
 * Библиотек нет намеренно: всё, что здесь нужно, — это отступ по Tab, нумерация
 * строк, опрос результата проверки и таймер. Ради этого тянуть редактор кода на
 * несколько сотен килобайт в браузер школьника не за что.
 */

/** Номера строк рядом с полем ввода. */
function attachGutter(textarea, gutter) {
    const paint = () => {
        const lines = textarea.value.split('\n').length;
        gutter.textContent = Array.from({ length: lines }, (_, i) => i + 1).join('\n');
    };

    textarea.addEventListener('input', paint);
    textarea.addEventListener('scroll', () => {
        gutter.scrollTop = textarea.scrollTop;
    });
    paint();
}

/** Tab ставит отступ, а не уводит фокус на следующую кнопку. */
function attachTabIndent(textarea) {
    textarea.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab' || event.ctrlKey || event.altKey) {
            return;
        }

        event.preventDefault();

        const { selectionStart: start, selectionEnd: end, value } = textarea;

        if (event.shiftKey) {
            // Shift+Tab снимает один уровень отступа у начала строки.
            const lineStart = value.lastIndexOf('\n', start - 1) + 1;
            const removed = value.slice(lineStart, lineStart + 4).match(/^ {1,4}/);

            if (removed) {
                textarea.value = value.slice(0, lineStart) + value.slice(lineStart + removed[0].length);
                textarea.selectionStart = textarea.selectionEnd = start - removed[0].length;
            }
        } else {
            textarea.value = value.slice(0, start) + '    ' + value.slice(end);
            textarea.selectionStart = textarea.selectionEnd = start + 4;
        }

        textarea.dispatchEvent(new Event('input'));
    });
}

function csrf() {
    return document.querySelector('input[name="_token"]')?.value ?? '';
}

function setupEditor(root) {
    const textarea = root.querySelector('[data-code]');
    const gutter = root.querySelector('[data-gutter]');
    const counter = root.querySelector('[data-char-count]');
    const runButton = root.querySelector('[data-run]');
    const stdinField = root.querySelector('[data-stdin]');
    const outputBox = root.querySelector('[data-run-output]');
    const outputText = root.querySelector('[data-run-text]');

    if (!textarea) {
        return;
    }

    attachGutter(textarea, gutter);
    attachTabIndent(textarea);

    const count = () => {
        if (counter) {
            counter.textContent = `${textarea.value.length}`;
        }
    };
    textarea.addEventListener('input', count);
    count();

    const run = async () => {
        runButton.disabled = true;
        const wasLabel = runButton.textContent;
        runButton.textContent = runButton.dataset.busy || 'Запускаю…';
        outputBox.hidden = false;
        outputBox.classList.remove('hidden');
        outputText.textContent = '';

        try {
            const response = await fetch(root.dataset.runUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({
                    code: textarea.value,
                    // Точка с запятой в поле ввода — это перевод строки: набирать
                    // многострочный ввод в однострочном поле иначе невозможно.
                    stdin: (stdinField?.value ?? '').split(';').join('\n'),
                }),
            });
            const result = await response.json();
            outputText.textContent = result.output || '—';
        } catch (error) {
            outputText.textContent = 'Не удалось связаться с сервером';
        } finally {
            runButton.disabled = false;
            runButton.textContent = wasLabel;
        }
    };

    runButton?.addEventListener('click', run);

    textarea.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
            event.preventDefault();
            run();
        }
    });

    if (root.dataset.pollUrl) {
        pollSubmission(root, root.dataset.pollUrl);
    }
}

/**
 * Опрос результата проверки.
 *
 * Работа проверяется в очереди: одна сборка на площадке идёт секунды, а на Go —
 * около двадцати, и держать открытый запрос всё это время нельзя. Интервал
 * растёт, чтобы долгая проверка не превращалась в сотню запросов.
 */
async function pollSubmission(root, url) {
    const box = root.querySelector('[data-result]');
    let delay = 1200;

    box.innerHTML = `<div class="flex items-center gap-3 text-sm">
        <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent opacity-40"></span>
        <span>${box.dataset.pendingLabel || 'Проверяется…'}</span>
    </div>`;

    for (let attempt = 0; attempt < 60; attempt++) {
        await new Promise((resolve) => setTimeout(resolve, delay));
        delay = Math.min(delay * 1.3, 6000);

        let data;
        try {
            data = await (await fetch(url, { headers: { Accept: 'application/json' } })).json();
        } catch (error) {
            continue;
        }

        if (!data.pending) {
            // Разметка результата живёт в шаблоне, а не здесь: перерисовка
            // страницы дешевле, чем вторая копия той же вёрстки на javascript.
            window.location.reload();
            return;
        }
    }
}

/** Таймер теста: когда время выходит, ответы отправляются сами. */
function setupQuizTimer(form) {
    const deadline = form.dataset.deadline ? new Date(form.dataset.deadline) : null;
    const display = form.querySelector('[data-timer]');

    if (!deadline || !display) {
        return;
    }

    let submitted = false;

    const tick = () => {
        const left = Math.max(0, Math.round((deadline - new Date()) / 1000));
        const minutes = String(Math.floor(left / 60)).padStart(2, '0');
        const seconds = String(left % 60).padStart(2, '0');
        display.textContent = `${minutes}:${seconds}`;

        if (left <= 60) {
            display.classList.add('text-rose-600', 'dark:text-rose-400');
        }

        if (left === 0 && !submitted) {
            submitted = true;
            form.submit();
        }
    };

    tick();
    setInterval(tick, 1000);
}

document.querySelectorAll('[data-editor]').forEach(setupEditor);
document.querySelectorAll('[data-quiz]').forEach(setupQuizTimer);
