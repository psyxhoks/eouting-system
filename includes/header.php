<!DOCTYPE html>
<html>
<head>
    <title>KPTM Hostel Outing System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/theme.css?v=<?= time() ?>" rel="stylesheet">

    <style>
        html, body {
            margin: 0 !important;
            padding-top: 0 !important;
        }
        .navbar {
            margin-top: 0 !important;
        }
    </style>
</head>
<body>

<script>
(function(){
    var saved = localStorage.getItem('theme');
    if(saved === 'dark'){
        document.body.classList.add('dark-mode');
    }
})();
</script>