@if(session('success'))

<div id="toast-overlay">

    <div id="toast-box" class="toast-success">

        <i class="bi bi-check-circle-fill me-2"></i>

        {{ session('success') }}

    </div>

</div>

@endif

@if(session('error'))

<div id="toast-overlay-error">

    <div id="toast-box-error" class="toast-error">

        <i class="bi bi-x-circle-fill me-2"></i>

        {{ session('error') }}

    </div>

</div>

@endif

<style>

#toast-overlay,
#toast-overlay-error{
    position: fixed;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
    pointer-events: none;
}

#toast-box,
#toast-box-error{
    color: #fff;
    padding: 16px 30px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    box-shadow: 0 12px 30px rgba(0,0,0,.25);
    opacity: 0;
    transform: scale(.8);
    animation: toastAnimation .35s ease forwards;
    max-width: 80vw;
    text-align: center;
}

.toast-success{
    background: #198754;
}

.toast-error{
    background: #dc3545;
}

@keyframes toastAnimation{
    from{
        opacity:0;
        transform:scale(.8);
    }
    to{
        opacity:1;
        transform:scale(1);
    }
}

.fade-out{
    animation:fadeOut .4s ease forwards;
}

@keyframes fadeOut{
    from{
        opacity:1;
        transform:scale(1);
    }
    to{
        opacity:0;
        transform:scale(.8);
    }
}

</style>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Toast sukses menghilang otomatis setelah beberapa detik.
    |--------------------------------------------------------------------------
    */

    const toast = document.getElementById('toast-box');

    if (toast) {

        setTimeout(function () {

            toast.classList.add('fade-out');

            setTimeout(function () {

                const overlay = document.getElementById('toast-overlay');

                if (overlay) {
                    overlay.remove();
                }

            }, 400);

        }, 2500);
    }

    /*
    |--------------------------------------------------------------------------
    | Toast error ditampilkan lebih lama karena pesannya
    | biasanya lebih penting untuk dibaca pengguna.
    |--------------------------------------------------------------------------
    */

    const toastError = document.getElementById('toast-box-error');

    if (toastError) {

        setTimeout(function () {

            toastError.classList.add('fade-out');

            setTimeout(function () {

                const overlayError = document.getElementById(
                    'toast-overlay-error'
                );

                if (overlayError) {
                    overlayError.remove();
                }

            }, 400);

        }, 5000);
    }

});

</script>