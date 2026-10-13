<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const showPassword = ref(false);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    showPassword.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="danger-section">
        <div class="danger-layout">
            <div class="danger-info">
                <div class="head-badge-row">
                    <span class="danger-pill">Bagian 03 &bull; Zona Berbahaya</span>
                </div>
                <h2 class="section-title">Penghapusan Akun Permanen</h2>
                <p class="section-desc">
                    Setelah akun Anda dihapus, seluruh akses masuk dan sesi yang terhubung dengan akun ini akan dihentikan secara permanen. Pastikan Anda telah mengunduh bukti pembayaran atau dokumen penting sebelum melanjutkan.
                </p>
            </div>

            <div class="danger-cta">
                <button
                    type="button"
                    class="btn-danger"
                    @click="confirmUserDeletion"
                >
                    Hapus Akun Saya
                </button>
            </div>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="modal-card">
                <div class="modal-head">
                    <span class="danger-pill">Konfirmasi Penghapusan</span>
                    <h2 class="modal-title">
                        Apakah Anda yakin ingin menghapus akun ini?
                    </h2>
                    <p class="modal-desc">
                        Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi akun Anda saat ini untuk mengonfirmasi penghapusan akun secara permanen dari sistem.
                    </p>
                </div>

                <div class="modal-field">
                    <label for="delete_password" class="form-label">
                        Kata Sandi Konfirmasi
                    </label>

                    <div class="pw-wrap">
                        <input
                            id="delete_password"
                            ref="passwordInput"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="form-input"
                            :class="{ 'is-invalid': form.errors.password }"
                            placeholder="Masukkan kata sandi Anda"
                            @keyup.enter="deleteUser"
                        />
                        <button
                            type="button"
                            class="btn-toggle-pw"
                            :aria-pressed="showPassword"
                            @click="showPassword = !showPassword"
                        >
                            {{ showPassword ? 'Tutup' : 'Lihat' }}
                        </button>
                    </div>

                    <p v-if="form.errors.password" class="field-error" role="alert">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn-secondary"
                        :disabled="form.processing"
                        @click="closeModal"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        class="btn-danger"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ form.processing ? 'Menghapus...' : 'Ya, Hapus Akun Permanen' }}
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>

<style scoped>
.danger-section {
    font-family: var(--font-sans);
    color: var(--color-ink);
}

.danger-layout {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-5);
}

.danger-info {
    max-width: 60ch;
}

.head-badge-row {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-2);
}

.danger-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px var(--space-2);
    border-radius: var(--radius-sm);
    background: #fdf2f2;
    border: 1px solid #f9d5d5;
    color: var(--color-danger);
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    letter-spacing: 0.05em;
    text-transform: uppercase;
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

.danger-cta {
    flex-shrink: 0;
}

.btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: var(--control-height);
    padding: 0 var(--space-5);
    background: var(--color-danger);
    color: #ffffff;
    border: 1px solid var(--color-danger);
    border-radius: var(--radius);
    font-family: inherit;
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    cursor: pointer;
    transition: filter 0.15s ease, opacity 0.15s ease;
}

.btn-danger:hover:not(:disabled) {
    filter: brightness(0.92);
}

.btn-danger:focus-visible,
.btn-secondary:focus-visible {
    outline: 2px solid var(--color-danger);
    outline-offset: 2px;
}

.btn-danger:disabled,
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
}

.btn-secondary:hover:not(:disabled) {
    background: var(--color-bg);
}

/* Modal */
.modal-card {
    padding: var(--space-6);
    font-family: var(--font-sans);
    color: var(--color-ink);
    background: var(--color-surface);
}

.modal-head {
    margin-bottom: var(--space-5);
}

.modal-title {
    font-size: var(--text-xl);
    font-weight: var(--weight-bold);
    letter-spacing: -0.015em;
    margin: var(--space-2) 0 var(--space-2);
    color: var(--color-ink);
}

.modal-desc {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--color-muted);
    line-height: var(--leading-body);
}

.modal-field {
    margin-bottom: var(--space-5);
}

.form-label {
    display: block;
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    color: var(--color-ink);
    margin-bottom: var(--space-2);
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
}

.form-input:focus {
    outline: none;
    border-color: var(--color-danger);
    box-shadow: 0 0 0 3px #fdf2f2;
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
}

.field-error {
    margin: var(--space-2) 0 0;
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    color: var(--color-danger);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
}

@media (max-width: 720px) {
    .danger-layout {
        flex-direction: column;
        align-items: flex-start;
    }

    .danger-cta,
    .btn-danger {
        width: 100%;
    }
}

@media (max-width: 540px) {
    .modal-card {
        padding: var(--space-5) var(--space-4);
    }

    .form-input {
        font-size: var(--text-base);
    }

    .modal-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .modal-actions .btn-secondary,
    .modal-actions .btn-danger {
        width: 100%;
    }
}
</style>
