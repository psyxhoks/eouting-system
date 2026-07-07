<?php
echo "<h1>GD Extension Check</h1>";

if(extension_loaded('gd')) {
    echo "✅ GD extension is ENABLED<br>";
    echo "✅ GD Version: " . gd_info()['GD Version'] . "<br>";
    echo "✅ You can generate QR codes!<br>";
} else {
    echo "❌ GD extension is DISABLED<br>";
    echo "Please enable it in php.ini<br>";
}

phpinfo();
?>