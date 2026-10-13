<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const strengthRules = computed(() => {
    const pw = form.password || '';
    return [
        { label: 'Minimal 8 karakter', valid: pw.length >= 8 },
        { label: 'Kombinasi huruf & angka', valid: /[A-Za-z]/.test(pw) && /\d/.test(pw) },
        {
            label: 'Konfirmasi sandi cocok',
            valid: pw.length > 0 && pw === form.password_confirmation,
        },
    ];
});

const strengthScore = computed(() => strengthRules.value.filter((r) => r.valid).length);

const strengthLabel = computed(() => {
    if (!form.password) return 'Belum diisi';
    if (strengthScore.value === 1) return 'Perlu diperkuat';
    if (strengthScore.value === 2) return 'Cukup baik';
    if (strengthScore.value === 3) return 'Kuat & siap disimpan';
    return 'Terlalu pendek';
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showCurrent.value = false;
            showNew.value = false;
            showConfirm.value = false;
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section class="panel-section">
        <header class="section-head">
            <div class="head-badge-row">
                <span class="step-pill">Bagian 02</span>
                <span class="security-pill">Enkripsi Aktif</span>
            </div>
            <h2 class="section-title">Keamanan &amp; Kata Sandi</h2>
            <p class="section-desc">
                Gunakan kata sandi yang kuat dan unik untuk menjaga keamanan akses data keuangan serta tagihan SPP Anda.
            </p>
        </header>

        <Transition
            enter-active-class="alert-enter-active"
            enter-from-class="alert-enter-from"
            leave-active-class="alert-leave-active"
            leave-to-class="alert-leave-to"
        >
            <div v-if="form.recentlySuccessful" class="alert alert-success" role="status">
                <span class="alert-dot" aria-hidden="true"></span>
                <div>
                    <strong>Kata sandi diperbarui.</strong>
                    <span>Kredensial keamanan akun Anda telah berhasil diganti.</span>
                </div>
            </div>
        </Transition>

        <form @submit.prevent="updatePassword" class="form-body" novalidate>
            <div class="form-group">
                <label for="current_password" class="form-label">
                    Kata Sandi Saat Ini
                    <span class="req" aria-hidden="true">*</span>
                </label>
                <div class="pw-wrap">
                    <input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'"
                        class="form-input"
                        :class="{ 'is-invalid': form.errors.current_password }"
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi lama Anda"
                    />
                    <button
                        type="button"
                        class="btn-toggle-pw"
                        :aria-pressed="showCurrent"
                        @click="showCurrent = !showCurrent"
                    >
                        {{ showCurrent ? 'Tutup' : 'Lihat' }}
                    </button>
                </div>
                <p v-if="form.errors.current_password" class="field-error" role="alert">
                    {{ form.errors.current_password }}
                </p>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="password" class="form-label">
                        Kata Sandi Baru
                        <span class="req" aria-hidden="true">*</span>
                    </label>
                    <div class="pw-wrap">
                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            :type="showNew ? 'text' : 'password'"
                            class="form-input"
                            :class="{ 'is-invalid': form.errors.password }"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                        />
                        <button
                            type="button"
                            class="btn-toggle-pw"
                            :aria-pressed="showNew"
                            @click="showNew = !showNew"
                        >
                            {{ showNew ? 'Tutup' : 'Lihat' }}
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="field-error" role="alert">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        Konfirmasi Kata Sandi Baru
                        <span class="req" aria-hidden="true">*</span>
                    </label>
                    <div class="pw-wrap">
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            class="form-input"
                            :class="{ 'is-invalid': form.errors.password_confirmation }"
                            autocomplete="new-password"
                            placeholder="Ketik ulang kata sandi baru"
                        />
                        <button
                            type="button"
                            class="btn-toggle-pw"
                            :aria-pressed="showConfirm"
                            @click="showConfirm = !showConfirm"
                        >
                            {{ showConfirm ? 'Tutup' : 'Lihat' }}
                        </button>
                    </div>
                    <p v-if="form.errors.password_confirmation" class="field-error" role="alert">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>
            </div>

            <!-- Indikator Kekuatan Kata Sandi -->
            <div class="pw-meter-card">
                <div class="pw-meter-head">
                    <span class="pw-meter-title">Indikator Kesiapan Sandi</span>
                    <span
                        class="pw-meter-status"
                        :class="{
                            'status-strong': strengthScore === 3,
                            'status-medium': strengthScore === 2,
                            'status-weak': strengthScore === 1,
                        }"
                    >
                        {{ strengthLabel }}
                    </span>
                </div>

                <div class="pw-bars" aria-hidden="true">
                    <span
                        v-for="idx in 3"
                        :key="idx"
                        class="pw-bar"
                        :class="{
                            'bar-active-weak': strengthScore === 1 && idx <= 1,
                            'bar-active-medium': strengthScore === 2 && idx <= 2,
                            'bar-active-strong': strengthScore === 3 && idx <= 3,
                        }"
                    ></span>
                </div>

                <ul class="pw-checklist">
                    <li
                        v-for="rule in strengthRules"
                        :key="rule.label"
                        class="pw-check-item"
                        :class="{ 'is-valid': rule.valid }"
                    >
                        <span class="check-indicator" aria-hidden="true"></span>
                        <span>{{ rule.label }}</span>
                    </li>
                </ul>
            </div>

            <div class="form-actions">
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Memperbarui...' : 'Perbarui Kata Sandi' }}
                </button>
            </div>
        </form>
    </section>
</template>

<style scoped>
.panel-section {
    font-family: var(--font-sans);
    color: var(--color-ink);
}

.section-head {
    padding-bottom: var(--space-4);
    border-bottom: var(--border);
    margin-bottom: var(--space-5);
}

.head-badge-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-2);
}

.step-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px var(--space-2);
    border-radius: var(--radius-sm);
    background: var(--color-brand-tint);
    color: var(--color-brand);
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.security-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px var(--space-2);
    border-radius: var(--radius-sm);
    background: var(--color-bg);
    border: var(--border);
    color: var(--color-muted);
    font-size: 0.75rem;
    font-weight: var(--weight-medium);
}

.section-title {
    font-size: var(--text-lg);
    font-weight: var(--weight-bold);
    letter-spacing: -0.015em;
    color: var(--color-ink);
    margin: 0 0 var(--space-1);
}

.section-desc {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--color-muted);
    line-height: var(--leading-body);
}

.alert {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius);
    font-size: var(--text-sm);
    margin-bottom: var(--space-5);
}

.alert-success {
    background: var(--color-brand-tint);
    border: 1px solid #b8d8d4;
    color: var(--color-brand-dark);
}

.alert-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: var(--color-brand);
    margin-top: 7px;
    flex-shrink: 0;
}

.alert strong {
    display: block;
    font-weight: var(--weight-bold);
}

.alert-enter-active,
.alert-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.alert-enter-from,
.alert-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

.form-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.form-label {
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    color: var(--color-ink);
    margin-bottom: var(--space-2);
}

.req {
    color: var(--color-danger);
    margin-left: 2px;
}

.pw-wrap {
    position: relative;
}

.form-input {
    width: 100%;
    height: var(--control-height);
    padding: 0 74px 0 var(--space-3);
    border: var(--border);
    border-radius: var(--radius);
    background: var(--color-surface);
    color: var(--color-ink);
    font-family: inherit;
    font-size: var(--text-sm);
    box-sizing: border-box;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-input:hover {
    border-color: var(--color-line-strong);
}

.form-input:focus {
    outline: none;
    border-color: var(--color-brand);
    box-shadow: 0 0 0 3px var(--color-brand-tint);
}

.form-input.is-invalid {
    border-color: var(--color-danger);
}

.btn-toggle-pw {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    min-width: 60px;
    height: 34px;
    background: var(--color-bg);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-sm);
    color: var(--color-muted);
    font-family: inherit;
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    cursor: pointer;
    padding: 0 var(--space-2);
    transition: color 0.15s ease, border-color 0.15s ease, background-color 0.15s ease;
}

.btn-toggle-pw:hover {
    color: var(--color-ink);
    border-color: var(--color-line-strong);
    background: var(--color-brand-tint);
}

.btn-toggle-pw:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 2px;
}

.field-error {
    margin: var(--space-2) 0 0;
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    color: var(--color-danger);
}

.pw-meter-card {
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius);
    background: var(--color-bg);
    border: var(--border);
}

.pw-meter-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-2);
    margin-bottom: var(--space-2);
}

.pw-meter-title {
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--color-muted);
}

.pw-meter-status {
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    color: var(--color-muted);
}

.pw-meter-status.status-weak {
    color: var(--color-danger);
}

.pw-meter-status.status-medium {
    color: #b45309;
}

.pw-meter-status.status-strong {
    color: var(--color-brand);
}

.pw-bars {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-2);
    margin-bottom: var(--space-3);
}

.pw-bar {
    height: 6px;
    border-radius: 999px;
    background: var(--color-line);
    transition: background-color 0.2s ease;
}

.pw-bar.bar-active-weak {
    background: var(--color-danger);
}

.pw-bar.bar-active-medium {
    background: #d97706;
}

.pw-bar.bar-active-strong {
    background: var(--color-brand);
}

.pw-checklist {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-2);
}

.pw-check-item {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-xs);
    color: var(--color-muted);
}

.check-indicator {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: var(--color-line-strong);
    flex-shrink: 0;
    transition: background-color 0.15s ease, transform 0.15s ease;
}

.pw-check-item.is-valid {
    color: var(--color-brand-dark);
    font-weight: var(--weight-medium);
}

.pw-check-item.is-valid .check-indicator {
    background: var(--color-brand);
    transform: scale(1.15);
}

.form-actions {
    display: flex;
    align-items: center;
    padding-top: var(--space-2);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: var(--control-height);
    padding: 0 var(--space-5);
    background: var(--color-brand);
    color: #ffffff;
    border: 1px solid var(--color-brand);
    border-radius: var(--radius);
    font-family: inherit;
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.btn-primary:hover:not(:disabled) {
    background: var(--color-brand-dark);
    border-color: var(--color-brand-dark);
}

.btn-primary:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 2px;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 720px) {
    .form-grid,
    .pw-checklist {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 540px) {
    .form-input {
        font-size: var(--text-base);
    }

    .btn-primary {
        width: 100%;
    }
}
</style>
