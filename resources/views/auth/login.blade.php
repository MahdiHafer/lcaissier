@extends('layouts.app')

@section('content')
<div class="login-shell">
    <div class="login-panel">
        <div class="login-brand">
            <img src="{{ asset('logo.png') }}" alt="Logo">
            <div>
                <h1>L'CAISSIER</h1>
                <p>Systeme de gestion commerciale & POS</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="pinForm">
            @csrf

            <label class="pin-label">Code d'acces</label>
            <input type="password" name="access_code" id="accessCode" class="pin-input" maxlength="12" inputmode="numeric" autocomplete="off" readonly required>

            <div class="pin-dots" id="pinDots">
                @for($i = 0; $i < 12; $i++)
                    <span class="dot"></span>
                @endfor
            </div>

            <div class="keypad">
                <button type="button" class="key" data-key="1">1</button>
                <button type="button" class="key" data-key="2">2</button>
                <button type="button" class="key" data-key="3">3</button>
                <button type="button" class="key" data-key="4">4</button>
                <button type="button" class="key" data-key="5">5</button>
                <button type="button" class="key" data-key="6">6</button>
                <button type="button" class="key" data-key="7">7</button>
                <button type="button" class="key" data-key="8">8</button>
                <button type="button" class="key" data-key="9">9</button>
                <button type="button" class="key key-action" id="clearKey">C</button>
                <button type="button" class="key" data-key="0">0</button>
                <button type="button" class="key key-action" id="backKey">?</button>
            </div>

            <div class="actions">
                <button type="submit" class="btn-enter">Se connecter</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<style>
.login-shell {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 20px;
    background:
        radial-gradient(900px 400px at 0% -15%, rgba(3, 151, 98, .16) 0%, transparent 60%),
        radial-gradient(900px 400px at 100% 0%, rgba(16, 129, 255, .14) 0%, transparent 60%),
        #f4f7fb;
}

.login-panel {
    width: 100%;
    max-width: 430px;
    background: rgba(255, 255, 255, .92);
    border: 1px solid rgba(219, 229, 240, .9);
    border-radius: 24px;
    padding: 22px;
    box-shadow: 0 28px 60px rgba(11, 26, 43, .14);
    backdrop-filter: blur(10px);
}

.login-brand {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 14px;
}

.login-brand img {
    width: 54px;
    height: 54px;
    object-fit: contain;
    border-radius: 12px;
    background: #f0f4f8;
    padding: 6px;
}

.login-brand h1 {
    font-size: 18px;
    line-height: 1.1;
    margin: 0;
    color: #12212f;
}

.login-brand p {
    margin: 2px 0 0;
    color: #65788a;
    font-size: 12px;
}

.pin-label {
    font-weight: 700;
    color: #1a2936;
    margin-bottom: 8px;
    display: block;
}

.pin-input {
    width: 100%;
    border: 1px solid #d8e3ee;
    border-radius: 14px;
    padding: 14px;
    font-size: 22px;
    letter-spacing: 10px;
    text-align: center;
    margin-bottom: 10px;
    background: #fff;
}

.pin-dots {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 6px;
    margin-bottom: 14px;
}

.dot {
    height: 4px;
    border-radius: 999px;
    background: #d4deea;
    transition: .15s ease;
}

.dot.filled {
    background: #0f9d64;
}

.keypad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.key {
    border: 1px solid #d8e3ee;
    border-radius: 14px;
    background: #fff;
    color: #12212f;
    font-size: 22px;
    font-weight: 700;
    height: 60px;
    transition: .16s ease;
}

.key:hover {
    border-color: #96c8ff;
    transform: translateY(-1px);
}

.key:active {
    transform: scale(.98);
}

.key-action {
    font-size: 18px;
    font-weight: 600;
}

.actions {
    margin-top: 12px;
}

.btn-enter {
    width: 100%;
    border: 0;
    border-radius: 14px;
    background: linear-gradient(135deg, #0f9d64, #0a7e50);
    color: #fff;
    font-weight: 700;
    height: 50px;
}

@media (max-width: 480px) {
    .login-panel { padding: 16px; border-radius: 18px; }
    .key { height: 56px; }
}
</style>

<script>
const accessInput = document.getElementById('accessCode');
const dots = Array.from(document.querySelectorAll('#pinDots .dot'));
const keys = Array.from(document.querySelectorAll('.key[data-key]'));
const clearKey = document.getElementById('clearKey');
const backKey = document.getElementById('backKey');

function refreshDots() {
    const len = accessInput.value.length;
    dots.forEach((dot, idx) => {
        dot.classList.toggle('filled', idx < len);
    });
}

function pushDigit(digit) {
    if (accessInput.value.length >= 12) return;
    accessInput.value += digit;
    refreshDots();
}

keys.forEach((btn) => {
    btn.addEventListener('click', () => pushDigit(btn.dataset.key));
});

clearKey.addEventListener('click', () => {
    accessInput.value = '';
    refreshDots();
});

backKey.addEventListener('click', () => {
    accessInput.value = accessInput.value.slice(0, -1);
    refreshDots();
});

refreshDots();
</script>
@endsection
