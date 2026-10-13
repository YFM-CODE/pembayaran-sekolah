<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const form = useForm({
    name: user.value.name,
    email: user.value.email,
});

const resetForm = () => {
    form.name = user.value.name;
    form.email = user.value.email;
    form.clearErrors();
};

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="panel-section">
        <header class="section-head">
            <div class="head-badge-row">
                <span class="step-pill">Bagian 01</span>
                <span v-if="form.isDirty" class="dirty-pill">Perubahan belum disimpan</span>
            </div>
            <h2 class="section-title">Informasi Identitas &amp; Kontak</h2>
            <p class="section-desc">
                Perbarui nama tampilan dan alamat email resmi yang digunakan untuk masuk ke portal serta menerima pemberitahuan administrasi SPP.
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
                    <strong>Berhasil disimpan.</strong>
                    <span>Informasi profil dan alamat email Anda telah diperbarui di sistem.</span>
                </div>
            </div>
        </Transition>

        <form @submit.prevent="submit" class="form-body" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama Lengkap
                        <span class="req" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="form-input"
                        :class="{ 'is-invalid': form.errors.name }"
                        required
                        autocomplete="name"
                        placeholder="Masukkan nama lengkap Anda"
                    />
                    <span class="field-hint">
                        Nama ini akan ditampilkan pada bukti transaksi, laporan, dan bilah navigasi.
                    </span>
                    <p v-if="form.errors.name" class="field-error" role="alert">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        Alamat Email
                        <span class="req" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        inputmode="email"
                        class="form-input"
                        :class="{ 'is-invalid': form.errors.email }"
                        required
                        autocomplete="username"
                        placeholder="nama@sekolah.sch.id"
                    />
                    <span class="field-hint">
                        Gunakan email aktif sebagai kredensial utama saat masuk ke sistem.
                    </span>
                    <p v-if="form.errors.email" class="field-error" role="alert">
                        {{ form.errors.email }}
                    </p>
                </div>
            </div>

            <div class="readonly-meta">
                <div class="meta-item">
                    <span class="meta-label">Peran Sistem</span>
                    <span class="meta-val">{{ user.role_label || user.role }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Otorisasi Peran</span>
                    <span class="meta-note">Diatur oleh Administrator Sekolah</span>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="verify-box">
                <p class="verify-text">
                    Alamat email Anda belum terverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="verify-link"
                    >
                        Kirim ulang tautan verifikasi email
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="verify-sent">
                    Tautan verifikasi baru telah dikirimkan ke alamat email Anda.
                </div>
            </div>

            <div class="form-actions">
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
                </button>

                <button
                    v-if="form.isDirty"
                    type="button"
                    class="btn-secondary"
                    :disabled="form.processing"
                    @click="resetForm"
                >
                    Batalkan Perubahan
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

.dirty-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px var(--space-2);
    border-radius: var(--radius-sm);
    background: #fef3c7;
    color: #92400e;
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
    gap: var(--space-5);
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

.form-input {
    width: 100%;
    height: var(--control-height);
    padding: 0 var(--space-3);
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

.field-hint {
    margin-top: var(--space-1);
    font-size: var(--text-xs);
    color: var(--color-muted);
    line-height: 1.45;
}

.field-error {
    margin: var(--space-2) 0 0;
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    color: var(--color-danger);
}

.readonly-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius);
    background: var(--color-bg);
    border: var(--border);
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.meta-label {
    font-size: 0.75rem;
    font-weight: var(--weight-medium);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--color-muted);
}

.meta-val {
    font-size: var(--text-sm);
    font-weight: var(--weight-bold);
    color: var(--color-brand);
}

.meta-note {
    font-size: var(--text-xs);
    color: var(--color-muted);
}

.verify-box {
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius);
    background: #fffbeb;
    border: 1px solid #fde68a;
}

.verify-text {
    margin: 0;
    font-size: var(--text-sm);
    color: #92400e;
}

.verify-link {
    background: none;
    border: none;
    padding: 0;
    margin-left: var(--space-1);
    color: var(--color-brand);
    font-weight: var(--weight-bold);
    font-size: var(--text-sm);
    text-decoration: underline;
    cursor: pointer;
}

.verify-sent {
    margin-top: var(--space-2);
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    color: var(--color-brand-dark);
}

.form-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--space-3);
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

.btn-primary:focus-visible,
.btn-secondary:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 2px;
}

.btn-primary:disabled,
.btn-secondary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: var(--control-height);
    padding: 0 var(--space-4);
    background: var(--color-surface);
    color: var(--color-ink);
    border: var(--border-strong);
    border-radius: var(--radius);
    font-family: inherit;
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.btn-secondary:hover:not(:disabled) {
    background: var(--color-bg);
}

@media (max-width: 720px) {
    .form-grid,
    .readonly-meta {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 540px) {
    .form-input {
        font-size: var(--text-base);
    }

    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-primary,
    .btn-secondary {
        width: 100%;
    }
}
</style>
