<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: { type: Boolean, default: false },
    status: { type: String, default: "" },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Masuk - SPP Sekolah" />

    <div class="login-wrapper">
        <aside class="auth-banner">
            <div class="banner-inner">
                <Link href="/" class="brand-link">
                    <span class="brand-mark" aria-hidden="true"></span>
                    SPP Sekolah
                </Link>

                <div class="banner-content">
                    <h2>
                        Pembayaran SPP yang dapat ditelusuri dari tarif hingga kas.
                    </h2>
                    <p>
                        Masuk sesuai peran: wali murid, staf TU, bendahara, kepala
                        sekolah, komite, atau admin.
                    </p>
                </div>

                <div class="banner-footer">
                    <span>&copy; {{ new Date().getFullYear() }} Sistem Informasi SPP Sekolah. Seluruh hak cipta dilindungi.</span>
                </div>
            </div>
        </aside>

        <main class="auth-content">
            <div class="auth-box">
                <div class="auth-header">
                    <h1>Masuk</h1>
                    <p>Gunakan akun yang terdaftar di sekolah.</p>
                </div>

                <div v-if="status" class="alert alert-info">
                    {{ status }}
                </div>

                <div
                    v-if="form.errors.email || form.errors.password"
                    class="alert alert-danger"
                    role="alert"
                >
                    {{ form.errors.email || form.errors.password }}
                </div>

                <form novalidate @submit.prevent="submit">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            inputmode="email"
                            autocomplete="username"
                            placeholder="nama@sekolah.sch.id"
                            required
                            autofocus
                            :class="{ 'is-invalid': form.errors.email }"
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">Kata Sandi</label>
                        <div class="password-wrapper">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                :class="{ 'is-invalid': form.errors.password }"
                            />
                            <button
                                type="button"
                                class="btn-toggle-pw"
                                :aria-pressed="showPassword"
                                @click="showPassword = !showPassword"
                            >
                                {{ showPassword ? "Tutup" : "Lihat" }}
                            </button>
                        </div>
                    </div>

                    <div class="remember-row">
                        <label class="checkbox-label">
                            <input v-model="form.remember" type="checkbox" />
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="btn-submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Memproses..." : "Masuk ke Sistem" }}
                    </button>
                </form>

                <p class="auth-footnote">
                    Lupa kata sandi? Hubungi staf TU atau administrator sekolah.
                </p>

                <div class="mobile-footer">
                    &copy; {{ new Date().getFullYear() }} Sistem Informasi SPP Sekolah. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.login-wrapper {
    display: grid;
    grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
    min-height: 100vh;
    min-height: 100dvh;
    background: var(--color-bg);
    color: var(--color-ink);
    font-family: var(--font-sans);
    overflow-x: clip;
}

/* Banner Kiri */
.auth-banner {
    background: var(--color-brand);
    color: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: stretch;
    box-sizing: border-box;
    min-width: 0;
}

/* Wadah inner untuk menyelaraskan margin dengan container Welcome */
.banner-inner {
    width: 100%;
    max-width: 500px;
    margin-left: auto;
    padding: var(--space-4) var(--space-7) var(--space-6) var(--space-5);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    box-sizing: border-box;
}

.brand-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-3);
    color: #ffffff;
    font-weight: var(--weight-bold);
    font-size: var(--text-lg);
    letter-spacing: -0.01em;
    text-decoration: none;
    height: var(--control-height);
}

.brand-mark {
    width: 22px;
    height: 22px;
    border-radius: 5px;
    background: #ffffff;
    box-shadow: inset 0 -6px 0 rgba(16, 36, 43, 0.2);
    flex-shrink: 0;
}

.brand-link:focus-visible,
.btn-toggle-pw:focus-visible,
.btn-submit:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 2px;
}

.brand-link:focus-visible {
    outline-color: #ffffff;
}

.banner-content {
    margin: var(--space-7) 0;
}

.banner-content h2 {
    font-size: var(--text-2xl);
    line-height: var(--leading-snug);
    margin: 0 0 var(--space-4);
    font-weight: var(--weight-bold);
    letter-spacing: -0.015em;
    max-width: 26ch;
}

.banner-content p {
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
    font-size: var(--text-base);
    line-height: var(--leading-body);
    max-width: 44ch;
}

.banner-footer {
    font-size: var(--text-xs);
    color: rgba(255, 255, 255, 0.75);
    line-height: var(--leading-snug);
}

/* Sisi Formulir Kanan */
.auth-content {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-6);
    min-width: 0;
}

.auth-box {
    width: 100%;
    max-width: 380px;
}

.auth-header h1 {
    font-size: var(--text-2xl);
    font-weight: var(--weight-bold);
    letter-spacing: -0.02em;
    margin: 0 0 var(--space-2);
}

.auth-header p {
    margin: 0 0 var(--space-5);
    color: var(--color-muted);
    font-size: var(--text-sm);
}

.alert {
    padding: var(--space-3);
    border-radius: var(--radius-sm);
    font-size: var(--text-sm);
    margin-bottom: var(--space-4);
    overflow-wrap: anywhere;
}

.alert-danger {
    background: #fdf2f2;
    border: 1px solid #f9d5d5;
    color: var(--color-danger);
}

.alert-info {
    background: var(--color-brand-tint);
    border: 1px solid var(--color-line);
    color: var(--color-brand-dark);
}

.form-group {
    margin-bottom: var(--space-4);
}

.form-group label {
    display: block;
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    margin-bottom: var(--space-2);
}

input[type="email"],
input[type="password"],
input[type="text"] {
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
}

input:focus {
    outline: none;
    border-color: var(--color-brand);
    box-shadow: 0 0 0 2px var(--color-brand-tint);
}

input.is-invalid {
    border-color: var(--color-danger);
}

.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 76px;
}

.btn-toggle-pw {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    min-width: 60px;
    height: 36px;
    background: none;
    border: none;
    border-radius: var(--radius-sm);
    color: var(--color-muted);
    font-family: inherit;
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    cursor: pointer;
    padding: 0 var(--space-2);
}

.btn-toggle-pw:hover {
    color: var(--color-ink);
}

.remember-row {
    margin-bottom: var(--space-5);
}

.checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    min-height: 32px;
    font-size: var(--text-sm);
    color: var(--color-muted);
    cursor: pointer;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    margin: 0;
    accent-color: var(--color-brand);
}

.btn-submit {
    width: 100%;
    height: var(--control-height);
    background: var(--color-brand);
    color: #ffffff;
    border: none;
    border-radius: var(--radius);
    font-family: inherit;
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.btn-submit:hover:not(:disabled) {
    background: var(--color-brand-dark);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.auth-footnote {
    margin: var(--space-5) 0 0;
    font-size: var(--text-xs);
    color: var(--color-muted);
    text-align: center;
}

.mobile-footer {
    display: none;
}

/* Tablet: tetap dua kolom, hanya padding banner dirapatkan */
@media (max-width: 1024px) {
    .banner-inner {
        padding-right: var(--space-5);
    }

    .auth-content {
        padding-left: var(--space-5);
        padding-right: var(--space-5);
    }
}

/* Di bawah 720px: banner menjadi blok di atas, formulir di bawahnya */
@media (max-width: 720px) {
    .login-wrapper {
        grid-template-columns: minmax(0, 1fr);
        /* baris banner mengikuti isinya, formulir mengisi sisanya */
        grid-template-rows: auto 1fr;
    }

    .auth-banner {
        border-bottom: var(--border);
    }

    .banner-inner {
        max-width: var(--container);
        margin: 0 auto;
        height: auto;
        padding: var(--space-3) var(--space-5) var(--space-6);
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
    }

    .brand-link {
        height: var(--control-height);
    }

    /* Teks banner tetap tampil, hanya lebih ringkas */
    .banner-content {
        margin: var(--space-4) 0 0;
    }

    .banner-content h2 {
        font-size: var(--text-xl);
        max-width: 30ch;
    }

    .banner-content p {
        font-size: var(--text-sm);
    }

    /* Hak cipta sudah ada di bawah formulir (.mobile-footer) */
    .banner-footer {
        display: none;
    }

    .auth-content {
        padding: var(--space-6) var(--space-5) var(--space-5);
        align-items: center;
    }

    .auth-box {
        max-width: 420px;
        margin: 0 auto;
    }

    .mobile-footer {
        display: block;
        margin-top: var(--space-6);
        font-size: var(--text-xs);
        color: var(--color-muted);
        text-align: center;
    }
}

/* Ponsel */
@media (max-width: 540px) {
    .banner-inner {
        padding: var(--space-2) var(--space-4) var(--space-5);
    }

    .auth-content {
        padding: var(--space-5) var(--space-4) var(--space-4);
        align-items: flex-start;
    }

    /* 16px mencegah iOS memperbesar layar saat kolom diketuk */
    input[type="email"],
    input[type="password"],
    input[type="text"] {
        font-size: var(--text-base);
    }

    .auth-footnote,
    .mobile-footer {
        line-height: var(--leading-body);
    }
}

/* Layar pendek (ponsel mendatar): jangan memaksa tinggi penuh */
@media (max-height: 560px) and (min-width: 721px) {
    .banner-content {
        margin: var(--space-5) 0;
    }

    .auth-content {
        align-items: flex-start;
        padding-top: var(--space-5);
        padding-bottom: var(--space-5);
    }
}

@media (prefers-reduced-motion: reduce) {
    .btn-submit {
        transition: none;
    }
}
</style>