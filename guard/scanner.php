<?php

    include '../config/error.php';

    error_reporting(E_ALL);
    ini_set('display_errors',1);

    include '../config/db.php';
    include '../config/session.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guard')
    {
        header("Location: ../login.php");
        exit();
    }

    include '../includes/header.php';
    include '../includes/navbar.php';
    include '../includes/sidebar.php';

    ?>

    <h2 class="fw-bold mt-3">

    QR Scanner

    </h2>

    <p class="text-muted">

    Verify student QR passes for check-in and check-out.

    </p>

    <hr>

    <script src="https://unpkg.com/html5-qrcode"></script>

    <div class="row justify-content-center">

    <div class="col-md-6">

    <div class="card">

    <div class="card-body p-5 text-center">

    <div class="mb-4">

    <i
    class="bi bi-qr-code-scan"
    style="
    font-size:70px;
    color:#D86A6A;
    ">

    </i>

    </div>

    <h4 class="mb-4">

    Scan QR Pass

    </h4>

    <div
    id="reader"
    style="
    width:100%;
    max-width:400px;
    margin:auto;
    ">
    </div>

    <form
    action="verify_qr.php"
    method="POST"
    id="qrForm">

    <input
    type="hidden"
    name="qr_content"
    id="qr_content">

    </form>

    </div>

    </div>

    </div>

    </div>

    <script>

const html5QrCode = new Html5Qrcode("reader");

function onScanSuccess(decodedText)
{
    html5QrCode.stop().then(() =>
    {
        document.getElementById("qr_content").value = decodedText;
        document.getElementById("qrForm").submit();
    });
}

html5QrCode.start(
    {
        facingMode: "environment"
    },
    {
        fps: 10,
        qrbox: 250
    },
    onScanSuccess
)
.then(() =>
{
    alert("Camera started successfully");
})
.catch((err) =>
{
    alert("Camera Error:\n" + err);
    console.error(err);
});

</script>

    <?php

    include '../includes/footer.php';

    ?>