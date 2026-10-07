@extends('layouts.master')
@section('title', 'مركز الأوامر الشامل')
@section('content')
<style>
    .cc-container { max-width: 900px; margin: 0 auto; padding: 20px; }
    .cc-search-box { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); padding: 30px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); margin-bottom: 20px; }
    .cc-search-input { width: 100%; padding: 20px 25px; font-size: 20px; border: none; border-radius: 12px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); direction: rtl; }
    .cc-search-input:focus { outline: none; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .cc-title { color: white; font-size: 28px; font-weight: bold; text-align: center; margin-bottom: 20px; }
    .cc-subtitle { color: rgba(255,255,255,0.9); text-align: center; margin-bottom: 20px; }
    .cc-shortcuts { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 15px; }
    .cc-shortcut { background: rgba(255,255,255,0.2); color: white; padding: 6px 12px; border-radius: 20px; font-size: 13px; cursor: pointer; border: 1px solid rgba(255,255,255,0.3); transition: all 0.2s; }
    .cc-shortcut:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); }
    .cc-result-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .cc-entity-item { display: flex; align-items: center; padding: 12px; border-radius: 8px; cursor: pointer; transition: background 0.2s; border: 1px solid #e9ecef; margin-bottom: 8px; }
    .cc-entity-item:hover { background: #f8f9fa; border-color: #198754; }
    .cc-entity-icon { font-size: 32px; margin-left: 15px; }
    .cc-entity-info { flex: 1; }
    .cc-entity-title { font-weight: bold; font-size: 16px; margin-bottom: 4px; }
    .cc-entity-subtitle { color: #6c757d; font-size: 13px; }
    .cc-entity-actions { display: flex; gap: 5px; }
    .cc-entity-actions a { padding: 4px 10px; font-size: 12px; border-radius: 4px; text-decoration: none; background: #e9ecef; color: #495057; }
    .cc-entity-actions a:hover { background: #198754; color: white; }
    .cc-command-item { display: flex; justify-content: space-between; padding: 10px 12px; border-radius: 6px; cursor: pointer; transition: background 0.2s; }
    .cc-command-item:hover { background: #f8f9fa; }
    .cc-command-code { font-family: 'Courier New', monospace; background: #f8f9fa; padding: 3px 8px; border-radius: 4px; font-size: 13px; color: #198754; direction: ltr; }
    .cc-answer-box { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-right: 4px solid #198754; padding: 20px; border-radius: 8px; white-space: pre-wrap; font-size: 15px; line-height: 1.8; }
    .cc-report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
    .cc-report-stat { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; }
    .cc-report-stat-label { font-size: 13px; opacity: 0.9; margin-bottom: 8px; }
    .cc-report-stat-value { font-size: 22px; font-weight: bold; }
    .cc-loading { text-align: center; padding: 40px; color: #6c757d; }
    .cc-error-box { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border-right: 4px solid #dc3545; }
    .cc-success-box { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border-right: 4px solid #28a745; }
    .cc-help-item { padding: 8px 0; border-bottom: 1px solid #e9ecef; }
    .cc-help-item:last-child { border-bottom: none; }
    .cc-suggestions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px; }
    .cc-suggestion { background: #e7f3ff; color: #0d6efd; padding: 6px 12px; border-radius: 20px; font-size: 13px; cursor: pointer; border: 1px solid #b8daff; }
    .cc-suggestion:hover { background: #0d6efd; color: white; }
    .cc-debug { background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; margin-top: 10px; font-size: 12px; }
    .cc-status-bar { text-align: center; padding: 10px; color: #6c757d; font-size: 12px; }
</style>

<div class="cc-container" x-data="commandCenter()" x-init="init()">
    {{-- Search Box --}}
    <div class="cc-search-box">
        <div class="cc-title">🎯 مركز الأوامر الشامل</div>
        <div class="cc-subtitle">اكتب سؤالاً، أمراً، أو ابحث عن أي شيء...</div>
        <input type="text" 
               class="cc-search-input" 
               x-model="query"
               @input.debounce.300ms="onSearch()"
               @keydown.enter="execute()"
               @keydown.escape="clearQuery()"
               placeholder="مثال: ما مبيعات اليوم؟، /new-customer، ابحث عن منتج..."
               autofocus>
        <div class="cc-shortcuts">
            <span class="cc-shortcut" @click="setQuery('/products ')">/products</span>
            <span class="cc-shortcut" @click="setQuery('/customers ')">/customers</span>
            <span class="cc-shortcut" @click="setQuery('/new-customer')">/new-customer</span>
            <span class="cc-shortcut" @click="setQuery('/sales today')">/sales</span>
            <span class="cc-shortcut" @click="setQuery('/transfer')">/transfer</span>
            <span class="cc-shortcut" @click="setQuery('/profit')">/profit</span>
            <span class="cc-shortcut" @click="setQuery('ما مبيعات اليوم؟')">مبيعات اليوم</span>
        </div>
    </div>

    {{-- Status Bar --}}
    <div class="cc-status-bar" x-show="status">
        <span x-text="status"></span>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="cc-loading">
        <div style="font-size: 32px;">⏳</div>
        <div>جاري البحث...</div>
    </div>

    {{-- Results --}}
    <div x-show="!loading && result" class="cc-results">
        
        {{-- Answer Type --}}
        <template x-if="result.type === 'answer'">
            <div class="cc-result-card">
                <h5 class="mb-3">💬 الإجابة</h5>
                <div class="cc-answer-box" x-text="result.answer"></div>
                <div class="cc-suggestions" x-show="result.suggestions && result.suggestions.length">
                    <template x-for="(s, i) in result.suggestions" :key="i">
                        <span class="cc-suggestion" @click="setQuery(s.action)" x-text="s.text"></span>
                    </template>
                </div>
            </div>
        </template>

        {{-- Entities Type --}}
        <template x-if="result.type === 'entities' || (result.entities && result.entities.length)">
            <div class="cc-result-card">
                <h5 class="mb-3">🔍 نتائج البحث (<span x-text="result.entities.length"></span>)</h5>
                <template x-for="(entity, i) in result.entities" :key="i">
                    <div class="cc-entity-item" @click="entity.url && (window.location.href = entity.url)">
                        <div class="cc-entity-icon" x-text="entity.icon"></div>
                        <div class="cc-entity-info">
                            <div class="cc-entity-title" x-text="entity.title"></div>
                            <div class="cc-entity-subtitle" x-text="entity.subtitle"></div>
                        </div>
                        <div class="cc-entity-actions" @click.stop>
                            <template x-for="(action, j) in entity.actions" :key="j">
                                <a :href="action.url" x-text="action.label" @click.stop></a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Form Type --}}
        <template x-if="result.type === 'form'">
            <div class="cc-result-card">
                <h5 class="mb-3" x-text="result.title"></h5>
                <form @submit.prevent="submitForm()">
                    <template x-for="(field, i) in result.fields" :key="i">
                        <div class="mb-3">
                            <label class="form-label fw-bold" x-text="field.label"></label>
                            <template x-if="field.type === 'text' || field.type === 'number'">
                                <input :type="field.type" 
                                       class="form-control form-control-lg" 
                                       :required="field.required"
                                       x-model="formData[field.name]">
                            </template>
                            <template x-if="field.type === 'select'">
                                <select class="form-select form-select-lg" 
                                        :required="field.required"
                                        x-model="formData[field.name]">
                                    <option value="">-- اختر --</option>
                                    <template x-for="(label, value) in field.options" :key="value">
                                        <option :value="value" x-text="label"></option>
                                    </template>
                                </select>
                            </template>
                        </div>
                    </template>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">✅ تنفيذ</button>
                        <button type="button" class="btn btn-outline-secondary" @click="clearResult()">إلغاء</button>
                    </div>
                </form>
            </div>
        </template>

        {{-- Report Type --}}
        <template x-if="result.type === 'report'">
            <div class="cc-result-card">
                <h5 class="mb-3" x-text="result.title"></h5>
                <div class="cc-report-grid">
                    <template x-if="result.data.count !== undefined">
                        <div class="cc-report-stat">
                            <div class="cc-report-stat-label">العدد</div>
                            <div class="cc-report-stat-value" x-text="result.data.count"></div>
                        </div>
                    </template>
                    <template x-if="result.data.formatted_total">
                        <div class="cc-report-stat">
                            <div class="cc-report-stat-label">الإجمالي</div>
                            <div class="cc-report-stat-value" x-text="result.data.formatted_total"></div>
                        </div>
                    </template>
                    <template x-if="result.data.formatted_revenue">
                        <div class="cc-report-stat">
                            <div class="cc-report-stat-label">الإيرادات</div>
                            <div class="cc-report-stat-value" x-text="result.data.formatted_revenue"></div>
                        </div>
                    </template>
                    <template x-if="result.data.formatted_expense">
                        <div class="cc-report-stat" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                            <div class="cc-report-stat-label">المصروفات</div>
                            <div class="cc-report-stat-value" x-text="result.data.formatted_expense"></div>
                        </div>
                    </template>
                    <template x-if="result.data.formatted_net">
                        <div class="cc-report-stat" :style="result.data.net >= 0 ? '' : 'background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);'">
                            <div class="cc-report-stat-label">صافي الربح</div>
                            <div class="cc-report-stat-value" x-text="result.data.formatted_net"></div>
                        </div>
                    </template>
                </div>
                <template x-if="result.data.items && result.data.items.length">
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>الصنف</th><th>المتوفر</th><th>الحد الأدنى</th></tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, i) in result.data.items" :key="i">
                                    <tr>
                                        <td x-text="item.name"></td>
                                        <td class="text-danger fw-bold" x-text="item.current"></td>
                                        <td x-text="item.min"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </template>

        {{-- Success Type --}}
        <template x-if="result.type === 'success'">
            <div class="cc-result-card">
                <div class="cc-success-box" x-text="result.message"></div>
                <div x-show="result.data && result.data.url" class="mt-3 d-grid">
                    <a :href="result.data.url" class="btn btn-primary btn-lg">👁️ عرض التفاصيل</a>
                </div>
            </div>
        </template>

        {{-- Error Type --}}
        <template x-if="result.type === 'error'">
            <div class="cc-result-card">
                <div class="cc-error-box" x-text="result.message"></div>
                <template x-if="result.suggestions">
                    <div class="mt-3">
                        <h6>الأوامر المتاحة:</h6>
                        <template x-for="(cmd, i) in result.suggestions" :key="i">
                            <div class="cc-help-item d-flex justify-content-between">
                                <span class="cc-command-code" x-text="cmd.command"></span>
                                <span class="text-muted" x-text="cmd.description"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        {{-- Help Type --}}
        <template x-if="result.type === 'help'">
            <div class="cc-result-card">
                <h5 class="mb-3" x-text="result.title"></h5>
                <template x-for="(cmd, i) in result.commands" :key="i">
                    <div class="cc-help-item d-flex justify-content-between align-items-center">
                        <span class="cc-command-code" x-text="cmd.command" @click="setQuery(cmd.command.split(' ')[0])" style="cursor:pointer"></span>
                        <span class="text-muted" x-text="cmd.description"></span>
                    </div>
                </template>
            </div>
        </template>

        {{-- Redirect Type --}}
        <template x-if="result.type === 'redirect'">
            <div class="cc-result-card">
                <div class="cc-success-box" x-text="result.message"></div>
                <div class="mt-3 d-grid">
                    <a :href="result.url" class="btn btn-primary btn-lg">🚀 فتح الصفحة الآن</a>
                </div>
            </div>
        </template>
    </div>

    {{-- Initial State (no query yet) --}}
    <div x-show="!loading && !result" class="cc-result-card">
        <h5 class="mb-3">💡 جرب هذه الأوامر</h5>
        <div class="cc-suggestions">
            <span class="cc-suggestion" @click="setQuery('ما مبيعات اليوم؟')">📊 ما مبيعات اليوم؟</span>
            <span class="cc-suggestion" @click="setQuery('ما إجمالي النقدية؟')">💰 ما إجمالي النقدية؟</span>
            <span class="cc-suggestion" @click="setQuery('ما الأصناف المنخفضة؟')">⚠️ الأصناف المنخفضة</span>
            <span class="cc-suggestion" @click="setQuery('/new-customer')">➕ عميل جديد</span>
            <span class="cc-suggestion" @click="setQuery('/new-product')">📦 منتج جديد</span>
            <span class="cc-suggestion" @click="setQuery('/transfer')">💱 تحويل نقدي</span>
            <span class="cc-suggestion" @click="setQuery('/profit')">💹 تقرير الأرباح</span>
            <span class="cc-suggestion" @click="setQuery('حول 5000 من الصندوق للبنك')">🔄 تحويل 5000</span>
        </div>
        
        <div class="mt-4">
            <h6>📖 الأوامر المتاحة:</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="cc-help-item"><span class="cc-command-code">/products</span> <span class="text-muted">البحث عن منتجات</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/customers</span> <span class="text-muted">البحث عن عملاء</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/suppliers</span> <span class="text-muted">البحث عن موردين</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/new-customer</span> <span class="text-muted">إضافة عميل</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/new-product</span> <span class="text-muted">إضافة منتج</span></div>
                </div>
                <div class="col-md-6">
                    <div class="cc-help-item"><span class="cc-command-code">/sales</span> <span class="text-muted">تقرير المبيعات</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/profit</span> <span class="text-muted">تقرير الأرباح</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/stock</span> <span class="text-muted">المخزون المنخفض</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/transfer</span> <span class="text-muted">تحويل نقدي</span></div>
                    <div class="cc-help-item"><span class="cc-command-code">/go صفحة</span> <span class="text-muted">فتح صفحة</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function commandCenter() {
    return {
        query: '',
        loading: false,
        result: null,
        formData: {},
        formAction: '',
        status: '',
        baseUrl: '{{ url("/") }}',
        csrfToken: '{{ csrf_token() }}',
        debugMode: false,

        init() {
            // محاولة الحصول على CSRF token من عدة مصادر
            if (!this.csrfToken) {
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    this.csrfToken = metaTag.content;
                }
            }
            
            console.log('🎯 Command Center initialized');
            console.log('   Base URL:', this.baseUrl);
            console.log('   CSRF Token:', this.csrfToken ? '✅ موجود' : '❌ غير موجود');
            
            // اختصار Ctrl+K للتركيز على البحث
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    document.querySelector('.cc-search-input').focus();
                }
            });
        },

        setQuery(q) {
            this.query = q;
            this.onSearch();
            document.querySelector('.cc-search-input').focus();
        },

        clearQuery() {
            this.query = '';
            this.result = null;
            this.status = '';
        },

        clearResult() {
            this.result = null;
            this.formData = {};
        },

        async makeRequest(url, data) {
            this.status = `📡 جاري الاتصال بـ: ${url}`;
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });

                this.status = `📨 الرد: ${response.status} ${response.statusText}`;

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server Error:', response.status, errorText);
                    
                    if (response.status === 419) {
                        throw new Error('انتهت صلاحية الجلسة. يرجى تحديث الصفحة.');
                    }
                    if (response.status === 404) {
                        throw new Error(`المسار غير موجود: ${url}`);
                    }
                    if (response.status === 500) {
                        throw new Error('خطأ في الخادم. تحقق من console للأخطاء.');
                    }
                    
                    throw new Error(`خطأ ${response.status}: ${errorText.substring(0, 200)}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text.substring(0, 500));
                    throw new Error('الاستجابة ليست JSON - قد تكون صفحة خطأ');
                }

                return await response.json();
            } catch (error) {
                this.status = `❌ خطأ: ${error.message}`;
                throw error;
            }
        },

        async onSearch() {
            if (this.query.length < 2) {
                this.result = null;
                this.status = '';
                return;
            }
            
            this.loading = true;
            this.status = '⏳ جاري البحث...';
            
            try {
                this.result = await this.makeRequest(
                    `${this.baseUrl}/command-center/process`,
                    { query: this.query }
                );
                
                if (this.result.type === 'form') {
                    this.formAction = this.result.action;
                    this.formData = {};
                }
                
                this.status = `✅ تم: ${this.result.type}`;
            } catch (error) {
                console.error('Search Error:', error);
                this.result = { 
                    type: 'error', 
                    message: `خطأ: ${error.message}\n\n💡 تأكد من:\n• تحديث الصفحة (F5)\n• تسجيل الدخول\n• وجود اتصال بالإنترنت`
                };
                this.status = '❌ فشل البحث';
            } finally {
                this.loading = false;
            }
        },

        async execute() {
            if (!this.query.trim()) return;
            await this.onSearch();
        },

        async submitForm() {
            this.loading = true;
            this.status = '⏳ جاري تنفيذ الإجراء...';
            
            try {
                this.result = await this.makeRequest(
                    `${this.baseUrl}/command-center/execute`,
                    { action: this.formAction, data: this.formData }
                );
                
                if (this.result.type === 'success') {
                    this.formData = {};
                    this.status = '✅ تم التنفيذ بنجاح';
                }
            } catch (error) {
                console.error('Form Error:', error);
                this.result = { type: 'error', message: 'خطأ في التنفيذ: ' + error.message };
                this.status = '❌ فشل التنفيذ';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection