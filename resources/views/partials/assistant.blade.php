@auth
<div id="assistant-root">
    <button id="assistant-toggle" hidden onclick="assistantToggle()" title="المساعد الذكي">
        🤖
    </button>

    <div id="assistant-panel" style="display:none;">
        <div class="assistant-header">
            <span>🤖 المساعد الذكي المحاسبي</span>
            <div class="d-flex gap-2 align-items-center">
                <span id="assistant-listening" style="display:none; color:#ff6b6b; font-size:12px;">🎙️ يستمع...</span>
                <button onclick="assistantToggle()" class="assistant-close">✕</button>
            </div>
        </div>

        <div id="assistant-messages"></div>

        <div id="assistant-suggestions" class="assistant-suggestions"></div>

        <div class="assistant-input">
            <button onclick="startVoice()" class="voice-btn" title="تحدث بصوتك">🎙️</button>
            <input type="text" id="assistant-question" placeholder="اكتب سؤالك أو أمر..."
                   onkeydown="if(event.key==='Enter') assistantSend()" autocomplete="off">
            <button onclick="assistantSend()" class="send-btn">إرسال</button>
        </div>
    </div>
</div>

<style>
    #assistant-toggle {
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: none;
        background: var(--accent, #2563eb);
        color: #fff;
        font-size: 26px;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(0,0,0,.3);
        z-index: 1060;
        transition: transform .2s, box-shadow .2s;
    }

    #assistant-toggle:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(0,0,0,.4); }

    #assistant-panel {
        position: fixed;
        bottom: 88px;
        left: 20px;
        width: 400px;
        max-width: calc(100vw - 40px);
        height: 540px;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 18px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 1060;
        box-shadow: 0 12px 40px rgba(0,0,0,.3);
    }

    .assistant-header {
        background: var(--accent, #2563eb);
        color: #fff;
        padding: 13px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
        font-size: 14px;
    }

    .assistant-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 16px;
        cursor: pointer;
    }

    #assistant-messages {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .assistant-msg {
        max-width: 88%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 13px;
        white-space: pre-line;
        line-height: 1.8;
        animation: fadeIn .3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .assistant-msg.user {
        align-self: flex-end;
        background: var(--accent, #2563eb);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .assistant-msg.bot {
        align-self: flex-start;
        background: rgba(128,128,128,.12);
        border-bottom-left-radius: 4px;
    }

    .assistant-link-btn {
        display: inline-block;
        margin-top: 8px;
        padding: 8px 16px;
        background: var(--accent, #2563eb);
        color: #fff !important;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all .2s;
        border: none;
    }

    .assistant-link-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37,99,235,.4);
    }

    .assistant-link-btn:active {
        transform: translateY(0);
    }

    .assistant-text-link {
        color: var(--accent, #2563eb);
        text-decoration: underline;
        cursor: pointer;
        font-weight: 500;
    }

    .assistant-text-link:hover {
        text-decoration: none;
    }

    .assistant-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 10px 14px;
        border-top: 1px solid var(--bs-border-color);
        max-height: 120px;
        overflow-y: auto;
    }

    .assistant-suggestions button {
        border: 1px solid var(--bs-border-color);
        background: transparent;
        color: inherit;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        cursor: pointer;
        transition: all .15s;
    }

    .assistant-suggestions button:hover {
        background: var(--accent, #2563eb);
        color: #fff;
        border-color: var(--accent, #2563eb);
    }

    .assistant-input {
        display: flex;
        gap: 6px;
        padding: 12px 14px;
        border-top: 1px solid var(--bs-border-color);
        align-items: center;
    }

    .voice-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: rgba(128,128,128,.15);
        font-size: 18px;
        cursor: pointer;
        transition: all .2s;
        flex-shrink: 0;
    }

    .voice-btn:hover { background: rgba(255,0,0,.2); }
    .voice-btn.listening { background: #ff4444; animation: pulse 1s infinite; }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .assistant-input input {
        flex: 1;
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 9px 12px;
        background: transparent;
        color: inherit;
        font-size: 13px;
    }

    .send-btn {
        border: none;
        background: var(--accent, #2563eb);
        color: #fff;
        border-radius: 12px;
        padding: 9px 16px;
        cursor: pointer;
        font-size: 13px;
        flex-shrink: 0;
    }

    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 8px 12px;
    }

    .typing-indicator span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(128,128,128,.5);
        animation: typing 1.4s infinite;
    }

    .typing-indicator span:nth-child(2) { animation-delay: .2s; }
    .typing-indicator span:nth-child(3) { animation-delay: .4s; }

    @keyframes typing {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-6px); }
    }
</style>

<script>
    const ASSISTANT_TOKEN = '{{ csrf_token() }}';
    let assistantContext = null;
    let assistantOpened = false;
    let assistantRecognition = null;

    function assistantToggle() {
        const panel = document.getElementById('assistant-panel');
        panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';

        if (panel.style.display === 'flex' && !assistantOpened) {
            assistantOpened = true;
            assistantAdd('مرحباً! 👋 أنا مساعدك الذكي المحاسبي.\n\n📊 اسألني عن المبيعات والأرباح والأرصدة.\n📦 اطلب تفاصيل أي صنف.\n➕ أضف عملاء وموردين وأصناف.\n✏️ عدّل الأسعار والبيانات.\n🔗 اطلب فتح أي صفحة.\n🎙️ أو اضغط الميكروفون وتحدث!', 'bot');
            assistantLoadSuggestions();
            document.getElementById('assistant-question').focus();
        }
    }

    function assistantAdd(text, type) {
        const box = document.getElementById('assistant-messages');
        const div = document.createElement('div');
        div.className = 'assistant-msg ' + type;
        
        // معالجة الروابط والأزرار
        if (type === 'bot') {
            const processed = processLinks(text);
            div.innerHTML = processed.html;
            
            // إضافة مستمعي الأحداث للأزرار
            processed.buttons.forEach((btn, index) => {
                const buttonEl = div.querySelector(`[data-btn-index="${index}"]`);
                if (buttonEl) {
                    buttonEl.addEventListener('click', (e) => {
                        e.preventDefault();
                        openLink(btn.url, btn.type);
                    });
                }
            });
        } else {
            div.textContent = text;
        }
        
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
        return div;
    }

    function processLinks(text) {
        const urlPattern = /https?:\/\/[^\s]+/g;
        const buttons = [];
        let html = text;
        
        // استخراج الروابط
        const urls = text.match(urlPattern) || [];
        
        if (urls.length > 0) {
            // تحويل النص إلى HTML مع الحفاظ على الأسطر
            html = text.replace(/\n/g, '<br>');
            
            // استبدال كل رابط بزر
            urls.forEach((url, index) => {
                const buttonIndex = buttons.length;
                buttons.push({ url: url, type: 'button', index: buttonIndex });
                
                // استبدال الرابط بزر
                const escapedUrl = url.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const linkRegex = new RegExp(escapedUrl, 'g');
                html = html.replace(linkRegex, 
                    `<button class="assistant-link-btn" data-btn-index="${buttonIndex}">` +
                    `🔗 فتح الصفحة</button>`
                );
            });
            
            // إضافة نص الرابط كرابط قابل للنقر أسفل الزر
            if (urls.length === 1) {
                html += `<br><small style="opacity:0.7; word-break:break-all;">` +
                       `<a href="${urls[0]}" target="_blank" class="assistant-text-link">${urls[0]}</a></small>`;
            }
        } else {
            html = text.replace(/\n/g, '<br>');
        }
        
        return { html, buttons };
    }

    function openLink(url, type) {
        if (type === 'button') {
            // فتح في نافذة جديدة
            window.open(url, '_blank');
        } else {
            // فتح في نفس النافذة
            window.location.href = url;
        }
    }

    function assistantShowTyping() {
        const box = document.getElementById('assistant-messages');
        const div = document.createElement('div');
        div.className = 'assistant-msg bot';
        div.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
        return div;
    }

    function assistantRenderSuggestions(suggestions) {
        const box = document.getElementById('assistant-suggestions');
        box.innerHTML = '';

        if (!suggestions || !suggestions.length) return;

        suggestions.forEach(s => {
            const btn = document.createElement('button');
            btn.textContent = s.text;
            btn.onclick = () => {
                document.getElementById('assistant-question').value = s.action;
                assistantSend();
            };
            box.appendChild(btn);
        });
    }

    function assistantLoadSuggestions() {
        fetch('{{ route("assistant.ask") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': ASSISTANT_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ question: 'اقترح', context: null }),
        })
        .then(r => r.json())
        .then(data => assistantRenderSuggestions(data.suggestions || []))
        .catch(() => {});
    }

    async function assistantSend() {
        const input = document.getElementById('assistant-question');
        const question = input.value.trim();

        if (!question) return;

        assistantAdd(question, 'user');
        input.value = '';

        const typing = assistantShowTyping();

        try {
            const response = await fetch('{{ route("assistant.ask") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': ASSISTANT_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question: question, context: assistantContext }),
            });

            const data = await response.json();

            typing.remove();

            assistantContext = data.context || null;
            assistantAdd(data.answer || 'عذراً، حدث خطأ.', 'bot');
            assistantRenderSuggestions(data.suggestions || []);
        } catch (e) {
            typing.remove();
            assistantAdd('تعذر الاتصال بالمساعد. تحقق من الاتصال.', 'bot');
        }
    }

    /* ============================================================
       التعرف الصوتي
       ================================================================ */

    function startVoice() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            assistantAdd('⚠️ متصفحك لا يدعم التعرف الصوتي.\nجرّب Google Chrome أو Microsoft Edge.', 'bot');
            return;
        }

        if (assistantRecognition) {
            assistantRecognition.stop();
            assistantRecognition = null;
            document.querySelector('.voice-btn').classList.remove('listening');
            document.getElementById('assistant-listening').style.display = 'none';
            return;
        }

        assistantRecognition = new SpeechRecognition();
        assistantRecognition.lang = 'ar-EG';
        assistantRecognition.continuous = false;
        assistantRecognition.interimResults = false;

        const voiceBtn = document.querySelector('.voice-btn');
        const listeningEl = document.getElementById('assistant-listening');

        assistantRecognition.onstart = () => {
            voiceBtn.classList.add('listening');
            listeningEl.style.display = 'inline';
        };

        assistantRecognition.onresult = (event) => {
            const text = event.results[0][0].transcript;
            document.getElementById('assistant-question').value = text;
            assistantSend();
        };

        assistantRecognition.onerror = (event) => {
            voiceBtn.classList.remove('listening');
            listeningEl.style.display = 'none';
            assistantRecognition = null;

            if (event.error === 'no-speech') {
                assistantAdd('لم أسمع صوتاً. حاول مرة أخرى.', 'bot');
            } else if (event.error === 'not-allowed') {
                assistantAdd('⚠️ يجب السماح بالوصول للميكروفون من إعدادات المتصفح.', 'bot');
            }
        };

        assistantRecognition.onend = () => {
            voiceBtn.classList.remove('listening');
            listeningEl.style.display = 'none';
            assistantRecognition = null;
        };

        assistantRecognition.start();
    }
</script>
@endauth