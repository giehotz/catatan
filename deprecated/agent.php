<?php
header("Location: /");
exit;

echo "<b>Current Browser User-Agent:</b> " . htmlspecialchars($userAgent) . "<br><br>";
echo "<b>Is Mobile Detected?</b> " . ($isMobile ? "<span style='color:green'>YES ($mobile)</span>" : "<span style='color:red'>NO</span>") . "<br><br>";

if (!$isMobile) {
    echo "If you are using Chrome Developer Tools Device Toolbar, make sure you actually REFRESH the page (F5) AFTER toggling the device. The server only sees the User-Agent when the page is loaded.";
}
